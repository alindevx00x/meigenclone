@extends('layouts.app')
@section('title', 'My creator studio')
@section('eyebrow', 'YOUR CREATOR SPACE')
@section('toolbar-title', 'My studio')
@section('content')
<div class="workspace-page">
    <div class="page-heading dashboard-heading">
        <div>
            <span class="section-kicker">CREATOR DASHBOARD</span>
            <h1>Good ideas live here.</h1>
            <p>Keep track of your prompts, profile settings, and the inspiration you’ve saved.</p>
        </div>
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <a class="outline-button" href="{{ route('dashboard.profile.edit') }}" style="text-decoration: none; font-weight: 600;"><span>⚙</span> Profile settings</a>
            <a class="outline-button" href="{{ route('creators.show', auth()->user()) }}" style="text-decoration: none; font-weight: 600; color: #6366f1; border-color: #c7d2fe;">View public profile <span>↗</span></a>
            @if ($directorySettings['allow_submissions'] === '1')
                <a class="primary-button" href="{{ route('dashboard.prompts.create') }}"><span>＋</span> Submit a prompt</a>
            @endif
        </div>
    </div>
    <div class="stats-row user-stats">
        <div class="stat-card"><span class="stat-icon stat-violet">✦</span><span class="stat-label">Total submissions</span><strong>{{ $counts['total'] }}</strong><small>All your shared ideas</small></div>
        <div class="stat-card"><span class="stat-icon stat-green">✓</span><span class="stat-label">Published prompts</span><strong>{{ $counts['approved'] }}</strong><small>Visible in the gallery</small></div>
        <div class="stat-card"><span class="stat-icon stat-amber">◷</span><span class="stat-label">In review</span><strong>{{ $counts['pending'] }}</strong><small>Usually reviewed in 1–2 days</small></div>
        <div class="stat-card"><span class="stat-icon stat-pink">♡</span><span class="stat-label">Saved prompts</span><strong>{{ $counts['favorites'] }}</strong><small>Your personal collection</small></div>
    </div>

    <!-- Creator Performance Analytics -->
    <section class="workspace-section" id="creator-analytics" style="margin-bottom: 36px;">
        <div class="section-heading" style="margin-bottom: 20px;">
            <div>
                <span class="section-kicker" style="color: #7c3aed; font-weight: 700;">INSIGHTS &amp; ENGAGEMENT</span>
                <h2 style="font-size: 22px; font-weight: 700; color: #1e1e24; margin: 4px 0 0;">Creator Analytics &amp; Performance</h2>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <a class="subtle-link" href="{{ route('dashboard.profile.edit') }}" style="font-weight: 600; color: #6366f1;">
                    <span>⚙</span> Edit profile <span>→</span>
                </a>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 22px;">
            <div style="background: #ffffff; border: 1px solid #e4e4eb; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 13px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Prompt Views</span>
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: #ede9fe; color: #7c3aed; font-size: 15px;">👁</span>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #18181b; line-height: 1.1;">{{ number_format($analytics['total_views']) }}</div>
                <div style="font-size: 12px; color: #71717a; margin-top: 6px;">Total organic impressions &amp; visits</div>
            </div>

            <div style="background: #ffffff; border: 1px solid #e4e4eb; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 13px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Prompt Copies</span>
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: #e0f2fe; color: #0284c7; font-size: 15px;">📋</span>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #18181b; line-height: 1.1;">{{ number_format($analytics['total_copies']) }}</div>
                <div style="font-size: 12px; color: #71717a; margin-top: 6px;">Clipboard copy clicks by users</div>
            </div>

            <div style="background: #ffffff; border: 1px solid #e4e4eb; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 13px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Copy Rate</span>
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: #ecfdf5; color: #059669; font-size: 15px;">🎯</span>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #18181b; line-height: 1.1;">{{ $analytics['copy_rate'] }}%</div>
                <div style="font-size: 12px; color: #71717a; margin-top: 6px;">Conversion from views to copies</div>
            </div>
        </div>

        <!-- 60-Day Activity Heatmap Card -->
        <div style="background: #ffffff; border: 1px solid #e4e4eb; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); margin-bottom: 22px;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; color: #1e1e24; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <span>📅</span> 60-Day Prompt Engagement Heatmap
                    </h3>
                    <p style="font-size: 12px; color: #64748b; margin: 4px 0 0;">Daily activity calculated from views, prompt copies, and community saves.</p>
                </div>
                <!-- Heatmap Legend -->
                <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #64748b;">
                    <span>Less</span>
                    <span title="Level 0: No activity" style="width: 13px; height: 13px; border-radius: 3px; background: #f4f4f6; border: 1px solid #e5e7eb; display: inline-block;"></span>
                    <span title="Level 1: Light activity" style="width: 13px; height: 13px; border-radius: 3px; background: #ddd6fe; border: 1px solid #c4b5fd; display: inline-block;"></span>
                    <span title="Level 2: Moderate activity" style="width: 13px; height: 13px; border-radius: 3px; background: #a78bfa; border: 1px solid #8b5cf6; display: inline-block;"></span>
                    <span title="Level 3: Active" style="width: 13px; height: 13px; border-radius: 3px; background: #7c3aed; border: 1px solid #6d28d9; display: inline-block;"></span>
                    <span title="Level 4: High activity" style="width: 13px; height: 13px; border-radius: 3px; background: #4c1d95; border: 1px solid #3b0764; display: inline-block;"></span>
                    <span>More</span>
                </div>
            </div>

            <!-- Heatmap Grid -->
            <div style="overflow-x: auto; padding-bottom: 4px;">
                <div style="display: grid; grid-template-columns: repeat(30, minmax(16px, 1fr)); gap: 5px; min-width: 580px;">
                    @foreach ($analytics['heatmap_days'] as $day)
                        @php
                            $bg = match($day['level']) {
                                1 => '#ddd6fe',
                                2 => '#a78bfa',
                                3 => '#7c3aed',
                                4 => '#4c1d95',
                                default => '#f4f4f6',
                            };
                            $borderColor = match($day['level']) {
                                1 => '#c4b5fd',
                                2 => '#8b5cf6',
                                3 => '#6d28d9',
                                4 => '#3b0764',
                                default => '#e5e7eb',
                            };
                        @endphp
                        <div
                            title="{{ $day['label'] }}: {{ $day['views'] }} views, {{ $day['copies'] }} copies, {{ $day['favorites'] }} favorites (Activity Score: {{ $day['score'] }})"
                            style="aspect-ratio: 1/1; border-radius: 4px; background: {{ $bg }}; border: 1px solid {{ $borderColor }}; cursor: pointer; transition: transform 0.15s ease;"
                            onmouseover="this.style.transform='scale(1.25)';"
                            onmouseout="this.style.transform='scale(1)';"
                        ></div>
                    @endforeach
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 11px; color: #94a3b8; margin-top: 8px;">
                <span>60 days ago</span>
                <span>30 days ago</span>
                <span>Today</span>
            </div>
        </div>

        <!-- 14-Day Trajectory Trend & Top Prompts Split -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px;">
            <!-- Trajectory Trend Chart -->
            <div style="background: #ffffff; border: 1px solid #e4e4eb; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <h3 style="font-size: 15px; font-weight: 700; color: #1e1e24; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <span>📈</span> 14-Day Trajectory
                    </h3>
                    <div style="display: flex; align-items: center; gap: 12px; font-size: 11px; font-weight: 600;">
                        <span style="display: inline-flex; align-items: center; gap: 4px; color: #7c3aed;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #7c3aed;"></span> Views
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px; color: #0284c7;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #0284c7;"></span> Copies
                        </span>
                    </div>
                </div>

                @php
                    $svgW = 440;
                    $svgH = 120;
                    $maxVal = max(1, $analytics['max_trend_value']);
                    $count = count($analytics['trend_slice']);
                    $viewsPoints = [];
                    $copiesPoints = [];
                    foreach ($analytics['trend_slice'] as $i => $item) {
                        $x = $count > 1 ? ($i / ($count - 1)) * $svgW : ($svgW / 2);
                        $yViews = $svgH - (($item['views'] / $maxVal) * ($svgH - 24)) - 12;
                        $yCopies = $svgH - (($item['copies'] / $maxVal) * ($svgH - 24)) - 12;
                        $viewsPoints[] = round($x, 1) . ',' . round($yViews, 1);
                        $copiesPoints[] = round($x, 1) . ',' . round($yCopies, 1);
                    }
                    $viewsPoly = implode(' ', $viewsPoints);
                    $copiesPoly = implode(' ', $copiesPoints);
                    $viewsArea = "0,{$svgH} " . $viewsPoly . " {$svgW},{$svgH}";
                @endphp

                <div style="width: 100%; overflow: hidden;">
                    <svg viewBox="0 0 {{ $svgW }} {{ $svgH }}" style="width: 100%; height: 130px; display: block; overflow: visible;">
                        <defs>
                            <linearGradient id="viewsGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#7c3aed" stop-opacity="0.25"/>
                                <stop offset="100%" stop-color="#7c3aed" stop-opacity="0.0"/>
                            </linearGradient>
                        </defs>
                        <!-- Grid lines -->
                        <line x1="0" y1="20" x2="{{ $svgW }}" y2="20" stroke="#f1f1f5" stroke-dasharray="3,3" />
                        <line x1="0" y1="65" x2="{{ $svgW }}" y2="65" stroke="#f1f1f5" stroke-dasharray="3,3" />
                        <line x1="0" y1="{{ $svgH }}" x2="{{ $svgW }}" y2="{{ $svgH }}" stroke="#e5e7eb" />

                        <!-- Views area and line -->
                        <polygon points="{{ $viewsArea }}" fill="url(#viewsGrad)" />
                        <polyline points="{{ $viewsPoly }}" fill="none" stroke="#7c3aed" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />

                        <!-- Copies line -->
                        <polyline points="{{ $copiesPoly }}" fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />

                        <!-- Dots for the last day -->
                        @if (!empty($viewsPoints))
                            @php
                                $lastV = explode(',', end($viewsPoints));
                                $lastC = explode(',', end($copiesPoints));
                            @endphp
                            <circle cx="{{ $lastV[0] }}" cy="{{ $lastV[1] }}" r="4" fill="#7c3aed" stroke="#ffffff" stroke-width="2" />
                            <circle cx="{{ $lastC[0] }}" cy="{{ $lastC[1] }}" r="4" fill="#0284c7" stroke="#ffffff" stroke-width="2" />
                        @endif
                    </svg>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 11px; color: #94a3b8; margin-top: 6px;">
                    <span>{{ reset($analytics['trend_slice'])['label'] ?? '14d ago' }}</span>
                    <span>Peak: ~{{ $maxVal }}</span>
                    <span>Today</span>
                </div>
            </div>

            <!-- Top Performing Prompts or Recent Supporters -->
            <div style="background: #ffffff; border: 1px solid #e4e4eb; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <h3 style="font-size: 15px; font-weight: 700; color: #1e1e24; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <span>⭐</span> Top Performing Prompts
                    </h3>
                    <span style="font-size: 11px; color: #64748b; font-weight: 600;">Ranked by views</span>
                </div>

                @if ($analytics['top_prompts']->isEmpty())
                    <div style="text-align: center; padding: 24px 12px; color: #94a3b8; font-size: 13px;">
                        No published prompts yet. Share your first prompt to start tracking live analytics.
                    </div>
                @else
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @foreach ($analytics['top_prompts'] as $tp)
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 10px; background: #fafafb; border: 1px solid #f1f1f4; border-radius: 8px;">
                                <div style="min-width: 0; flex: 1;">
                                    <a href="{{ route('prompts.show', $tp->slug) }}" style="text-decoration: none; font-size: 13px; font-weight: 600; color: #18181b; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        {{ $tp->title }}
                                    </a>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                        {{ $tp->category->name }} &bull; {{ $tp->model }}
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0; font-size: 12px;">
                                    <span title="Views" style="display: inline-flex; align-items: center; gap: 3px; background: #ede9fe; color: #6d28d9; padding: 2px 7px; border-radius: 6px; font-weight: 600;">
                                        👁 {{ $tp->views_count }}
                                    </span>
                                    <span title="Copies" style="display: inline-flex; align-items: center; gap: 3px; background: #e0f2fe; color: #0369a1; padding: 2px 7px; border-radius: 6px; font-weight: 600;">
                                        📋 {{ $tp->copies_count }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="workspace-section" id="my-prompts">
        <div class="section-heading"><div><span class="section-kicker">YOUR CONTRIBUTIONS</span><h2>My prompts</h2></div>@if ($directorySettings['allow_submissions'] === '1')<a class="subtle-link" href="{{ route('dashboard.prompts.create') }}">Add a new prompt <span>→</span></a>@endif</div>
        @if ($prompts->isEmpty())
            <div class="empty-panel"><span class="empty-icon">✦</span><div><strong>Your first idea belongs here.</strong><p>Share a prompt that helped you make something you love.</p></div>@if ($directorySettings['allow_submissions'] === '1')<a class="outline-button" href="{{ route('dashboard.prompts.create') }}">Create your first prompt <span>→</span></a>@endif</div>
        @else
            <div class="table-wrap"><table class="data-table"><thead><tr><th>Prompt</th><th>Category</th><th>Views</th><th>Copies</th><th>Submitted</th><th>Status</th><th></th></tr></thead><tbody>
                @foreach ($prompts as $prompt)
                    <tr>
                        <td>
                            <div class="table-prompt">
                                <span class="table-thumb">{{ $prompt->category->icon }}</span>
                                <div>
                                    <strong>{{ $prompt->title }}</strong>
                                    <small>{{ $prompt->model }}</small>
                                    @if ($prompt->admin_note)<em>{{ $prompt->admin_note }}</em>@endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $prompt->category->name }}</td>
                        <td><strong style="color: #6d28d9; font-size: 13px;">{{ number_format($prompt->views_count) }}</strong></td>
                        <td><strong style="color: #0284c7; font-size: 13px;">{{ number_format($prompt->copies_count) }}</strong></td>
                        <td>{{ $prompt->created_at->format('M j, Y') }}</td>
                        <td><span class="status-pill status-{{ $prompt->status }}"><i></i>{{ ucfirst($prompt->status) }}</span></td>
                        <td>
                            <div class="row-actions">
                                @if ($prompt->status === 'approved')<a href="{{ route('prompts.show', $prompt->slug) }}">View</a>@endif
                                @if ($directorySettings['allow_submissions'] === '1')<a href="{{ route('dashboard.prompts.edit', $prompt) }}">Edit</a>@endif
                                <form method="POST" action="{{ route('dashboard.prompts.destroy', $prompt) }}" onsubmit="return confirm('Delete this prompt?')">@csrf @method('DELETE')<button type="submit">Delete</button></form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody></table></div>
        @endif
    </section>

    <section class="workspace-section" id="favorites">
        <div class="section-heading"><div><span class="section-kicker">YOUR SAVED COLLECTION</span><h2>Favorites <span class="heading-count">{{ $counts['favorites'] }}</span></h2></div><a class="subtle-link" href="{{ route('prompts.index', ['sort' => 'popular']) }}">Discover more <span>→</span></a></div>
        @if ($favorites->isEmpty())
            <div class="empty-panel"><span class="empty-icon">♡</span><div><strong>Keep the ideas that move you.</strong><p>Tap the heart on any prompt to save it here for later.</p></div><a class="outline-button" href="{{ route('prompts.index') }}">Explore the gallery <span>→</span></a></div>
        @else
            <div class="prompt-grid dashboard-favorites">@foreach ($favorites as $prompt) @php($prompt->is_favorited = true) @include('partials.prompt-card', ['prompt' => $prompt]) @endforeach</div>
        @endif
    </section>
</div>
@endsection
