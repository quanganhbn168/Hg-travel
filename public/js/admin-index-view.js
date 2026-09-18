const initAdminIndexViews = () => {
    document.querySelectorAll('[data-index-view-manager]').forEach((manager) => {
        if (manager.dataset.indexViewInitialized === 'true') return;

        manager.dataset.indexViewInitialized = 'true';

        const treePanel = manager.querySelector('[data-index-tree-panel]');
        const listPanel = manager.querySelector('[data-index-list-panel]');
        const viewButtons = [...manager.querySelectorAll('[data-index-view]')];
        const listFilterForm = manager.querySelector('[data-index-list-filter]');
        const listFilterPanel = listFilterForm?.closest('.admin-filter-panel');

        const setView = (view) => {
            const normalized = view === 'list' ? 'list' : 'tree';

            if (treePanel) treePanel.hidden = normalized !== 'tree';
            if (listPanel) listPanel.hidden = normalized !== 'list';
            if (listFilterPanel) listFilterPanel.hidden = normalized !== 'list';

            viewButtons.forEach((button) => {
                const active = button.dataset.indexView === normalized;

                button.classList.toggle('btn-primary', active);
                button.classList.toggle('btn-default', !active);
                button.setAttribute('aria-pressed', String(active));
            });
        };

        viewButtons.forEach((button) => {
            button.addEventListener('click', () => {
                setView(button.dataset.indexView);
            });
        });

        setView(manager.dataset.defaultView || 'tree');
    });
};

document.addEventListener('DOMContentLoaded', initAdminIndexViews);
