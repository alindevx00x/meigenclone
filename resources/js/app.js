import './bootstrap';
import Alpine from 'alpinejs';

Alpine.data('globalAppModal', () => ({
    searchOpen: false,
    searchQuery: '',
    searchVibe: '',
    searchResults: [],
    searchLoading: false,

    moodboardOpen: false,
    activePromptId: null,
    userMoodboards: [],
    newBoardName: '',

    tipOpen: false,
    tipCreatorId: null,
    tipCreatorName: '',
    tipCreatorHandle: '',
    tipPromptId: null,
    tipGoal: '',
    tipLink: '',
    tipAmount: 5,
    tipCustomAmount: '',
    tipNote: '',
    tipSubmitting: false,
    tipSuccess: false,
    tipSuccessMessage: '',
    tipTransactionRef: '',

    openSearch() {
        this.searchOpen = true;
        this.$nextTick(() => {
            if (this.$refs.searchInput) this.$refs.searchInput.focus();
        });
        if (this.searchResults.length === 0) {
            this.executeSearch();
        }
    },

    toggleVibe(vibe) {
        this.searchVibe = vibe;
        this.executeSearch();
    },

    async executeSearch() {
        this.searchLoading = true;
        try {
            const url = '/api/prompts/instant-search?q=' + encodeURIComponent(this.searchQuery) + '&vibe=' + encodeURIComponent(this.searchVibe);
            const res = await fetch(url);
            const data = await res.json();
            this.searchResults = data.results || [];
        } catch {
            // Ignore search errors
        } finally {
            this.searchLoading = false;
        }
    },

    async openMoodboard(detail) {
        this.activePromptId = detail.promptId;
        this.moodboardOpen = true;
        try {
            const res = await fetch('/api/my-collections?prompt_id=' + this.activePromptId);
            const data = await res.json();
            this.userMoodboards = data.collections || [];
        } catch (e) {
            console.error(e);
        }
    },

    async toggleSaveToBoard(board) {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const res = await fetch('/collections/' + board.id + '/toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ prompt_id: this.activePromptId })
            });
            const data = await res.json();
            board.contains_prompt = data.saved;
        } catch (e) {
            console.error(e);
        }
    },

    async createAndSaveBoard() {
        if (!this.newBoardName.trim()) return;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const res = await fetch('/collections', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ name: this.newBoardName, prompt_id: this.activePromptId })
            });
            const data = await res.json();
            if (data.success) {
                this.newBoardName = '';
                const refresh = await fetch('/api/my-collections?prompt_id=' + this.activePromptId);
                const refData = await refresh.json();
                this.userMoodboards = refData.collections || [];
            }
        } catch (e) {
            console.error(e);
        }
    },

    openTip(detail) {
        this.tipCreatorId = detail.creatorId;
        this.tipCreatorName = detail.creatorName;
        this.tipCreatorHandle = detail.creatorHandle || '';
        this.tipPromptId = detail.promptId || null;
        this.tipGoal = detail.tipGoal || '';
        this.tipLink = detail.tipLink || '';
        this.tipAmount = 5;
        this.tipCustomAmount = '';
        this.tipNote = '';
        this.tipSuccess = false;
        this.tipOpen = true;
    },

    async submitTip() {
        const amount = this.tipCustomAmount ? parseFloat(this.tipCustomAmount) : parseFloat(this.tipAmount);
        if (!amount || amount < 1) {
            alert('Please select or enter an amount of at least $1.');
            return;
        }
        this.tipSubmitting = true;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const res = await fetch('/creators/' + this.tipCreatorId + '/tip', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({
                    amount: amount,
                    message: this.tipNote,
                    prompt_id: this.tipPromptId,
                    payment_gateway: 'platform'
                })
            });
            const data = await res.json();
            if (data.success) {
                this.tipSuccess = true;
                this.tipSuccessMessage = data.message;
                this.tipTransactionRef = data.tip?.reference || '';
            } else {
                alert(data.message || 'Could not complete tip.');
            }
        } catch (e) {
            console.error(e);
            alert('Network error while processing tip.');
        } finally {
            this.tipSubmitting = false;
        }
    }
}));

