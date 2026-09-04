const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

const initFieldToggles = () => {
    document.querySelectorAll('.toggle-field-switch').forEach((input) => {
        if (input.dataset.toggleInitialized === 'true') return;
        input.dataset.toggleInitialized = 'true';

        input.addEventListener('change', async () => {
            const nextValue = input.checked;
            input.disabled = true;

            try {
                const response = await fetch(input.dataset.toggleUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: JSON.stringify({
                        resource: input.dataset.model,
                        id: input.dataset.id,
                        field: input.dataset.field,
                        value: nextValue,
                    }),
                });
                const payload = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(payload.message || `HTTP ${response.status}`);

                input.checked = Boolean(payload.value);
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: payload.message, showConfirmButton: false, timer: 1800 });
            } catch (error) {
                input.checked = !nextValue;
                await Swal.fire('Không thể cập nhật', error.message || 'Vui lòng thử lại.', 'error');
            } finally {
                input.disabled = false;
            }
        });
    });
};

const initStandardIndexColumns = (root) => {
    const resource = root.dataset.indexResource;
    const table = root.querySelector('table');
    const headerRow = table?.querySelector('thead tr');
    const body = table?.querySelector('tbody');
    if (!resource || !headerRow || !body) return;

    const rows = [...body.querySelectorAll(':scope > tr[data-record-id]')];
    if (rows.length === 0) return;

    const insertFirst = (row, element) => row.insertBefore(element, row.firstChild);
    const insertAfterSelection = (row, element) => {
        const selection = row.querySelector(':scope > [data-select-column]');
        row.insertBefore(element, selection?.nextSibling || row.firstChild);
    };

    const bulkFormId = root.dataset.bulkFormId;
    if (bulkFormId && !headerRow.querySelector('[data-check-all]')) {
        const header = document.createElement('th');
        header.className = 'text-center';
        header.style.width = '48px';
        header.dataset.selectColumn = 'true';
        header.innerHTML = '<input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả">';
        insertFirst(headerRow, header);

        rows.forEach((row) => {
            const cell = document.createElement('td');
            cell.className = 'text-center';
            cell.dataset.selectColumn = 'true';
            cell.innerHTML = `<input form="${bulkFormId}" type="checkbox" name="ids[]" value="${row.dataset.recordId}" class="form-check-input" data-check-item aria-label="Chọn bản ghi">`;
            insertFirst(row, cell);
        });
    }

    const canReorder = root.dataset.reorderable === '1' && root.dataset.reorderEnabled === '1';
    if (!canReorder) return;

    body.dataset.sortableBody = 'true';
    body.dataset.resource = resource;
    body.dataset.reorderUrl = root.dataset.reorderUrl;
    body.dataset.orderStart = root.dataset.orderStart || '1';

    if (!headerRow.querySelector('[data-order-column]')) {
        const header = document.createElement('th');
        header.className = 'text-center';
        header.dataset.orderColumn = 'true';
        header.innerHTML = '<i class="bi bi-arrow-down-up"></i>';
        insertAfterSelection(headerRow, header);

        rows.forEach((row) => {
            const cell = document.createElement('td');
            cell.className = 'text-center';
            cell.dataset.orderColumn = 'true';
            cell.innerHTML = '<button type="button" class="admin-drag-handle" data-drag-handle aria-label="Kéo để sắp xếp"><i class="bi bi-grip-vertical fs-5"></i></button>';
            insertAfterSelection(row, cell);
        });
    }
};

