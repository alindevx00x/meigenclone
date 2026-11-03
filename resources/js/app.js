import './bootstrap';

const toast = document.getElementById('copy-toast');
let toastTimer;

function showToast(message) {
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('is-visible');
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(() => toast.classList.remove('is-visible'), 1900);
}

const masonryRowSize = 7;
const masonryGrids = [...document.querySelectorAll('.prompt-grid')];
const masonryObservers = [];
const pendingMasonryGrids = new Set();
let masonryFrame = 0;

function layoutMasonry(grid) {
    const cards = [...grid.querySelectorAll('.prompt-card')];
    if (!cards.length) return;

    grid.classList.add('is-measuring');
    const rowGap = Number.parseFloat(getComputedStyle(grid).rowGap) || 0;
    const heights = cards.map((card) => card.getBoundingClientRect().height);
    grid.classList.remove('is-measuring');
    grid.classList.add('is-masonry');

    cards.forEach((card, index) => {
        const rowSpan = Math.max(1, Math.ceil((heights[index] + rowGap) / (masonryRowSize + rowGap)));
        card.style.gridRowEnd = `span ${rowSpan}`;
    });
}

function scheduleMasonryLayout(grid) {
    pendingMasonryGrids.add(grid);
    if (masonryFrame) return;

    masonryFrame = window.requestAnimationFrame(() => {
        pendingMasonryGrids.forEach(layoutMasonry);
        pendingMasonryGrids.clear();
        masonryFrame = 0;
    });
}

masonryGrids.forEach((grid) => {
    layoutMasonry(grid);

    if ('ResizeObserver' in window) {
        const observer = new ResizeObserver(() => scheduleMasonryLayout(grid));
        masonryObservers.push(observer);
        grid.querySelectorAll('.prompt-image, .prompt-card-body').forEach((element) => observer.observe(element));
    }
});

window.addEventListener('resize', () => masonryGrids.forEach(scheduleMasonryLayout), { passive: true });

document.addEventListener('click', async (event) => {
    const menuToggle = event.target.closest('[data-user-menu-toggle]');
    if (menuToggle) {
        const menu = menuToggle.closest('[data-user-menu]');
        const willOpen = !menu.classList.contains('is-open');
        document.querySelectorAll('[data-user-menu]').forEach((item) => {
            item.classList.remove('is-open');
            item.querySelector('[data-user-menu-toggle]')?.setAttribute('aria-expanded', 'false');
        });
        menu.classList.toggle('is-open', willOpen);
        menuToggle.setAttribute('aria-expanded', String(willOpen));
        return;
    }

    if (!event.target.closest('[data-user-menu]')) {
        document.querySelectorAll('[data-user-menu].is-open').forEach((menu) => {
            menu.classList.remove('is-open');
            menu.querySelector('[data-user-menu-toggle]')?.setAttribute('aria-expanded', 'false');
        });
    }

    const copyButton = event.target.closest('[data-copy]');
    if (copyButton) {
        const value = copyButton.getAttribute('data-copy') || '';
        try {
            await navigator.clipboard.writeText(value);
            showToast('Prompt copied to clipboard');
        } catch {
            const input = document.createElement('textarea');
            input.value = value;
            input.style.position = 'fixed';
            input.style.opacity = '0';
            document.body.append(input);
            input.select();
            document.execCommand('copy');
            input.remove();
            showToast('Prompt copied to clipboard');
        }
    }

    if (event.target.closest('[data-dismiss]')) {
        event.target.closest('.flash')?.remove();
    }

    if (event.target.closest('[data-sidebar-toggle]')) {
        document.body.classList.toggle('sidebar-open');
    }
});

document.addEventListener('keydown', (event) => {
    const isCommandK = (event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k';
    const isSlash = event.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement?.tagName);
    if (isCommandK || isSlash) {
        event.preventDefault();
        document.querySelector('#prompt-search input, .top-search input')?.focus();
    }
    if (event.key === 'Escape') document.body.classList.remove('sidebar-open');
    if (event.key === 'Escape') {
        document.querySelectorAll('[data-user-menu].is-open').forEach((menu) => {
            menu.classList.remove('is-open');
            const toggle = menu.querySelector('[data-user-menu-toggle]');
            toggle?.setAttribute('aria-expanded', 'false');
            toggle?.focus();
        });
    }
});

if (new URLSearchParams(window.location.search).get('focus') === 'search') {
    window.setTimeout(() => document.querySelector('#prompt-search input')?.focus(), 150);
}
