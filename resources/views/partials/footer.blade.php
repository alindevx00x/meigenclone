<footer class="app-footer">
    <div class="footer-grid">
        <!-- Brand & Mission Column -->
        <div class="footer-brand-col">
            <div class="footer-brand-header">
                @if (!empty($directorySettings['logo_url']))
                    <span class="brand-mark footer-brand-mark">
                        <img src="{{ $directorySettings['logo_url'] }}" alt="{{ $directorySettings['site_name'] }}">
                    </span>
                @else
                    <span class="brand-mark footer-brand-mark">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 18.7 8.15 6.4l3.15 6.1 2.1-3.6L22 18.7H2Z" fill="currentColor"/><path d="m7.7 14.2 2.8 1.65 2.35-4.05 3.55 6.9" stroke="#fff" stroke-width=".75" opacity=".75"/></svg>
                    </span>
                @endif
                <span class="footer-brand-name">{{ $directorySettings['site_name'] }}</span>
            </div>
            <p class="footer-mission-text">
                The open creative prompt library for Midjourney, Flux.1, Seedance, and modern AI visual models. Curated daily by the world's best prompt engineers.
            </p>
            <div class="footer-badges-row">
                <span class="footer-badge">
                    <span class="dot-green"></span> Verified Prompts
                </span>
                <span class="footer-badge badge-purple">
                    <span>⚡</span> 1-Click Copy Ready
                </span>
            </div>
            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="footer-newsletter-form">
                @csrf
                <input type="email" name="email" required placeholder="Enter your email for weekly Top 10..." class="footer-email-input">
                <button type="submit" class="primary-button footer-subscribe-btn">Subscribe</button>
            </form>
            <div class="footer-note-sub">Weekly digest powered by SMTP. No spam, unsubscribe anytime.</div>
        </div>

        <!-- Explore Prompts Column -->
        <div class="footer-col">
            <div class="footer-col-title">Explore Prompts</div>
            <ul class="footer-nav-list">
                <li><a href="{{ route('prompts.index') }}">Featured Prompts</a></li>
                <li><a href="{{ route('prompts.index', ['sort' => 'newest']) }}">Newest Prompts</a></li>
                <li><a href="{{ route('prompts.index', ['sort' => 'popular']) }}">Most Popular</a></li>
                <li><a href="{{ route('prompts.index') }}#sidebar">Categories</a></li>
            </ul>
        </div>

        <!-- Community & Tools Column -->
        <div class="footer-col">
            <div class="footer-col-title">Community</div>
            <ul class="footer-nav-list">
                <li><a href="{{ route('prompts.index', ['sort' => 'popular']) }}">Trending Today</a></li>
                <li><a href="{{ auth()->check() ? route('dashboard.index') : route('login') }}">Creator Dashboard</a></li>
                @guest
                    <li><a href="{{ route('register') }}">Publish a Prompt</a></li>
                @else
                    <li><a href="{{ route('dashboard.prompts.create') }}">Publish a Prompt</a></li>
                @endguest
                <li><a href="{{ route('sitemap') }}">XML Sitemap</a></li>
            </ul>
        </div>

        <!-- Legal & GDPR Column -->
        <div class="footer-col">
            <div class="footer-col-title">Legal &amp; Privacy</div>
            <ul class="footer-nav-list">
                <li><a href="{{ route('legal.privacy') }}">Privacy Policy</a></li>
                <li><a href="{{ route('legal.terms') }}">Terms of Service</a></li>
                <li><a href="{{ route('contact.index') }}">Contact Us</a></li>
                <li><a href="{{ route('legal.gdpr') }}" class="footer-gdpr-link"><span>🇪🇺</span> GDPR Statement</a></li>
                <li><a href="{{ route('legal.cookies') }}">Cookie Policy</a></li>
            </ul>
        </div>
    </div>

    <!-- Copyright & Disclaimer Bottom Bar -->
    <div class="footer-bottom-bar">
        <div class="footer-copy-text">
            &copy; {{ date('Y') }} {{ $directorySettings['site_name'] }}. All rights reserved. Built for creators and prompt artists worldwide.
        </div>
        <div class="footer-bottom-links">
            <a href="{{ route('legal.gdpr') }}">GDPR Compliant</a>
            <span>&bull;</span>
            <a href="{{ route('legal.privacy') }}">Privacy</a>
            <span>&bull;</span>
            <a href="{{ route('legal.terms') }}">Terms</a>
            <span>&bull;</span>
            <a href="{{ route('legal.cookies') }}">Cookies</a>
        </div>
    </div>
</footer>