const initBulkSelection = (root) => {
    const form = root.querySelector('[data-admin-bulk-form]');
    const toolbar = root.querySelector('[data-bulk-toolbar]');
    const checkAll = root.querySelector('[data-check-all]');
    const items = [...root.querySelectorAll('[data-check-item]')];
    const count = root.querySelector('[data-selected-count]');
    const action = root.querySelector('[data-bulk-action]');
    const apply = root.querySelector('[data-bulk-apply]');
    if (!form || !toolbar || !checkAll) return;

    const syncSelectedInputs = () => {
        form.querySelectorAll('[data-bulk-selection-proxy]').forEach((input) => input.remove());

        items
            .filter((item) => item.checked && item.form !== form)
            .forEach((item) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = item.name || 'ids[]';
                input.value = item.value;
                input.dataset.bulkSelectionProxy = 'true';
                form.append(input);
            });
    };

    const sync = () => {
        const selected = items.filter((item) => item.checked).length;
        checkAll.checked = items.length > 0 && selected === items.length;
        checkAll.indeterminate = selected > 0 && selected < items.length;
        toolbar.hidden = selected === 0;
        if (count) count.textContent = String(selected);
    };

    checkAll.addEventListener('change', () => {
        items.forEach((item) => { item.checked = checkAll.checked; });
        sync();
    });
    items.forEach((item) => item.addEventListener('change', sync));
    apply?.addEventListener('click', async () => {
        const selected = items.filter((item) => item.checked).length;
        if (!action?.value || selected === 0) {
            await Swal.fire('Chưa đủ thông tin', 'Hãy chọn thao tác và ít nhất một bản ghi.', 'warning');
            return;
        }
        const deleting = action.value === 'delete';
        const result = await Swal.fire({
            title: deleting ? `Xóa ${selected} bản ghi đã chọn?` : `Áp dụng cho ${selected} bản ghi?`,
            text: deleting ? (form.dataset.deleteWarning || 'Dữ liệu đã xóa không thể khôi phục.') : 'Thao tác sẽ áp dụng cho các dòng đang chọn trên trang này.',
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Xác nhận', cancelButtonText: 'Hủy',
            confirmButtonColor: deleting ? '#dc3545' : '#0d6efd',
        });
        if (result.isConfirmed) {
            syncSelectedInputs();
            form.requestSubmit();
        }
    });
    sync();
};

const initReordering = (root) => {
    const toggle = root.querySelector('[data-reorder-toggle]');
    const body = root.querySelector('[data-sortable-body]');
    if (!toggle || !body || typeof Sortable === 'undefined') return;
    let enabled = false;
    const sortable = Sortable.create(body, {
        animation: 160, handle: '[data-drag-handle]', draggable: '[data-record-id]',
        ghostClass: 'admin-sortable-ghost', chosenClass: 'admin-sortable-chosen', disabled: true,
        onEnd: async () => {
            const start = Number(body.dataset.orderStart || 1);
            const items = [...body.querySelectorAll('[data-record-id]')].map((row, index) => ({ id: Number(row.dataset.recordId), order: start + index }));
            try {
                const response = await fetch(body.dataset.reorderUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                    body: JSON.stringify({ resource: body.dataset.resource, items }),
                });
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const payload = await response.json();
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: payload.message, showConfirmButton: false, timer: 1800 });
            } catch (error) {
                await Swal.fire('Không thể lưu thứ tự', 'Trang sẽ tải lại để khôi phục thứ tự trước đó.', 'error');
                window.location.reload();
            }
        },
    });
    toggle.addEventListener('click', () => {
        enabled = !enabled;
        if (enabled) {
            root.querySelectorAll('[data-check-item], [data-check-all]').forEach((checkbox) => {
                checkbox.checked = false;
                checkbox.indeterminate = false;
                checkbox.dispatchEvent(new Event('change'));
            });
        }
        sortable.option('disabled', !enabled);
        root.classList.toggle('is-reordering', enabled);
        toggle.classList.toggle('btn-primary', enabled);
        toggle.classList.toggle('btn-default', !enabled);
        toggle.setAttribute('aria-pressed', String(enabled));
        toggle.querySelector('[data-reorder-label]').textContent = enabled ? 'Xong' : 'Sắp xếp';
    });
};

