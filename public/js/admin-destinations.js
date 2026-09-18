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

    const tree = manager.querySelector('[data-admin-tree]');

    if (tree) {
        const rows = [...tree.querySelectorAll('[data-tree-node]')];
        const byId = new Map(
            rows.map((row) => [String(row.dataset.treeId), row]),
        );
        const children = new Map();

        rows.forEach((row) => {
            const parentId = String(row.dataset.treeParent || '');

            if (!children.has(parentId)) {
                children.set(parentId, []);
            }

            children.get(parentId).push(row);
        });

        const collapsed = new Set();

        rows.forEach((row) => {
            const depth = Number(row.dataset.treeDepth || 0);
            const hasChildren = row.dataset.treeHasChildren === '1';

            if (hasChildren && depth > 0) {
                collapsed.add(String(row.dataset.treeId));
            }
        });

        const hasCollapsedAncestor = (row) => {
            let parentId = String(row.dataset.treeParent || '');
            const visited = new Set();

            while (parentId && !visited.has(parentId)) {
                visited.add(parentId);

                if (collapsed.has(parentId)) {
                    return true;
                }

                parentId = String(
                    byId.get(parentId)?.dataset.treeParent || '',
                );
            }

            return false;
        };

        const syncToggle = (row) => {
            const toggle = row.querySelector('[data-tree-toggle]');

            if (!toggle) return;

            const id = String(row.dataset.treeId);
            const expanded = !collapsed.has(id);
            const icon = toggle.querySelector('i');

            toggle.setAttribute('aria-expanded', String(expanded));
            icon?.classList.toggle('bi-chevron-down', expanded);
            icon?.classList.toggle('bi-chevron-right', !expanded);
        };

        const render = () => {
            rows.forEach((row) => {
                row.hidden = hasCollapsedAncestor(row);
                syncToggle(row);
            });
        };

        tree.addEventListener('click', (event) => {
            const toggle = event.target.closest('[data-tree-toggle]');

            if (!toggle) return;

            const row = toggle.closest('[data-tree-node]');

            if (!row) return;

            const id = String(row.dataset.treeId);

            if (collapsed.has(id)) {
                collapsed.delete(id);
            } else {
                collapsed.add(id);
            }

            render();
        });

        manager
            .querySelector('[data-tree-expand-all]')
            ?.addEventListener('click', () => {
                collapsed.clear();
                render();
            });

        manager
            .querySelector('[data-tree-collapse-all]')
            ?.addEventListener('click', () => {
                rows.forEach((row) => {
                    const hasChildren = row.dataset.treeHasChildren === '1';
                    const depth = Number(row.dataset.treeDepth || 0);

                    if (hasChildren && depth > 0) {
                        collapsed.add(String(row.dataset.treeId));
                    }
                });

                render();
            });

        const search = manager.querySelector('[data-tree-search]');

        search?.addEventListener('input', () => {
            const query = search.value
                .trim()
                .toLocaleLowerCase('vi-VN');

            if (!query) {
                render();
                return;
            }

            const visible = new Set();

            const addAncestors = (row) => {
                let current = row;
                const visited = new Set();

                while (current) {
                    const currentId = String(current.dataset.treeId);

                    if (visited.has(currentId)) break;

                    visited.add(currentId);
                    visible.add(currentId);

                    const parentId = String(
                        current.dataset.treeParent || '',
                    );

                    current = parentId ? byId.get(parentId) : null;
                }
            };

            const addDescendants = (row) => {
                const id = String(row.dataset.treeId);

                visible.add(id);

                (children.get(id) || []).forEach(addDescendants);
            };

            rows.forEach((row) => {
                const haystack = (
                    row.dataset.treeSearch || ''
                ).toLocaleLowerCase('vi-VN');

                if (!haystack.includes(query)) return;

                addAncestors(row);
                addDescendants(row);
            });

            rows.forEach((row) => {
                row.hidden = !visible.has(String(row.dataset.treeId));
            });
        });

        render();
    }

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
