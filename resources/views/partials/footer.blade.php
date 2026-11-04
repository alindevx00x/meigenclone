<footer class="app-footer" style="border-top: 1px solid rgba(230, 230, 235, 0.8); background: #ffffff; padding: 48px 32px 36px; margin-top: 60px;">
    <div style="max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 36px; margin-bottom: 40px;" class="footer-grid">
        <!-- Brand & Mission Column -->
        <div style="display: flex; flex-direction: column; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="brand-mark" style="width: 22px; height: 22px; color: #17171b;">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" style="width: 22px; height: 22px;"><path d="M2 18.7 8.15 6.4l3.15 6.1 2.1-3.6L22 18.7H2Z" fill="currentColor"/><path d="m7.7 14.2 2.8 1.65 2.35-4.05 3.55 6.9" stroke="#fff" stroke-width=".75" opacity=".75"/></svg>
                </span>
                <span style="font-size: 19px; font-weight: 700; color: #17171b; letter-spacing: -0.5px;">{{ $directorySettings['site_name'] }}</span>
                <span style="font-family: Georgia, serif; font-style: italic; color: #888; font-size: 13.5px;">Studio</span>
            </div>
            <p style="font-size: 13px; line-height: 1.6; color: #6b7280; max-width: 380px; margin: 0;">
                The open creative prompt library and remixing platform for Midjourney, Flux.1, Seedance, and modern AI visual models. Curated daily by the world's best prompt engineers.
            </p>
            <div style="display: flex; gap: 10px; margin-top: 4px;">
                <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 99px; background: #f3f4f6; color: #4b5563; font-size: 11px; font-weight: 500;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span> 1,800+ Verified Prompts
                </span>
                <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 99px; background: #f5f3ff; color: #6d28d9; font-size: 11px; font-weight: 500;">
                    <span>🪄</span> Fast Remix Studio
                </span>
            </div>
            <form action="{{ route('newsletter.subscribe') }}" method="POST" style="margin-top: 10px; display: flex; gap: 6px; max-width: 380px;">
                @csrf
                <input type="email" name="email" required placeholder="Enter your email for weekly Top 10..." style="flex: 1; min-width: 0; padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 12px; outline: none;">
                <button type="submit" class="primary-button" style="min-height: 34px; padding: 0 14px; font-size: 12px; border-radius: 8px;">Subscribe</button>
            </form>
            <div style="font-size: 11px; color: #9ca3af;">Weekly digest powered by SMTP. No spam, unsubscribe anytime.</div>
        </div>

        <!-- Studio & Explore Column -->
        <div>
            <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #1f2937; margin-bottom: 16px;">Explore Studio</div>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                <li><a href="{{ route('prompts.index') }}" style="color: #6b7280; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">Featured Prompts</a></li>
                <li><a href="{{ route('studio.remix') }}" style="color: #6b7280; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">Remix Studio</a></li>
                <li><a href="{{ route('srefs.index') }}" style="color: #6b7280; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">SREF Style Codes</a></li>
                <li><a href="{{ route('packs.index') }}" style="color: #6b7280; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">Prompt Packs</a></li>
                <li><a href="{{ route('collections.index') }}" style="color: #6b7280; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">Moodboard Collections</a></li>
            </ul>
        </div>

        <!-- Community & Tools Column -->
        <div>
            <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #1f2937; margin-bottom: 16px;">Community</div>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                <li><a href="{{ route('prompts.index', ['sort' => 'popular']) }}" style="color: #6b7280; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">Trending Today</a></li>
                <li><a href="{{ auth()->check() ? route('dashboard.index') : route('login') }}" style="color: #6b7280; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">Creator Dashboard</a></li>
                @guest
                    <li><a href="{{ route('register') }}" style="color: #6b7280; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">Publish a Prompt</a></li>
                @else
                    <li><a href="{{ route('dashboard.prompts.create') }}" style="color: #6b7280; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">Publish a Prompt</a></li>
                @endguest
                <li><a href="{{ route('sitemap') }}" style="color: #6b7280; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">XML Sitemap</a></li>
            </ul>
        </div>

        <!-- Legal & GDPR Column -->
        <div>
            <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #1f2937; margin-bottom: 16px;">Legal &amp; Privacy</div>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                <li><a href="{{ route('legal.privacy') }}" style="color: #6b7280; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">Privacy Policy</a></li>
                <li><a href="{{ route('legal.terms') }}" style="color: #6b7280; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">Terms of Service</a></li>
                <li><a href="{{ route('legal.gdpr') }}" style="display: inline-flex; align-items: center; gap: 5px; color: #4f46e5; font-weight: 600; text-decoration: none;"><span>🇪🇺</span> GDPR Statement</a></li>
                <li><a href="{{ route('legal.cookies') }}" style="color: #6b7280; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">Cookie Policy</a></li>
            </ul>
        </div>
    </div>

    <!-- Copyright & Disclaimer Bottom Bar -->
    <div style="max-width: 1400px; margin: 0 auto; padding-top: 24px; border-top: 1px solid #f3f4f6; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; font-size: 12px; color: #9ca3af;">
        <div>
            &copy; {{ date('Y') }} {{ $directorySettings['site_name'] }}. All rights reserved. Built for creators and prompt artists worldwide.
        </div>
        <div style="display: flex; gap: 16px; align-items: center;">
            <a href="{{ route('legal.gdpr') }}" style="color: #9ca3af; text-decoration: none;">GDPR Compliant</a>
            <span>&bull;</span>
            <a href="{{ route('legal.privacy') }}" style="color: #9ca3af; text-decoration: none;">Privacy</a>
            <span>&bull;</span>
            <a href="{{ route('legal.terms') }}" style="color: #9ca3af; text-decoration: none;">Terms</a>
            <span>&bull;</span>
            <a href="{{ route('legal.cookies') }}" style="color: #9ca3af; text-decoration: none;">Cookies</a>
        </div>
    </div>
</footer>