const menuDecodeSource = (encoded) => {
    const binary = atob(encoded);
    const bytes = Uint8Array.from(binary, (character) => character.charCodeAt(0));

    return JSON.parse(new TextDecoder().decode(bytes));
};

const menuOwnEditor = (node) => node.querySelector(':scope > .menu-builder__item > [data-menu-editor]');
const menuOwnChildren = (node) => node.querySelector(':scope > [data-menu-list]');
const menuField = (node, name) => menuOwnEditor(node)?.querySelector(`[data-menu-field="${name}"]`);

const menuSetHeaderState = (node) => {
    const title = menuField(node, 'title')?.value.trim() || 'Mục menu mới';
    const active = menuField(node, 'is_active')?.checked ?? true;
    const label = node.querySelector(':scope > .menu-builder__item [data-menu-item-label]');
    const activeLabel = node.querySelector(':scope > .menu-builder__item [data-menu-active-label]');

    if (label) label.textContent = title;
    if (activeLabel) {
        activeLabel.textContent = active ? 'Đang bật' : 'Đang tắt';
        activeLabel.classList.toggle('text-bg-success', active);
        activeLabel.classList.toggle('text-bg-secondary', !active);
    }
};

const menuSyncEmptyStates = (root) => {
    root.querySelectorAll('[data-menu-list]').forEach((list) => {
        const hasNodes = [...list.children].some((child) => child.matches('[data-menu-node]'));
        const empty = list.querySelector(':scope > [data-menu-empty]');

        if (hasNodes && empty) empty.remove();
        if (!hasNodes && !empty) {
            const placeholder = document.createElement('li');
            placeholder.className = 'menu-builder__empty';
            placeholder.dataset.menuEmpty = 'true';
            placeholder.textContent = 'Thả mục vào đây để tạo cấp con.';
            list.append(placeholder);
        }
    });
};

const menuSetSourceState = (node, source) => {
    const sourceType = source.source_type || 'custom';
    const title = source.label || 'Mục menu mới';
    const titleField = menuField(node, 'title');
    const urlField = menuField(node, 'url');
    const routeField = menuField(node, 'route_name');
    const sourceIdField = menuField(node, 'linked_source_id');
    const sourceTypeField = menuField(node, 'linked_source_type');
    const linkSummary = node.querySelector(':scope > .menu-builder__item [data-menu-link]');
    const itemType = node.querySelector(':scope > .menu-builder__item [data-menu-item-type]');

    if (titleField) titleField.value = title;
    if (urlField) urlField.value = source.url || '';
    if (routeField) routeField.value = source.route_name || '';
    if (sourceIdField) sourceIdField.value = source.source_id || '';
    if (sourceTypeField) sourceTypeField.value = sourceType;
    if (linkSummary) linkSummary.textContent = sourceType === 'custom'
        ? (source.url || 'Chưa có liên kết')
        : `${source.label} · ${source.meta}`;
    if (itemType) itemType.textContent = sourceType === 'custom' ? 'Liên kết custom' : source.meta;

    menuSetHeaderState(node);
};