Alpine.data('remixStudio', (config = {}) => ({
    initialPrompt: config.initial || '',
    prompt: config.initial || '',
    activeTags: [],
    snippetsMap: {},
    copied: false,

    resetPrompt() {
        this.prompt = this.initialPrompt;
        this.activeTags = [];
        this.snippetsMap = {};
    },

    toggleSnippet(name, snippet) {
        const idx = this.activeTags.indexOf(name);
        if (idx > -1) {
            this.activeTags.splice(idx, 1);
            this.prompt = this.prompt.replace(', ' + snippet, '').replace(snippet, '').trim();
            delete this.snippetsMap[name];
        } else {
            this.activeTags.push(name);
            this.snippetsMap[name] = snippet;
            this.prompt = this.prompt.trim() + (this.prompt.trim() ? ', ' : '') + snippet;
        }
    },

    removeTag(name) {
        const snippet = this.snippetsMap[name];
        if (snippet) {
            this.prompt = this.prompt.replace(', ' + snippet, '').replace(snippet, '').trim();
        }
        this.activeTags = this.activeTags.filter(t => t !== name);
        delete this.snippetsMap[name];
    },

    setParam(type, val) {
        this.prompt = this.prompt.replace(/--ar\s+\d+:\d+/g, '').trim();
        this.prompt += ' ' + val;
    },

    toggleParam(p) {
        if (this.prompt.includes(p)) {
            this.prompt = this.prompt.replace(p, '').replace(/\s+/g, ' ').trim();
        } else {
            this.prompt += ' ' + p;
        }
    },

    toggleSref(code) {
        const srefTag = '--sref ' + code;
        if (this.prompt.includes(srefTag)) {
            this.prompt = this.prompt.replace(srefTag, '').replace(/\s+/g, ' ').trim();
        } else {
            this.prompt = this.prompt.replace(/--sref\s+\d+/g, '').trim();
            this.prompt += ' ' + srefTag;
        }
    },

    async copyPrompt() {
        try {
            await navigator.clipboard.writeText(this.prompt);
        } catch {
            const input = document.createElement('textarea');
            input.value = this.prompt;
            document.body.append(input);
            input.select();
            document.execCommand('copy');
            input.remove();
        }
        this.copied = true;
        setTimeout(() => this.copied = false, 2000);
    },

    sendToGenerator() {
        const url = '/generate?prompt=' + encodeURIComponent(this.prompt);
        window.location.href = url;
    }
}));

Alpine.data('generationStudio', (config = {}) => ({
    promptText: config.prefilled || '',
    promptId: config.promptId || null,
    credits: config.userCredits !== undefined ? config.userCredits : 25,
    model: 'flux-schnell',
    provider: 'flux',
    aspectRatio: '1:1',
    apiKey: '',
    isLoading: false,
    resultImage: null,
    resultModel: '',
    resultAspect: '',
    generationId: null,

    clearPrompt() {
        this.promptText = '';
    },

    async generateImage() {
        if (!this.promptText.trim()) return;

        this.isLoading = true;
        this.resultImage = null;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        try {
            const response = await fetch('/generations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({
                    prompt_text: this.promptText,
                    provider: this.provider,
                    model: this.model,
                    aspect_ratio: this.aspectRatio,
                    api_key: this.apiKey,
                    prompt_id: this.promptId
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                this.resultImage = data.generation.image_url;
                this.resultModel = data.generation.model;
                this.resultAspect = data.generation.aspect_ratio;
                this.generationId = data.generation.id;
                if (data.credits_remaining !== null && data.credits_remaining !== undefined) {
                    this.credits = data.credits_remaining;
                }
            } else {
                alert(data.message || 'Generation failed. Please try again.');
            }
        } catch (err) {
            alert('A network error occurred during generation: ' + err.message);
        } finally {
            this.isLoading = false;
        }
    }
}));

