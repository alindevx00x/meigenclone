@extends('layouts.app')
@section('title', 'Directory settings')
@section('eyebrow', 'MEIGEN ADMIN')
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

    <form method="POST" action="{{ route('admin.settings.update') }}" class="settings-form">
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
                </div>
            </section>

            <section class="settings-card">
                <div class="settings-card-heading"><span class="shortcut-icon">◎</span><div><h2>Community access</h2><p>Choose who can join and contribute.</p></div></div>
                <div class="settings-toggles">
                    <label class="settings-toggle"><span><strong>Allow new registrations</strong><small>Visitors can create a creator account.</small></span><input type="checkbox" name="allow_registrations" value="1" @checked($settings['allow_registrations'] === '1')><i aria-hidden="true"></i></label>
                    <label class="settings-toggle"><span><strong>Accept prompt submissions</strong><small>Creators can add and edit prompts.</small></span><input type="checkbox" name="allow_submissions" value="1" @checked($settings['allow_submissions'] === '1')><i aria-hidden="true"></i></label>
                    <label class="settings-toggle"><span><strong>Require admin approval</strong><small>New or edited prompts wait in the review queue.</small></span><input type="checkbox" name="require_approval" value="1" @checked($settings['require_approval'] === '1')><i aria-hidden="true"></i></label>
                </div>
            </section>

            <section class="settings-card">
                <div class="settings-card-heading"><span class="shortcut-icon">▤</span><div><h2>Gallery discovery</h2><p>Set the opening view for visitors.</p></div></div>
                <div class="settings-fields settings-fields-inline">
                    <label class="settings-field">Default prompt order
                        <select name="default_sort">
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
