(() => {
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('[data-tour-form]');
        if (!form) return;
        let dirty = false;
        let editorRevision = 0;
        const markDirty = () => { dirty = true; form.querySelector('[data-save-state]').textContent = 'Có thay đổi chưa lưu'; };
        form.addEventListener('input', markDirty);
        form.addEventListener('change', markDirty);
        form.addEventListener('submit', () => { window.tinymce?.triggerSave(); dirty = false; });
        window.addEventListener('beforeunload', event => { if (dirty) { event.preventDefault(); event.returnValue = ''; } });

        const reveal = field => {
            if (!field) return;
            const panels = [];
            for (let node = field.parentElement; node && node !== form; node = node.parentElement) {
                if (node.matches('details')) node.open = true;
                if (node.matches('.tab-pane')) panels.unshift(node);
            }
            panels.forEach(panel => {
                const button = form.querySelector('[data-bs-target="#' + panel.id + '"]');
                if (button) window.bootstrap.Tab.getOrCreateInstance(button).show();
            });
            requestAnimationFrame(() => { field.scrollIntoView({ block: 'center' }); field.focus({ preventScroll: true }); });
        };
        form.addEventListener('invalid', event => reveal(event.target), true);
        const normalize = name => name.replace(/\[([^\]]+)\]/g, '.$1');
        const findField = key => [...form.elements].find(field => normalize(field.name || '') === key);
        form.querySelectorAll('[data-error-field]').forEach(button => {
            const field = findField(button.dataset.errorField);
            if (field) { field.classList.add('is-invalid'); field.setAttribute('aria-invalid', 'true'); }
            button.addEventListener('click', () => reveal(field));
        });
        const firstError = form.querySelector('[data-error-field]');
        if (firstError) reveal(findField(firstError.dataset.errorField));
        const tabKey = 'hg-tour-editor:' + location.pathname;
        if (!firstError) {
            try {
                const savedTabs = JSON.parse(sessionStorage.getItem(tabKey) || '[]');
                if (Array.isArray(savedTabs)) savedTabs.forEach(id => {
                    const button = [...form.querySelectorAll('[data-bs-toggle="tab"]')].find(tab => tab.id === id);
                    if (button) window.bootstrap.Tab.getOrCreateInstance(button).show();
                });
            } catch { /* Editing remains available when browser storage is disabled. */ }
        }
        form.addEventListener('shown.bs.tab', () => {
            const activeTabs = [...form.querySelectorAll('[data-bs-toggle="tab"].active')].map(tab => tab.id);
            try { sessionStorage.setItem(tabKey, JSON.stringify(activeTabs)); } catch { /* Optional preference only. */ }
        });

        form.querySelectorAll('details').forEach(details => details.addEventListener('toggle', () => {
            if (details.open) window.initHgTinyMceEditors?.(details);
        }));
        form.querySelectorAll('[data-tour-repeater]').forEach(root => {
            const list = root.querySelector('[data-repeater-list]');
            const template = root.querySelector('[data-repeater-template]');
            const undoBar = root.querySelector('[data-repeater-undo]');
            let nextIndex = 0;
            const removed = [...list.querySelectorAll(':scope > [data-repeater-row][hidden]')];
            undoBar.hidden = removed.length === 0;
            removed.forEach(row => row.querySelectorAll('input, select, textarea').forEach(field => {
                if (!field.name.endsWith('[id]') && !field.matches('[data-remove-input]')) field.disabled = true;
            }));
            const rows = () => [...list.querySelectorAll(':scope > [data-repeater-row]')].filter(row => !row.hidden);
            const detachEditors = row => row.querySelectorAll('.tinymce-editor').forEach(area => {
                const editor = window.tinymce?.get(area.id);
                if (editor) { editor.save(); editor.remove(); }
                // Moving an iframe resets its document. Give its replacement a fresh identity.
                const replacement = area.cloneNode(true);
                replacement.id = area.id.replace(/_move_\d+$/, '') + '_move_' + (++editorRevision);
                delete replacement.dataset.editorInitializing;
                replacement.style.removeProperty('display');
                replacement.removeAttribute('aria-hidden');
                row.querySelectorAll('label').forEach(label => { if (label.htmlFor === area.id) label.htmlFor = replacement.id; });
                area.replaceWith(replacement);
            });
            const sync = () => {
                rows().forEach((row, index) => {
                    const day = row.querySelector('[data-day-number]');
                    if (day) { day.value = index + 1; row.querySelector('[data-day-label]').textContent = 'Ngày ' + (index + 1); }
                    const title = row.querySelector('[data-title-input]');
                    const type = row.querySelector('[data-section-type]');
                    const label = title?.value.trim() || type?.selectedOptions[0]?.textContent || 'Chưa nhập tiêu đề';
                    row.querySelector('[data-row-title]').textContent = label;
                    row.querySelector('[data-row-title]').title = label;
                    if (type) row.querySelector('[data-row-type]').textContent = type.selectedOptions[0]?.textContent;
                });
                root.querySelector('[data-repeater-empty]').hidden = rows().length > 0;
                root.querySelector('[data-expand-rows]').textContent = rows().length && rows().every(row => row.open) ? 'Thu gọn tất cả' : 'Mở tất cả';
            };
            const bind = row => {
                row.addEventListener('toggle', () => { if (row.open) window.initHgTinyMceEditors?.(row); sync(); });
                row.querySelector('[data-reorder-handle]').addEventListener('click', event => event.preventDefault());
                row.querySelector('[data-reorder-handle]').addEventListener('keydown', event => {
                    if (!['ArrowUp', 'ArrowDown'].includes(event.key)) return;
                    event.preventDefault();
                    const all = rows(); const index = all.indexOf(row);
                    const sibling = all[index + (event.key === 'ArrowUp' ? -1 : 1)];
                    if (!sibling) return;
                    detachEditors(row);
                    if (event.key === 'ArrowUp') list.insertBefore(row, sibling); else sibling.after(row);
                    sync(); window.initHgTinyMceEditors?.(row); markDirty(); event.currentTarget.focus();
                });
            };
            list.querySelectorAll('[data-repeater-row]').forEach(bind);
            root.addEventListener('input', sync);
            root.addEventListener('change', event => {
                if (event.target.matches('[data-section-type]')) {
                    const note = event.target.closest('[data-repeater-row]').querySelector('[data-section-placement]');
                    note.textContent = event.target.value === 'highlights' ? 'Hiển thị trước lịch trình. Sau khi lưu, mục này nằm trong Điểm nổi bật.' : 'Hiển thị sau lịch trình. Sau khi lưu, mục này nằm trong Dịch vụ & chính sách.';
                }
                sync();
            });
            root.addEventListener('click', event => {
                if (event.target.closest('[data-repeater-add]')) {
                    let index;
                    do { index = (root.dataset.indexPrefix || root.dataset.tourRepeater) + '_new_' + nextIndex++; }
                    while ([...form.elements].some(field => field.name?.startsWith(root.dataset.tourRepeater + '[' + index + ']')));
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', index);
                    const row = wrapper.firstElementChild;
                    list.appendChild(row); bind(row); sync(); window.initHgTinyMceEditors?.(row); markDirty();
                    row.querySelector('[data-title-input]')?.focus();
                }
                if (event.target.closest('[data-repeater-remove]')) {
                    const row = event.target.closest('[data-repeater-row]');
                    row.querySelectorAll('.tinymce-editor').forEach(area => window.tinymce?.get(area.id)?.save());
                    const removeInput = row.querySelector('[data-remove-input]');
                    if (removeInput) removeInput.value = '1';
                    row.querySelectorAll('input, select, textarea').forEach(field => {
                        if (!field.name.endsWith('[id]') && field !== removeInput) field.disabled = true;
                    });
                    row.hidden = true; removed.push(row); undoBar.hidden = false; sync(); markDirty();
                    root.querySelector('[data-undo-remove]').focus();
                }
                if (event.target.closest('[data-undo-remove]')) {
                    const row = removed.pop();
                    if (row) {
                        row.hidden = false; row.open = true;
                        row.querySelectorAll('input, select, textarea').forEach(field => { field.disabled = false; });
                        const removeInput = row.querySelector('[data-remove-input]'); if (removeInput) removeInput.value = '0';
                        sync(); window.initHgTinyMceEditors?.(row); markDirty(); row.querySelector('[data-title-input]')?.focus();
                    }
                    undoBar.hidden = removed.length === 0;
                }
                if (event.target.closest('[data-expand-rows]')) {
                    const open = !rows().every(row => row.open); rows().forEach(row => { row.open = open; }); sync();
                }
            });
            if (window.Sortable) window.Sortable.create(list, {
                handle: '[data-reorder-handle]', draggable: '[data-repeater-row]:not([hidden])', animation: 150,
                onStart: event => detachEditors(event.item),
                onEnd: event => { sync(); window.initHgTinyMceEditors?.(event.item); markDirty(); },
            });
            sync();
        });
    });
})();
