const initAdminTrees = () => {
    document.querySelectorAll('[data-admin-tree]').forEach((tree) => {
        if (tree.dataset.adminTreeInitialized === 'true') return;

        tree.dataset.adminTreeInitialized = 'true';

        const rows = [...tree.querySelectorAll('[data-tree-node]')];
        const search = document.querySelector(
            tree.dataset.treeSearchTarget || '[data-tree-search]',
        );
        const expandAll = document.querySelector(
            tree.dataset.treeExpandTarget || '[data-tree-expand-all]',
        );
        const collapseAll = document.querySelector(
            tree.dataset.treeCollapseTarget || '[data-tree-collapse-all]',
        );

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

        expandAll?.addEventListener('click', () => {
            collapsed.clear();
            render();
        });

        collapseAll?.addEventListener('click', () => {
            rows.forEach((row) => {
                if (
                    row.dataset.treeHasChildren === '1'
                    && Number(row.dataset.treeDepth || 0) > 0
                ) {
                    collapsed.add(String(row.dataset.treeId));
                }
            });

            render();
        });

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
    });
};

document.addEventListener('DOMContentLoaded', initAdminTrees);