window.Alpine = Alpine;
Alpine.start();

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
        const promptCard = copyButton.closest('.prompt-card') || copyButton.closest('[data-prompt-id]');
        const promptId = copyButton.getAttribute('data-prompt-id') || promptCard?.getAttribute('data-prompt-id');
        if (promptId) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch('/api/prompts/' + promptId + '/track-copy', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
            }).catch(() => {});
        }

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

    const discordBtn = event.target.closest('[data-discord-copy]');
    if (discordBtn) {
        const val = discordBtn.getAttribute('data-discord-copy') || '';
        try {
            await navigator.clipboard.writeText(val);
            showToast('Copied Discord /imagine prompt command!');
        } catch {
            const input = document.createElement('textarea');
            input.value = val;
            input.style.position = 'fixed';
            input.style.opacity = '0';
            document.body.append(input);
            input.select();
            document.execCommand('copy');
            input.remove();
            showToast('Copied Discord /imagine prompt command!');
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

document.addEventListener('submit', async (event) => {
    const favoriteForm = event.target.closest('.favorite-form');
    if (!favoriteForm) return;

    if (favoriteForm.action.includes('/login')) return;

    event.preventDefault();
    event.stopPropagation();

    const button = favoriteForm.querySelector('button[type="submit"], .favorite-button');
    if (!button || button.disabled) return;

    const card = favoriteForm.closest('.prompt-card');
    const likesBadge = card ? card.querySelector('.prompt-likes') : null;
    const detailStats = document.querySelector('.detail-stats span:first-child');
    const favHeadingCount = document.querySelector('#favorites .heading-count');

    const isCardButton = button.classList.contains('favorite-button');
    const isCurrentlyFavorited = isCardButton
        ? button.classList.contains('is-favorite')
        : button.classList.contains('saved');

    const nextFavorited = !isCurrentlyFavorited;

    // Optimistic UI updates
    if (isCardButton) {
        button.classList.toggle('is-favorite', nextFavorited);
        button.textContent = nextFavorited ? '♥' : '♡';
        button.setAttribute('aria-label', nextFavorited ? 'Remove from saved' : 'Save prompt');
        if (likesBadge) {
            const currentMatch = likesBadge.textContent.match(/\d[,\d]*/);
            if (currentMatch) {
                const count = parseInt(currentMatch[0].replace(/,/g, ''), 10) || 0;
                const nextCount = Math.max(0, count + (nextFavorited ? 1 : -1));
                likesBadge.textContent = `♥ ${nextCount.toLocaleString()}`;
            }
        }
    } else {
        button.classList.toggle('saved', nextFavorited);
        button.textContent = nextFavorited ? '♥ Saved' : '♡ Save';
        if (detailStats) {
            const currentMatch = detailStats.textContent.match(/\d[,\d]*/);
            if (currentMatch) {
                const count = parseInt(currentMatch[0].replace(/,/g, ''), 10) || 0;
                const nextCount = Math.max(0, count + (nextFavorited ? 1 : -1));
                detailStats.textContent = `♥ ${nextCount.toLocaleString()} saves`;
            }
        }
    }

    if (favHeadingCount) {
        const count = parseInt(favHeadingCount.textContent.replace(/,/g, ''), 10) || 0;
        favHeadingCount.textContent = Math.max(0, count + (nextFavorited ? 1 : -1)).toLocaleString();
    }

    showToast(nextFavorited ? 'Saved to your favorites' : 'Removed from saved prompts');

    button.style.transform = 'scale(1.25)';
    setTimeout(() => {
        button.style.transform = '';
    }, 180);

    const csrfToken = favoriteForm.querySelector('input[name="_token"]')?.value
        || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    try {
        const response = await fetch(favoriteForm.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(new FormData(favoriteForm)).toString()
        });

        if (response.status === 401) {
            window.location.href = '/login';
            return;
        }

        const data = await response.json();
        if (data && data.success) {
            if (isCardButton) {
                button.classList.toggle('is-favorite', data.is_favorited);
                button.textContent = data.is_favorited ? '♥' : '♡';
                if (likesBadge && data.formatted_likes !== undefined) {
                    likesBadge.textContent = `♥ ${data.formatted_likes}`;
                }
            } else {
                button.classList.toggle('saved', data.is_favorited);
                button.textContent = data.is_favorited ? '♥ Saved' : '♡ Save';
                if (detailStats && data.formatted_likes !== undefined) {
                    detailStats.textContent = `♥ ${data.formatted_likes} saves`;
                }
            }
        } else {
            revert();
        }
    } catch {
        revert();
    }

    function revert() {
        if (isCardButton) {
            button.classList.toggle('is-favorite', isCurrentlyFavorited);
            button.textContent = isCurrentlyFavorited ? '♥' : '♡';
        } else {
            button.classList.toggle('saved', isCurrentlyFavorited);
            button.textContent = isCurrentlyFavorited ? '♥ Saved' : '♡ Save';
        }
        showToast('Could not update favorite. Please try again.');
    }
});

if (new URLSearchParams(window.location.search).get('focus') === 'search') {
    window.setTimeout(() => document.querySelector('#prompt-search input')?.focus(), 150);
}
