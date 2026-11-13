<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Top 10 AI Art Prompts</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f7;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1f2937;
            -webkit-font-smoothing: antialiased;
        }
        table { border-collapse: collapse; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f4f4f7; padding: 30px 10px; }
        .main { background: #ffffff; max-width: 600px; margin: 0 auto; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { background: linear-gradient(135deg, #18181b 0%, #27272a 100%); padding: 32px 24px; text-align: center; }
        .logo { font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px; text-decoration: none; }
        .logo span { color: #818cf8; }
        .header-title { color: #f4f4f5; font-size: 20px; font-weight: 700; margin: 16px 0 6px; }
        .header-sub { color: #a1a1aa; font-size: 13.5px; margin: 0; }
        .content { padding: 24px 20px; }
        .prompt-card { background: #fafafa; border: 1px solid #e5e7eb; border-radius: 10px; margin-bottom: 20px; overflow: hidden; }
        .prompt-img { width: 100%; max-height: 280px; object-fit: cover; display: block; }
        .prompt-body { padding: 16px; }
        .prompt-badges { margin-bottom: 8px; }
        .badge { display: inline-block; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; margin-right: 6px; }
        .badge-cat { background: #e0e7ff; color: #4338ca; }
        .badge-model { background: #f3f4f6; color: #374151; }
        .prompt-title { font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 8px; text-decoration: none; display: block; }
        .prompt-text { font-size: 13px; color: #4b5563; line-height: 1.5; margin: 0 0 14px; background: #ffffff; padding: 10px 12px; border-radius: 6px; border: 1px solid #e5e7eb; font-family: monospace; }
        .btn-view { display: inline-block; background: #4f46e5; color: #ffffff !important; font-size: 13px; font-weight: 600; padding: 8px 18px; border-radius: 6px; text-decoration: none; text-align: center; }
        .btn-view:hover { background: #4338ca; }
        .footer { background: #f9fafb; padding: 24px; text-align: center; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280; }
        .footer a { color: #4f46e5; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main">
            <!-- Header -->
            <div class="header">
                <a href="{{ url('/') }}" class="logo" style="display: inline-flex; align-items: center; justify-content: center; gap: 10px; text-decoration: none;">
                    @if (!empty($logoUrl))
                        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" style="height: 32px; width: auto; max-width: 48px; object-fit: contain; vertical-align: middle; display: inline-block;">
                    @else
                        <span style="font-size: 22px; vertical-align: middle;">✨</span>
                    @endif
                    <span style="vertical-align: middle; color: #ffffff; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">{{ $siteName }}</span>
                </a>
                <h1 class="header-title">🎨 Top 10 AI Art Prompts of the Week</h1>
                <p class="header-sub">Curated standout generations and visual prompts hand-picked for your inspiration.</p>
            </div>

            <!-- Prompts List -->
            <div class="content">
                @forelse ($prompts as $index => $prompt)
                    <div class="prompt-card">
                        @if ($prompt->cover_url)
                            <a href="{{ route('prompts.show', $prompt->slug) }}" target="_blank">
                                <img src="{{ $prompt->cover_url }}" alt="{{ $prompt->title }}" class="prompt-img">
                            </a>
                        @endif
                        <div class="prompt-body">
                            <div class="prompt-badges">
                                <span class="badge badge-cat">{{ $prompt->category->icon ?? '✨' }} {{ $prompt->category->name ?? 'General' }}</span>
                                <span class="badge badge-model">{{ $prompt->model }}</span>
                                <span style="font-size: 12px; color: #dc2626; font-weight: 600; float: right;">♥ {{ number_format($prompt->likes_count) }}</span>
                            </div>
                            <a href="{{ route('prompts.show', $prompt->slug) }}" class="prompt-title" target="_blank">
                                #{{ $index + 1 }} · {{ $prompt->title }}
                            </a>
                            <div class="prompt-text">
                                {{ Str::limit($prompt->prompt_text, 140) }}
                            </div>
                            <div style="text-align: right;">
                                <a href="{{ route('prompts.show', $prompt->slug) }}" class="btn-view" target="_blank">
                                    Copy Prompt &amp; View Details →
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p style="text-align: center; color: #6b7280; padding: 30px;">No prompts found for this week's digest.</p>
                @endforelse

                <div style="text-align: center; padding: 16px 0;">
                    <a href="{{ url('/') }}" style="display: inline-block; background: #111827; color: #ffffff !important; font-size: 14px; font-weight: 600; padding: 12px 28px; border-radius: 8px; text-decoration: none;">
                        Explore All 5,000+ AI Art Prompts →
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p style="margin: 0 0 8px;">
                    You are receiving this weekly digest because you subscribed with <strong>{{ $subscriberEmail }}</strong> on <a href="{{ url('/') }}">{{ $siteName }}</a>.
                </p>
                <p style="margin: 0;">
                    Don't want these emails? <a href="{{ $unsubscribeUrl }}">Unsubscribe immediately</a> with one click.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
