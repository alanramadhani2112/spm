const ready = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback);
    } else {
        callback();
    }
};

const ensureCloakStyle = () => {
    if (document.getElementById('x-cloak-style')) {
        return;
    }

    const style = document.createElement('style');
    style.id = 'x-cloak-style';
    style.textContent = '[x-cloak]{display:none!important}';
    document.head.appendChild(style);
};

const parseActiveTab = (value) => {
    const match = value.match(/activeTab\s*:\s*['"]([^'"]+)['"]/);
    return match ? match[1] : null;
};

const initTabState = (root) => {
    const initial = parseActiveTab(root.getAttribute('x-data') || '');

    if (!initial) {
        return;
    }

    let activeTab = initial;
    const triggers = Array.from(root.querySelectorAll('[\\@click], [x-on\\:click]'))
        .map((element) => {
            const expression = element.getAttribute('@click') || element.getAttribute('x-on:click') || '';
            const match = expression.match(/activeTab\s*=\s*['"]([^'"]+)['"]/);

            return match ? { element, tab: match[1] } : null;
        })
        .filter(Boolean);

    const panels = Array.from(root.querySelectorAll('[x-show]'))
        .map((element) => {
            const expression = element.getAttribute('x-show') || '';
            const match = expression.match(/activeTab\s*={2,3}\s*['"]([^'"]+)['"]/);

            return match ? { element, tab: match[1] } : null;
        })
        .filter(Boolean);

    const render = () => {
        panels.forEach(({ element, tab }) => {
            element.style.display = tab === activeTab ? '' : 'none';
            element.toggleAttribute('hidden', tab !== activeTab);
        });

        triggers.forEach(({ element, tab }) => {
            const isActive = tab === activeTab;
            element.classList.toggle('border-primary', isActive);
            element.classList.toggle('text-primary', isActive);
            element.classList.toggle('border-transparent', !isActive);
            element.classList.toggle('text-muted', !isActive);
        });
    };

    triggers.forEach(({ element, tab }) => {
        element.addEventListener('click', (event) => {
            event.preventDefault();
            activeTab = tab;
            render();
        });
    });

    render();
};

const initSectionState = (root) => {
    if (!/(sections\s*:\s*\[)/.test(root.getAttribute('x-data') || '')) {
        return;
    }

    const inputs = Array.from(root.querySelectorAll('[x-model="sections"]'));
    const emptyMessages = Array.from(root.querySelectorAll('[x-show="sections.length === 0"]'));

    const render = () => {
        const selectedCount = inputs.filter((input) => input.checked).length;
        emptyMessages.forEach((element) => {
            element.style.display = selectedCount === 0 ? '' : 'none';
            element.toggleAttribute('hidden', selectedCount !== 0);
        });
    };

    inputs.forEach((input) => input.addEventListener('change', render));
    render();
};

const initLiteDirectives = () => {
    document.querySelectorAll('[x-data]').forEach((root) => {
        initTabState(root);
        initSectionState(root);
    });

    document.querySelectorAll('[x-cloak]').forEach((element) => element.removeAttribute('x-cloak'));
};

const initTableSearch = () => {
    const selectors = [
        '[data-kt-filter="search"]',
        '[data-kt-admin-search="search"]',
        '[data-kt-ketua-search="search"]',
        '[data-kt-anggota-search="search"]',
        '[data-kt-sa-search="search"]',
    ];

    document.querySelectorAll(selectors.join(',')).forEach((input) => {
        const table = input.closest('.card, .table-responsive, body')?.querySelector('table');

        if (!table) {
            return;
        }

        const rows = Array.from(table.querySelectorAll('tbody tr'));
        input.addEventListener('input', () => {
            const needle = input.value.trim().toLowerCase();

            rows.forEach((row) => {
                const matches = row.textContent.toLowerCase().includes(needle);
                row.style.display = matches ? '' : 'none';
            });
        });
    });
};

const initStatusFilters = () => {
    document.querySelectorAll('[data-kt-filter="status"], [data-kt-admin-search="status"], [data-kt-ketua-search="status"], [data-kt-anggota-search="status"]').forEach((select) => {
        const table = select.closest('.card, body')?.querySelector('table');

        if (!table) {
            return;
        }

        const rows = Array.from(table.querySelectorAll('tbody tr'));
        const applyFilter = () => {
            const value = select.value;
            rows.forEach((row) => {
                const text = row.textContent.toLowerCase();
                let matches = true;

                if (value === 'completed') {
                    matches = text.includes('selesai') || text.includes('final disetujui');
                } else if (value === 'correction') {
                    matches = text.includes('koreksi');
                } else if (value === 'active') {
                    matches = !text.includes('selesai') && !text.includes('final disetujui');
                } else if (value !== 'all') {
                    matches = text.includes(value.replaceAll('-', ' '));
                }

                row.style.display = matches ? '' : 'none';
            });
        };

        select.addEventListener('change', applyFilter);
        const container = select.closest('.menu, .card');
        container?.querySelector('[data-kt-filter="apply"]')?.addEventListener('click', applyFilter);
        container?.querySelector('[data-kt-filter="reset"]')?.addEventListener('click', () => {
            select.value = 'all';
            applyFilter();
        });
    });
};

ready(() => {
    ensureCloakStyle();
    initLiteDirectives();
    initTableSearch();
    initStatusFilters();
});
