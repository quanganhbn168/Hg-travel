(() => {
    'use strict';
    const config = () => window.hgMediaConfig;
    const headers = () => ({ Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content });
    const request = async (url, options = {}) => {
        const response = await fetch(url, { ...options, headers: { ...headers(), ...(options.headers || {}) } });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || (response.status === 413 ? 'File vượt giới hạn máy chủ.' : 'Không thể xử lý tệp. Vui lòng thử lại.'));
        return data;
    };
    const uploadCounts = new WeakMap();
    const busy = (form, amount) => {
        if (!form) return;
        const count = Math.max(0, (uploadCounts.get(form) || 0) + amount);
        uploadCounts.set(form, count);
        Array.from(form.elements).filter(button => button.type === 'submit').forEach(button => {
            if (count && button.dataset.mediaWasDisabled === undefined) button.dataset.mediaWasDisabled = button.disabled ? '1' : '0';
            if (count) button.disabled = true;
            else if (button.dataset.mediaWasDisabled !== undefined) { button.disabled = button.dataset.mediaWasDisabled === '1'; delete button.dataset.mediaWasDisabled; }
        });
    };
    document.addEventListener('submit', event => {
        if ((uploadCounts.get(event.target) || 0) > 0) { event.preventDefault(); event.stopImmediatePropagation(); }
    }, true);
    const element = (tag, className, text) => { const node = document.createElement(tag); node.className = className; if (text) node.textContent = text; return node; };
    const picker = async (callback) => {
        const dialog = element('dialog', 'hg-media-dialog border rounded shadow p-3');
        dialog.style.cssText = 'width:min(900px,95vw);max-height:85vh;z-index:100000';
        const top = element('div', 'd-flex gap-2 mb-3');
        const search = element('input', 'form-control'); search.placeholder = 'Tìm tên ảnh'; search.setAttribute('aria-label', 'Tìm ảnh');
        const close = element('button', 'btn btn-outline-secondary', 'Đóng'); close.type = 'button'; close.onclick = () => dialog.close();
        top.append(search, close);
        const grid = element('div', 'row g-2');
        const status = element('p', 'small mt-2'); status.setAttribute('role', 'status');
        const more = element('button', 'btn btn-outline-primary mt-3', 'Xem thêm'); more.type = 'button'; more.hidden = true;
        dialog.append(top, grid, status, more); document.body.append(dialog); dialog.showModal();
        dialog.addEventListener('close', () => dialog.remove(), { once: true });
        let generation = 0;
        const load = async (url, append = false) => {
            const current = ++generation; more.disabled = true; status.textContent = 'Đang tải ảnh…';
            try {
                const data = await request(url);
                if (current !== generation || !dialog.open) return;
                if (!append) grid.replaceChildren();
                data.data.forEach(item => {
                    if (!item.url) return;
                    const column = element('div', 'col-6 col-md-3');
                    const button = element('button', 'btn border w-100 h-100 text-start p-2'); button.type = 'button'; button.title = item.name;
                    const image = element('img', 'w-100 rounded'); image.src = item.thumbnail_url || item.url; image.alt = item.name; image.style.cssText = 'height:110px;object-fit:contain';
                    const name = element('div', 'small text-truncate mt-1', item.name);
                    button.append(image, name); button.onclick = () => { callback(item); dialog.close(); }; column.append(button); grid.append(column);
                });
                status.textContent = `${data.total} ảnh`; more.hidden = !data.next_page_url; more.onclick = () => load(data.next_page_url, true);
            } catch (error) { status.textContent = error.message; }
            finally { more.disabled = false; }
        };
        let timer;
        search.oninput = () => { clearTimeout(timer); timer = setTimeout(() => load(`${config().list}?q=${encodeURIComponent(search.value)}`), 250); };
        await load(config().list);
    };
    const initialize = root => {
        if (root.dataset.initialized) return;
        root.dataset.initialized = '1';
        const form = root.closest('form');
        const input = root.querySelector('[data-media-value]');
        const remove = root.querySelector('[data-media-remove]');
        const previews = root.querySelector('[data-media-previews]');
        const status = root.querySelector('[data-media-status]');
        const max = Number(root.dataset.maxFiles);
        let items = JSON.parse(root.dataset.items || '[]');
        const render = () => {
            input.value = items.map(item => item.reference).join('|'); previews.replaceChildren();
            items.forEach((item, index) => {
                const card = element('div', 'border rounded p-1'); card.style.width = '132px';
                if (item.url) { const image = element('img', 'w-100 rounded'); image.src = item.url; image.alt = item.name || 'Ảnh'; image.style.cssText = 'height:100px;object-fit:contain'; card.append(image); }
                else card.append(element('div', 'small text-danger p-2', 'Không tìm thấy file ảnh. Hãy chọn ảnh thay thế.'));
                const button = element('button', 'btn btn-sm btn-link text-danger', 'Gỡ'); button.type = 'button';
                button.onclick = () => { items.splice(index, 1); remove.value = items.length ? '0' : '1'; render(); }; card.append(button); previews.append(card);
            });
        };
        const select = item => {
            if (max === 1) items = [item];
            else if (!items.some(current => current.reference === item.reference)) {
                if (items.length >= max) { status.textContent = `Tối đa ${max} ảnh mỗi lần.`; return; }
                items.push(item);
            }
            remove.value = '0'; render();
        };
        root.querySelector('[data-media-picker]').onclick = () => picker(select);
        root.querySelector('[data-media-clear]').onclick = () => { items = []; remove.value = '1'; render(); };
        new Dropzone(root.querySelector('[data-media-dropzone]'), {
            url: config().upload, maxFilesize: Number(root.dataset.maxSize), acceptedFiles: root.dataset.acceptedFiles || '.disabled',
            parallelUploads: 1, uploadMultiple: false, headers: headers(), timeout: 120000,
            init() {
                this.on('addedfile', file => { file.hgBusy = true; busy(form, 1); });
                this.on('uploadprogress', (file, progress) => { status.textContent = `Đang tải ${Math.round(progress)}%`; });
                this.on('success', (file, data) => { select({ reference: data.path, url: data.url, name: file.name }); status.textContent = 'Đã tải ảnh. Bấm lưu để cập nhật nội dung.'; });
                this.on('error', (file, error) => { status.textContent = typeof error === 'string' ? error : Object.values(error.errors || {}).flat()[0] || error.message || 'Không thể tải ảnh.'; });
                this.on('complete', file => { if (file.hgBusy) { busy(form, -1); file.hgBusy = false; } this.removeFile(file); });
            }
        });
        render();
    };
    window.HgMedia = { request, picker, busy, headers };
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.hg-image-upload').forEach(initialize);
        document.querySelectorAll('[data-tour-gallery-sort]').forEach(root => new Sortable(root, { handle: '[data-image-handle]', animation: 150 }));
    });
})();
