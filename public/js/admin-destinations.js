(() => {
    const manager = document.querySelector('[data-destination-manager]');

    if (!manager) return;

    const treePanel = manager.querySelector('[data-destination-tree-panel]');
    const listPanel = manager.querySelector('[data-destination-list-panel]');
    const viewButtons = [...manager.querySelectorAll('[data-destination-view]')];
    const listFilterForm = manager.querySelector('[data-destination-list-filter]');
    const listFilterPanel = listFilterForm?.closest('.admin-filter-panel');

    const setView = (view) => {
        const normalized = view === 'list' ? 'list' : 'tree';

        if (treePanel) treePanel.hidden = normalized !== 'tree';
        if (listPanel) listPanel.hidden = normalized !== 'list';
        if (listFilterPanel) listFilterPanel.hidden = normalized !== 'list';

        viewButtons.forEach((button) => {
            const active = button.dataset.destinationView === normalized;

            button.classList.toggle('btn-primary', active);
            button.classList.toggle('btn-default', !active);
            button.setAttribute('aria-pressed', String(active));
        });
    };

    viewButtons.forEach((button) => {
        button.addEventListener('click', () => {
            setView(button.dataset.destinationView);
        });
    });

    setView(manager.dataset.defaultView || 'tree');

    const modalElement = document.getElementById('quickDestinationModal');
    const quickForm = manager.querySelector('[data-quick-destination-form]');
    const parentSelect = quickForm?.querySelector('#quick-destination-parent');
    const typeSelect = quickForm?.querySelector('#quick-destination-type');
    const marketSelect = quickForm?.querySelector('#quick-destination-market');
    const parentLabel = quickForm?.querySelector('[data-quick-parent-label]');
    const nameInput = quickForm?.querySelector('#quick-destination-name');
    const parentMetaElement = manager.querySelector('[data-destination-parent-meta]');
    const parentField = parentSelect?.closest('.col-md-6');
    const typeField = typeSelect?.closest('.col-md-6');
    const marketField = marketSelect?.closest('.col-md-6');
    const advancedAccordion = quickForm?.querySelector('#quickDestinationAdvancedAccordion');

    let parentMeta = {};

    try {
        parentMeta = JSON.parse(parentMetaElement?.textContent || '{}');
    } catch {
        parentMeta = {};
    }

    const typeOptions = typeSelect
        ? [...typeSelect.options]
            .filter((option) => option.value !== '')
            .map((option) => ({
                value: option.value,
                label: option.textContent,
            }))
        : [];

    const replaceTypeOptions = (allowedTypes, preferredValue = '') => {
        if (!typeSelect) return;

        const allowed = new Set(allowedTypes || []);
        const options = typeOptions.filter((option) => allowed.has(option.value));

        typeSelect.replaceChildren(
            ...options.map((option) => new Option(option.label, option.value)),
        );

        const nextValue = options.some((option) => option.value === preferredValue)
            ? preferredValue
            : options.some((option) => option.value === 'city')
                ? 'city'
                : (options[0]?.value || '');

        typeSelect.value = nextValue;
    };

    const currentParentMeta = () => {
        const parentId = String(parentSelect?.value || '');

        return parentMeta[parentId || '__root__'] || parentMeta.__root__ || {
            name: 'Điểm đến gốc',
            market: 'international',
            allowed_child_types: ['continent'],
        };
    };

    const syncQuickHierarchy = ({ scoped = false, preserveType = false } = {}) => {
        if (!parentSelect) return;

        const parentId = String(parentSelect.value || '');
        const meta = currentParentMeta();
        const allowedTypes = Array.isArray(meta.allowed_child_types)
            ? meta.allowed_child_types
            : [];
        const previousType = typeSelect?.value || '';

        replaceTypeOptions(
            allowedTypes,
            preserveType ? previousType : '',
        );

        if (marketSelect) {
            marketSelect.value = meta.market || 'international';
        }

        if (parentField) {
            parentField.hidden = scoped;
        }

        const showType = allowedTypes.length > 1;
        const showMarket = parentId === '';

        if (typeField) typeField.hidden = !showType;
        if (marketField) marketField.hidden = !showMarket;
        if (advancedAccordion) advancedAccordion.hidden = !showType && !showMarket;

        if (parentLabel) {
            parentLabel.textContent = scoped && parentId
                ? `Thêm điểm đến vào ${meta.name}`
                : parentId
                    ? `${meta.name} · chỉ hiển thị loại điểm đến hợp lệ`
                    : 'Thêm điểm đến gốc';
        }
    };

    parentSelect?.addEventListener('change', () => {
        syncQuickHierarchy({ scoped: false });
    });

    const openQuickModal = ({
        parentId = '',
        preserveConfig = false,
        scoped = Boolean(parentId),
    } = {}) => {
        if (!modalElement || !parentSelect || !window.bootstrap?.Modal) {
            return;
        }

        parentSelect.value = String(parentId || '');

        syncQuickHierarchy({
            scoped,
            preserveType: preserveConfig,
        });

        bootstrap.Modal.getOrCreateInstance(modalElement).show();

        modalElement.addEventListener(
            'shown.bs.modal',
            () => nameInput?.focus(),
            { once: true },
        );
    };

    manager.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-quick-destination]');

        if (!trigger) return;

        event.preventDefault();

        const parentId = trigger.dataset.parentId || '';

        openQuickModal({
            parentId,
            scoped: parentId !== '',
        });
    });

    if (manager.dataset.autoOpenQuick === '1') {
        const parentId = parentSelect?.value || '';

        openQuickModal({
            parentId,
            preserveConfig: manager.dataset.validationReopen === '1',
            scoped: parentId !== '',
        });
    }
})();
