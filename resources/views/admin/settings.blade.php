@extends('layouts.app')
@section('title', 'Directory settings')
@section('eyebrow', 'ADMIN STUDIO')
@section('toolbar-title', 'Directory settings')
@section('content')
<div class="workspace-page admin-page settings-page">
    <div class="page-heading list-heading">
        <div>
            <span class="section-kicker">MAKE THE DIRECTORY YOURS</span>
            <h1>Settings</h1>
            <p>Manage how your prompt community looks, publishes, and welcomes new creators.</p>
        </div>
        <span class="settings-live-note"><i></i> Changes apply across the directory</span>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="settings-form" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="settings-grid">
            <section class="settings-card">
                <div class="settings-card-heading"><span class="shortcut-icon">✦</span><div><h2>Directory identity</h2><p>Name and describe the prompt library.</p></div></div>
                <div class="settings-fields">
                    <label class="settings-field">Directory name
                        <input name="site_name" maxlength="60" value="{{ old('site_name', $settings['site_name']) }}" required>
                        <small>Shown in the sidebar brand and page title.</small>
                    </label>
                    <label class="settings-field">Short description
                        <textarea name="site_tagline" rows="3" maxlength="160" required>{{ old('site_tagline', $settings['site_tagline']) }}</textarea>
                        <small>A brief line describing your community.</small>
                    </label>
                    <div class="settings-field">
                        <label>Sidebar Brand Logo Icon (.svg, .png, .webp, .jpg)</label>
                        <div style="display: flex; align-items: center; gap: 14px; margin-top: 6px; flex-wrap: wrap;">
                            @if (!empty($settings['logo_url']))
                                <div style="display: flex; align-items: center; gap: 10px; padding: 6px 12px; background: #f4f4f6; border-radius: 8px; border: 1px solid #e4e4e7;">
                                    <img src="{{ $settings['logo_url'] }}" alt="Current Brand Logo" style="width: 26px; height: 26px; object-fit: contain;">
                                    <label style="font-size: 11px; color: #dc2626; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                                        <input type="checkbox" name="remove_logo" value="1"> Remove
                                    </label>
                                </div>
                            @else
                                <div style="display: flex; align-items: center; gap: 8px; padding: 6px 12px; background: #f4f4f6; border-radius: 8px; border: 1px solid #e4e4e7; font-size: 11px; color: #71717a;">
                                    <span class="brand-mark" style="width: 20px; height: 20px; display: inline-grid; place-items: center;"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true" style="width: 18px; height: 18px;"><path d="M2 18.7 8.15 6.4l3.15 6.1 2.1-3.6L22 18.7H2Z" fill="currentColor"/><path d="m7.7 14.2 2.8 1.65 2.35-4.05 3.55 6.9" stroke="#fff" stroke-width=".75" opacity=".75"/></svg></span>
                                    <span>Default SVG Icon</span>
                                </div>
                            @endif
                            <input type="file" name="logo" accept=".svg,.png,.webp,.jpg,.jpeg,.ico" style="font-size: 12px; border: 1px dashed #d4d4d8; padding: 6px 10px; border-radius: 8px; background: #fafafa; cursor: pointer;">
                        </div>
                        <small>Replaces the default geometric SVG mark at the top of the sidebar and footer.</small>
                    </div>
                    <div class="settings-field">
                        <label>Platform Favicon (.ico, .png, .svg)</label>
                        <div style="display: flex; align-items: center; gap: 14px; margin-top: 6px;">
                            @if (!empty($settings['favicon_url']))
                                <div style="display: flex; align-items: center; gap: 8px; padding: 6px 12px; background: #f4f4f6; border-radius: 8px; border: 1px solid #e4e4e7;">
                                    <img src="{{ $settings['favicon_url'] }}" alt="Favicon" style="width: 24px; height: 24px; object-fit: contain;">
                                    <label style="font-size: 11px; color: #dc2626; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                                        <input type="checkbox" name="remove_favicon" value="1"> Remove
                                    </label>
                                </div>
                            @endif
                            <input type="file" name="favicon" accept=".ico,.png,.svg,.webp,.jpg" style="font-size: 12px; border: 1px dashed #d4d4d8; padding: 6px 10px; border-radius: 8px; background: #fafafa; cursor: pointer;">
                        </div>
                        <small>Displayed on browser tabs and bookmarks (recommended 32x32 or 64x64 PNG/ICO).</small>
                    </div>
                </div>
            </section>

            <section class="settings-card">
                <div class="settings-card-heading"><span class="shortcut-icon">◎</span><div><h2>Community access</h2><p>Choose who can join and contribute.</p></div></div>
                <div class="settings-toggles">
                    <label class="settings-toggle"><span><strong>Allow new registrations</strong><small>Visitors can create a creator account.</small></span><input type="checkbox" name="allow_registrations" value="1" @checked($settings['allow_registrations'] === '1')><i aria-hidden="true"></i></label>
                    <label class="settings-toggle"><span><strong>Require email verification</strong><small>New members must verify their email address before submitting prompts or accessing features.</small></span><input type="checkbox" name="require_email_verification" value="1" @checked(($settings['require_email_verification'] ?? '0') === '1')><i aria-hidden="true"></i></label>
                    <label class="settings-toggle"><span><strong>Accept prompt submissions</strong><small>Creators can add and edit prompts.</small></span><input type="checkbox" name="allow_submissions" value="1" @checked($settings['allow_submissions'] === '1')><i aria-hidden="true"></i></label>
                    <label class="settings-toggle"><span><strong>Require admin approval</strong><small>New or edited prompts wait in the review queue.</small></span><input type="checkbox" name="require_approval" value="1" @checked($settings['require_approval'] === '1')><i aria-hidden="true"></i></label>
                </div>
            </section>

            <section class="settings-card">
                <div class="settings-card-heading"><span class="shortcut-icon">☁</span><div><h2>Image storage &amp; CDN</h2><p>Choose where prompt covers and assets are stored and served.</p></div></div>
                <div class="settings-fields settings-fields-inline">
                    <label class="settings-field" style="grid-column: 1 / -1;">Storage Provider / Disk
                        <select name="storage_driver">
                            <option value="local" @selected(($settings['storage_driver'] ?? 'local') === 'local')>Local Public Disk (storage/app/public)</option>
                            <option value="s3" @selected(($settings['storage_driver'] ?? '') === 's3')>Amazon AWS S3</option>
                            <option value="r2" @selected(($settings['storage_driver'] ?? '') === 'r2')>Cloudflare R2 Storage (S3 API Compatible)</option>
                        </select>
                        <small>Active driver for storing prompt covers, avatars, and WebP thumbnails.</small>
                    </label>
                    <label class="settings-field">AWS / R2 Access Key ID
                        <input name="storage_aws_access_key_id" value="{{ old('storage_aws_access_key_id', $settings['storage_aws_access_key_id'] ?? '') }}" placeholder="AKIA... or R2 token ID">
                        <small>API Access key from cloud storage provider.</small>
                    </label>
                    <label class="settings-field">AWS / R2 Secret Access Key
                        <input type="password" name="storage_aws_secret_access_key" value="{{ old('storage_aws_secret_access_key', $settings['storage_aws_secret_access_key'] ?? '') }}" placeholder="Secret access key..." autocomplete="off">
                        <small>Secret key paired with Access Key ID.</small>
                    </label>
                    <label class="settings-field">Bucket Name
                        <input name="storage_aws_bucket" value="{{ old('storage_aws_bucket', $settings['storage_aws_bucket'] ?? '') }}" placeholder="prompt-covers-bucket">
                        <small>Target bucket or container name.</small>
                    </label>
                    <label class="settings-field">Default Region
                        <input name="storage_aws_default_region" value="{{ old('storage_aws_default_region', $settings['storage_aws_default_region'] ?? 'us-east-1') }}" placeholder="us-east-1 or auto">
                        <small>e.g. us-east-1, eu-west-1, or 'auto' for Cloudflare R2.</small>
                    </label>
                    <label class="settings-field" style="grid-column: 1 / -1;">Endpoint URL (Required for Cloudflare R2)
                        <input name="storage_aws_endpoint" value="{{ old('storage_aws_endpoint', $settings['storage_aws_endpoint'] ?? '') }}" placeholder="https://<account_id>.r2.cloudflarestorage.com">
                        <small>Cloudflare R2 endpoint URL (or custom S3/MinIO endpoint).</small>
                    </label>
                    <label class="settings-field" style="grid-column: 1 / -1;">Public CDN / Asset URL
                        <input name="storage_aws_url" value="{{ old('storage_aws_url', $settings['storage_aws_url'] ?? '') }}" placeholder="https://cdn.promptimage.cc or https://pub-<hash>.r2.dev">
                        <small>Public CDN domain used to serve optimized WebP thumbnails and images globally.</small>
                    </label>
                </div>
            </section>

            <section class="settings-card">
                <div class="settings-card-heading"><span class="shortcut-icon">⊞</span><div><h2>Gallery discovery</h2><p>Set the opening view for visitors.</p></div></div>
                <div class="settings-fields settings-fields-inline">
                    <label class="settings-field">Default prompt order
                        <select name="default_sort">
                            <option value="random" @selected(($settings['default_sort'] ?? 'random') === 'random')>Discover / Random (Fresh order on every visit)</option>
                            <option value="featured" @selected($settings['default_sort'] === 'featured')>Featured first</option>
                            <option value="newest" @selected($settings['default_sort'] === 'newest')>Newest first</option>
                            <option value="popular" @selected($settings['default_sort'] === 'popular')>Most popular</option>
                        </select>
                        <small>Visitors can still change the sort on the gallery.</small>
                    </label>
                    <label class="settings-field">Prompts per page
                        <select name="prompts_per_page">
                            @php
                                $counts = [12, 16, 20, 24, 30, 36, 48, 60, 72, 96, 120, 150, 200];
                                $currentCount = (int) $settings['prompts_per_page'];
                                if ($currentCount && !in_array($currentCount, $counts)) {
                                    $counts[] = $currentCount;
                                    sort($counts);
                                }
                            @endphp
                            @foreach ($counts as $count)
                                <option value="{{ $count }}" @selected($currentCount === $count)>{{ $count }} prompts</option>
                            @endforeach
                        </select>
                        <small>Controls the gallery page size and pagination.</small>
                    </label>
                    <label class="settings-field" style="grid-column: 1 / -1;">Prompt of the Day / Featured Carousel
                        <select name="featured_prompt_id">
                            <option value="" @selected(empty($settings['featured_prompt_id']))>Automatic (Highest rated &amp; featured prompts)</option>
                            @if(isset($approvedPrompts))
                                @foreach ($approvedPrompts as $ap)
                                    <option value="{{ $ap->id }}" @selected(($settings['featured_prompt_id'] ?? '') == (string) $ap->id)>
                                        #{{ $ap->id }} — {{ Str::limit($ap->title, 55) }} ({{ $ap->model }} &bull; {{ $ap->likes_count }} likes)
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <small>Choose a specific approved prompt to lock as the primary slide in the Prompt of the Day carousel.</small>
                    </label>
                </div>
            </section>

            <section class="settings-card">
                <div class="settings-card-heading"><span class="shortcut-icon">✉</span><div><h2>Email delivery</h2><p>Configure the outgoing mail transport and sender identity.</p></div></div>
                <label class="settings-toggle settings-email-enable"><span><strong>Use custom email settings</strong><small>When disabled, Laravel uses the mail settings from your server environment.</small></span><input type="checkbox" name="mail_settings_enabled" value="1" @checked($settings['mail_settings_enabled'] === '1')><i aria-hidden="true"></i></label>
                <div class="settings-fields settings-fields-inline">
                    <label class="settings-field">Mail transport
                        <select name="mail_mailer">
                            <option value="smtp" @selected($settings['mail_mailer'] === 'smtp')>SMTP</option>
                            <option value="log" @selected($settings['mail_mailer'] === 'log')>Log (development)</option>
                            <option value="array" @selected($settings['mail_mailer'] === 'array')>Array (preview only)</option>
                        </select>
                        <small>SMTP sends mail through your provider. Log and Array do not deliver messages.</small>
                    </label>
                    <label class="settings-field">Contact email <span class="optional-label">Optional</span>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}" placeholder="support@example.com">
                        <small>Public support address for your prompt directory.</small>
                    </label>
                    <label class="settings-field">SMTP host
                        <input name="mail_host" value="{{ old('mail_host', $settings['mail_host']) }}" placeholder="smtp.example.com">
                    </label>
                    <label class="settings-field">SMTP port
                        <input type="number" name="mail_port" min="1" max="65535" value="{{ old('mail_port', $settings['mail_port']) }}" placeholder="587">
                    </label>
                    <label class="settings-field">Connection encryption
                        <select name="mail_encryption">
                            <option value="tls" @selected($settings['mail_encryption'] === 'tls')>TLS / STARTTLS</option>
                            <option value="ssl" @selected($settings['mail_encryption'] === 'ssl')>SSL</option>
                            <option value="none" @selected($settings['mail_encryption'] === 'none')>None</option>
                        </select>
                    </label>
                    <label class="settings-field">SMTP username <span class="optional-label">Optional</span>
                        <input name="mail_username" autocomplete="off" value="{{ old('mail_username', $settings['mail_username']) }}">
                    </label>
                    <label class="settings-field">SMTP password <span class="optional-label">Optional</span>
                        <input type="password" name="mail_password" autocomplete="new-password" placeholder="{{ $settings['mail_password_configured'] ? 'Saved securely — leave blank to keep' : 'SMTP password or app key' }}">
                        <small>The password is encrypted in the database and never shown here.</small>
                    </label>
                    @if ($settings['mail_password_configured'])
                        <label class="settings-toggle settings-clear-secret"><span><strong>Remove saved SMTP password</strong><small>Use this if your provider no longer requires authentication.</small></span><input type="checkbox" name="clear_mail_password" value="1"><i aria-hidden="true"></i></label>
                    @endif
                    <label class="settings-field">Sender email
                        <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address']) }}" placeholder="hello@example.com">
                    </label>
                    <label class="settings-field">Sender name <span class="optional-label">Optional</span>
                        <input name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name']) }}" placeholder="{{ $settings['site_name'] }}">
                    </label>
                    <label class="settings-field">Reply-to email <span class="optional-label">Optional</span>
                        <input type="email" name="mail_reply_to" value="{{ old('mail_reply_to', $settings['mail_reply_to']) }}" placeholder="support@example.com">
                    </label>
                </div>
            </section>

            <section class="settings-card">
                <div class="settings-card-heading"><span class="shortcut-icon">💳</span><div><h2>Payment Gateways &amp; Creator Tips</h2><p>Configure Stripe Checkout &amp; PayPal for direct on-site supporter tips.</p></div></div>
                <label class="settings-toggle"><span><strong>Enable Stripe Checkout</strong><small>Allows supporters to tip creators directly on-site using Credit Cards, Apple Pay, and Google Pay.</small></span><input type="checkbox" name="stripe_enabled" value="1" @checked(($settings['stripe_enabled'] ?? '0') === '1')><i aria-hidden="true"></i></label>
                <div class="settings-fields settings-fields-inline" style="margin-bottom: 20px;">
                    <label class="settings-field">Stripe Publishable Key
                        <input name="stripe_publishable_key" value="{{ old('stripe_publishable_key', $settings['stripe_publishable_key'] ?? '') }}" placeholder="pk_test_... or pk_live_...">
                        <small>Public key from your Stripe account.</small>
                    </label>
                    <label class="settings-field">Stripe Secret Key
                        <input type="password" name="stripe_secret_key" value="{{ old('stripe_secret_key', $settings['stripe_secret_key'] ?? '') }}" placeholder="sk_test_... or sk_live_..." autocomplete="off">
                        <small>Secret key used for sessions and creator payout transfers.</small>
                    </label>
                    <label class="settings-field" style="grid-column: 1 / -1;">Stripe Webhook Secret <span class="optional-label">Optional</span>
                        <input type="password" name="stripe_webhook_secret" value="{{ old('stripe_webhook_secret', $settings['stripe_webhook_secret'] ?? '') }}" placeholder="whsec_..." autocomplete="off">
                        <small>Webhook endpoint: <code>{{ url('/api/webhooks/tips') }}</code></small>
                    </label>
                </div>

                <label class="settings-toggle"><span><strong>Enable PayPal Checkout</strong><small>Allows supporters to tip creators via PayPal balances and credit cards.</small></span><input type="checkbox" name="paypal_enabled" value="1" @checked(($settings['paypal_enabled'] ?? '0') === '1')><i aria-hidden="true"></i></label>
                <div class="settings-fields settings-fields-inline">
                    <label class="settings-field">PayPal Environment Mode
                        <select name="paypal_mode">
                            <option value="sandbox" @selected(($settings['paypal_mode'] ?? 'sandbox') === 'sandbox')>Sandbox (Test Environment)</option>
                            <option value="live" @selected(($settings['paypal_mode'] ?? '') === 'live')>Live (Production Payments)</option>
                        </select>
                        <small>Test tipping freely in Sandbox before going Live.</small>
                    </label>
                    <label class="settings-field">PayPal Client ID
                        <input name="paypal_client_id" value="{{ old('paypal_client_id', $settings['paypal_client_id'] ?? '') }}" placeholder="PayPal REST App Client ID">
                        <small>Client ID from PayPal Developer Portal.</small>
                    </label>
                    <label class="settings-field" style="grid-column: 1 / -1;">PayPal Secret Key
                        <input type="password" name="paypal_secret" value="{{ old('paypal_secret', $settings['paypal_secret'] ?? '') }}" placeholder="PayPal Secret..." autocomplete="off">
                        <small>Secret used to capture orders and credit creator balances.</small>
                    </label>
                </div>
            </section>

            <section class="settings-card">
                <div class="settings-card-heading"><span class="shortcut-icon">⌕</span><div><h2>SEO and social sharing</h2><p>Set the search result metadata, indexing, and social preview defaults.</p></div></div>
                <div class="settings-fields">
                    <label class="settings-field">SEO site name <span class="optional-label">Optional</span>
                        <input name="seo_title" maxlength="120" value="{{ old('seo_title', $settings['seo_title']) }}" placeholder="{{ $settings['site_name'] }}">
                        <small>Used for the social site name and the %site% title token. Leave blank to use the directory name.</small>
                    </label>
                    <label class="settings-field">Page title template
                        <input name="seo_title_template" maxlength="160" value="{{ old('seo_title_template', $settings['seo_title_template']) }}" placeholder="%page% — %site%">
                        <small>Use %page% for each page title and %site% for the SEO site name.</small>
                    </label>
                    <label class="settings-field">Meta description <span class="optional-label">Optional</span>
                        <textarea name="seo_description" rows="3" maxlength="320" placeholder="{{ $settings['site_tagline'] }}">{{ old('seo_description', $settings['seo_description']) }}</textarea>
                        <small>Shown in search and social previews when a page has no custom description.</small>
                    </label>
                    <label class="settings-field">Search keywords <span class="optional-label">Optional</span>
                        <input name="seo_keywords" maxlength="500" value="{{ old('seo_keywords', $settings['seo_keywords']) }}" placeholder="AI prompts, prompt library, image generation">
                    </label>
                    <label class="settings-field">Canonical site URL <span class="optional-label">Optional</span>
                        <input type="url" name="seo_canonical_base" value="{{ old('seo_canonical_base', $settings['seo_canonical_base']) }}" placeholder="https://prompts.example.com">
                        <small>Set the public base URL; each page’s path is added automatically.</small>
                    </label>
                    <div class="settings-fields settings-fields-inline">
                        <label class="settings-field">Search engine indexing
                            <select name="seo_robots">
                                <option value="index,follow" @selected($settings['seo_robots'] === 'index,follow')>Index and follow links</option>
                                <option value="index,nofollow" @selected($settings['seo_robots'] === 'index,nofollow')>Index, do not follow links</option>
                                <option value="noindex,follow" @selected($settings['seo_robots'] === 'noindex,follow')>Do not index, follow links</option>
                                <option value="noindex,nofollow" @selected($settings['seo_robots'] === 'noindex,nofollow')>Do not index or follow links</option>
                            </select>
                        </label>
                        <label class="settings-field">Social preview image URL <span class="optional-label">Optional</span>
                            <input type="url" name="seo_og_image" value="{{ old('seo_og_image', $settings['seo_og_image']) }}" placeholder="https://example.com/social-preview.webp">
                            <small>Recommended size: 1200 × 630 pixels.</small>
                        </label>
                    </div>
                </div>
            </section>
        </div>

        <div class="settings-footer"><span>Settings are saved to your directory database.</span><button class="primary-button" type="submit">Save settings <span>→</span></button></div>
    </form>
</div>
@endsection