const menuNewNode = (template, source) => {
    const node = template.content.firstElementChild.cloneNode(true);
    const key = `new-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;

    node.dataset.itemId = '';
    node.dataset.menuKey = key;
    node.querySelectorAll('[id]').forEach((element) => {
        element.id = element.id.replace('new', key);
    });
    menuSetSourceState(node, source);

    return node;
};

const menuSerializeNode = (node) => {
    const data = {};
    const id = Number(node.dataset.itemId || 0);

    if (id > 0) data.id = id;

    menuOwnEditor(node)?.querySelectorAll('[data-menu-field]').forEach((field) => {
        const name = field.dataset.menuField;
        if (name === 'id') return;
        data[name] = field.type === 'checkbox' ? field.checked : field.value;
    });

    data.children = [...(menuOwnChildren(node)?.children || [])]
        .filter((child) => child.matches('[data-menu-node]'))
        .map(menuSerializeNode);

    return data;
};

const initMenuBuilder = () => {
    document.querySelectorAll('[data-menu-builder]').forEach((form) => {
        if (form.dataset.menuBuilderInitialized === 'true') return;
        form.dataset.menuBuilderInitialized = 'true';

        const rootList = form.querySelector('.menu-builder__root');
        const template = document.querySelector('template[data-menu-item-template]');
        const payload = form.querySelector('[data-menu-payload]');
        if (!rootList || !template || !payload) return;

        const initMenuSortable = (list) => {
            if (typeof Sortable === 'undefined') return;
            if (list.dataset.menuSortableInitialized === 'true') return;
            list.dataset.menuSortableInitialized = 'true';

            Sortable.create(list, {
                animation: 180,
                handle: '[data-menu-handle]',
                draggable: '[data-menu-node]',
                filter: '[data-menu-empty]',
                preventOnFilter: false,
                fallbackOnBody: true,
                group: { name: 'menu-builder', pull: true, put: true },
                ghostClass: 'menu-builder__ghost',
                chosenClass: 'menu-builder__chosen',
                onMove: (event) => !event.dragged.contains(event.to),
                onEnd: () => menuSyncEmptyStates(form),
            });
        };

        form.querySelectorAll('[data-menu-list]').forEach(initMenuSortable);

        form.querySelectorAll('[data-menu-source-button]').forEach((button) => {
            button.addEventListener('click', () => {
                const source = menuDecodeSource(button.dataset.menuSource);
                const node = menuNewNode(template, source);
                rootList.append(node);
                node.querySelectorAll('[data-menu-list]').forEach(initMenuSortable);
                menuSyncEmptyStates(form);
                menuOpenNode(node);
                node.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã thêm mục vào menu', showConfirmButton: false, timer: 1600 });
            });
        });

        const search = form.querySelector('[data-menu-source-search]');
        search?.addEventListener('input', () => {
            const query = search.value.trim().toLowerCase();

            form.querySelectorAll('[data-menu-source-group]').forEach((group) => {
                let visible = 0;
                group.querySelectorAll('[data-menu-source-button]').forEach((button) => {
                    const matches = !query || (button.dataset.menuSearch || '').includes(query);
                    button.hidden = !matches;
                    if (matches) visible += 1;
                });
                group.hidden = visible === 0;
                if (query && visible > 0) group.open = true;
            });
        });

        form.querySelector('[data-menu-custom-add]')?.addEventListener('click', async () => {
            const label = form.querySelector('[data-menu-custom-label]')?.value.trim() || '';
            const url = form.querySelector('[data-menu-custom-url]')?.value.trim() || '';
            const target = form.querySelector('[data-menu-custom-target]')?.value || '_self';

            if (!label || !url || url === '#') {
                await Swal.fire('Thiếu thông tin', 'Vui lòng nhập nhãn hiển thị và URL hợp lệ.', 'warning');
                return;
            }

            if (!url.startsWith('/') && !/^https?:\/\//i.test(url)) {
                await Swal.fire('URL chưa hợp lệ', 'Dùng URL đầy đủ hoặc đường dẫn nội bộ bắt đầu bằng /.', 'warning');
                return;
            }

            const node = menuNewNode(template, {
                label,
                meta: 'Liên kết custom',
                source_type: 'custom',
                source_id: null,
                route_name: null,
                url,
                target,
            });
            menuField(node, 'target').value = target;
            rootList.append(node);
            node.querySelectorAll('[data-menu-list]').forEach(initMenuSortable);
            form.querySelector('[data-menu-custom-label]').value = '';
            form.querySelector('[data-menu-custom-url]').value = '';
            menuSyncEmptyStates(form);
            menuOpenNode(node);
            node.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã thêm link custom', showConfirmButton: false, timer: 1600 });
        });

        form.addEventListener('click', async (event) => {
            const toggle = event.target.closest('[data-menu-toggle]');
            const remove = event.target.closest('[data-menu-remove]');
            const node = event.target.closest('[data-menu-node]');

            if (toggle && node) {
                event.preventDefault();
                const editor = menuOwnEditor(node);
                const isOpen = editor && !editor.hidden;
                if (editor) editor.hidden = isOpen;
                toggle.setAttribute('aria-expanded', String(!isOpen));
                toggle.querySelector('[data-menu-toggle-icon]')?.classList.toggle('bi-chevron-right', isOpen);
                toggle.querySelector('[data-menu-toggle-icon]')?.classList.toggle('bi-chevron-down', !isOpen);
            }

            if (remove && node) {
                event.preventDefault();
                const result = await Swal.fire({
                    title: 'Xóa mục menu này?',
                    text: 'Mục con đi kèm cũng sẽ được bỏ khỏi cấu trúc menu.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Xóa',
                    cancelButtonText: 'Hủy',
                    confirmButtonColor: '#dc3545',
                });
                if (result.isConfirmed) {
                    node.remove();
                    menuSyncEmptyStates(form);
                }
            }
        });

        form.addEventListener('input', (event) => {
            const node = event.target.closest('[data-menu-node]');
            if (node && event.target.matches('[data-menu-field="title"]')) menuSetHeaderState(node);
        });
        form.addEventListener('change', (event) => {
            const node = event.target.closest('[data-menu-node]');
            if (node && event.target.matches('[data-menu-field="is_active"]')) menuSetHeaderState(node);
        });
        form.addEventListener('submit', () => {
            payload.value = JSON.stringify([...rootList.children]
                .filter((node) => node.matches('[data-menu-node]'))
                .map(menuSerializeNode));
        });

        menuSyncEmptyStates(form);
    });
};

const menuOpenNode = (node) => {
    const editor = menuOwnEditor(node);
    const toggle = node.querySelector(':scope > .menu-builder__item [data-menu-toggle]');
    const icon = toggle?.querySelector('[data-menu-toggle-icon]');

    if (editor) editor.hidden = false;
    toggle?.setAttribute('aria-expanded', 'true');
    icon?.classList.remove('bi-chevron-right');
    icon?.classList.add('bi-chevron-down');
};

const initTomSelect = () => {
    if (typeof TomSelect === 'undefined') return;

    document.querySelectorAll('select[data-tom-select="1"]').forEach((select) => {
        if (select.tomselect) return;
        const allowCreate = select.getAttribute('data-tom-select-create') === '1';
        const treeOrder = select.getAttribute('data-tom-select-sort') === 'tree';

        new TomSelect(select, {
            plugins: select.multiple ? {
                remove_button: { title: 'Xóa lựa chọn' },
            } : {},
            placeholder: select.getAttribute('data-placeholder') || null,
            allowEmptyOption: true,
            create: allowCreate,
            closeAfterSelect: !select.multiple,
            sortField: treeOrder
                ? [{ field: '$order', direction: 'asc' }]
                : { field: 'text', direction: 'asc' },
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initFieldToggles();
    document.querySelectorAll('[data-admin-index]').forEach((root) => {
        initStandardIndexColumns(root);
        initBulkSelection(root);
        initReordering(root);
    });
    initTomSelect();
    initMenuBuilder();
});

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('[data-admin-delete-form]');
    if (!form || form.dataset.confirmed === 'true') return;
    event.preventDefault();
    const result = await Swal.fire({
        title: form.dataset.deleteTitle || 'Xóa bản ghi này?',
        text: form.dataset.deleteWarning || 'Dữ liệu đã xóa không thể khôi phục.',
        icon: 'warning', showCancelButton: true, confirmButtonText: 'Xóa', cancelButtonText: 'Hủy', confirmButtonColor: '#dc3545',
    });
    if (result.isConfirmed) {
        form.dataset.confirmed = 'true';
        form.requestSubmit();
    }
});
