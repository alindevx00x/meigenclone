@extends('layouts.app')
@section('title', 'Write new blog article')
@section('eyebrow', 'EDITORIAL STUDIO')
@section('toolbar-title', 'Create blog post')

@section('preload')
    <!-- Self-hosted TinyMCE via Cloudflare CDN (No API key, No license required, No registration) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
@endsection

@section('content')
<div class="workspace-page form-page">
    <a class="back-link" href="{{ route('admin.blog.index') }}"><span>←</span> Back to blog articles</a>
    
    <div class="page-heading form-heading">
        <div>
            <span class="section-kicker">EDITORIAL STUDIO</span>
            <h1>Write a new article</h1>
            <p>Draft tutorials, prompt engineering best practices, or model comparisons with rich formatting.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert-box alert-error" style="margin-bottom: 20px;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form class="panel-card" method="POST" action="{{ route('admin.blog.store') }}" enctype="multipart/form-data" style="padding: 24px;">
        @csrf

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
            <!-- Left Column: Main Content -->
            <div style="display: flex; flex-direction: column; gap: 18px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                        Article Title <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="title" id="article-title" required maxlength="255" value="{{ old('title') }}" placeholder="e.g. The Ultimate Midjourney v6.1 Prompt Architecture Guide" style="width: 100%; height: 42px; padding: 0 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 15px; font-weight: 600; color: #0f172a;">
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                        URL Slug <span style="color: #94a3b8; font-weight: 400; font-size: 12px;">(Leave blank to auto-generate from title)</span>
                    </label>
                    <div style="display: flex; align-items: center; border: 1.5px solid #cbd5e1; border-radius: 8px; overflow: hidden; background: #fff;">
                        <span style="padding: 0 12px; background: #f8fafc; color: #64748b; font-size: 13px; border-right: 1px solid #e2e8f0; height: 38px; display: flex; align-items: center;">/blog/</span>
                        <input type="text" name="slug" id="article-slug" maxlength="255" value="{{ old('slug') }}" placeholder="ultimate-midjourney-v6-prompt-guide" style="flex: 1; height: 38px; padding: 0 12px; border: none; font-size: 13.5px; color: #334155;">
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                        Short Excerpt / Summary <span style="color: #94a3b8; font-weight: 400; font-size: 12px;">(Displays on blog cards and Google snippets)</span>
                    </label>
                    <textarea name="excerpt" rows="3" maxlength="1000" placeholder="Brief summary of what readers will learn in this article..." style="width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; line-height: 1.5; color: #334155; resize: vertical;">{{ old('excerpt') }}</textarea>
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                        Article Content <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea id="blog-content-editor" name="content" rows="18">{{ old('content') }}</textarea>
                </div>
            </div>

            <!-- Right Column: Metadata & Settings -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <!-- Publishing Status Card -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 16px;">
                    <h3 style="font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.04em;">Publishing Status</h3>
                    
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #475569; margin-bottom: 5px;">Status</label>
                        <select name="status" style="width: 100%; height: 38px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 13px;">
                            <option value="published" @selected(old('status', 'published') === 'published')>🟢 Published</option>
                            <option value="draft" @selected(old('status') === 'draft')>🟡 Draft</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #475569; margin-bottom: 5px;">Publish Date</label>
                        <input type="datetime-local" name="published_at" value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}" style="width: 100%; height: 38px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 13px;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #475569; margin-bottom: 5px;">Est. Reading Time (minutes)</label>
                        <input type="number" name="reading_time_minutes" min="1" max="120" value="{{ old('reading_time_minutes') }}" placeholder="Auto-calculated if empty" style="width: 100%; height: 38px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 13px;">
                    </div>
                </div>

                <!-- Category Card -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h3 style="font-size: 13.5px; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.04em;">Category</h3>
                        <a href="{{ route('admin.blog.categories.index') }}" target="_blank" style="font-size: 11.5px; color: #6366f1; text-decoration: none;">＋ Manage</a>
                    </div>
                    <select name="blog_category_id" style="width: 100%; height: 38px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 13px;">
                        <option value="">Select Category (Optional)</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('blog_category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Cover Image Card -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 16px;">
                    <h3 style="font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.04em;">Cover Image</h3>
                    
                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-size: 12px; color: #475569; margin-bottom: 4px;">Upload from Computer</label>
                        <input type="file" name="cover_image_file" accept="image/*" style="width: 100%; font-size: 12px;">
                    </div>

                    <div style="position: relative; text-align: center; margin: 10px 0;">
                        <span style="font-size: 11px; background: #f8fafc; padding: 0 8px; color: #94a3b8; position: relative; z-index: 1;">OR IMAGE URL</span>
                        <div style="position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: #e2e8f0; z-index: 0;"></div>
                    </div>

                    <div>
                        <input type="url" name="cover_image_url" value="{{ old('cover_image_url') }}" placeholder="https://images.unsplash.com/…" style="width: 100%; height: 36px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 12.5px;">
                    </div>
                </div>

                <!-- SEO Optimization Card -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 16px;">
                    <h3 style="font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.04em;">SEO Meta Details</h3>
                    
                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-size: 12px; color: #475569; margin-bottom: 4px;">Custom Meta Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title') }}" placeholder="Defaults to Article Title" style="width: 100%; height: 36px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 12.5px;">
                    </div>

                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-size: 12px; color: #475569; margin-bottom: 4px;">Meta Description</label>
                        <textarea name="meta_description" rows="3" placeholder="Defaults to Excerpt (recommended 150-160 chars)" style="width: 100%; padding: 8px 10px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 12px; resize: vertical;">{{ old('meta_description') }}</textarea>
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; color: #475569; margin-bottom: 4px;">Meta Keywords</label>
                        <input type="text" name="meta_keywords" value="{{ old('meta_keywords') }}" placeholder="ai prompts, midjourney tutorial, flux.1" style="width: 100%; height: 36px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 12.5px;">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer" style="margin-top: 24px; padding-top: 18px; border-top: 1.5px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <a class="outline-button" href="{{ route('admin.blog.index') }}">Cancel</a>
            <button class="primary-button" type="submit">Publish Article <span>→</span></button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Auto-slug generator
        const titleInput = document.getElementById('article-title');
        const slugInput = document.getElementById('article-slug');
        if (titleInput && slugInput) {
            titleInput.addEventListener('input', function () {
                if (!slugInput.dataset.touched) {
                    slugInput.value = titleInput.value
                        .toLowerCase()
                        .trim()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/[\s_-]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                }
            });
            slugInput.addEventListener('input', function () {
                slugInput.dataset.touched = 'true';
            });
        }

        // Initialize TinyMCE without API key / no license prompt (free CDN build from cdnjs)
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '#blog-content-editor',
                height: 540,
                menubar: 'file edit view insert format tools table help',
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount', 'codesample'
                ],
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media codesample | removeformat | code fullscreen',
                images_upload_url: '{{ route('admin.blog.upload_image') }}',
                images_upload_credentials: true,
                automatic_uploads: true,
                relative_urls: false,
                remove_script_host: false,
                convert_urls: true,
                skin: 'oxide',
                content_css: 'default',
                content_style: 'body { font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 15px; line-height: 1.75; color: #1e293b; padding: 14px; max-width: 860px; margin: 0 auto; } img { max-width: 100%; height: auto; border-radius: 8px; margin: 12px 0; } pre { background: #f8fafc; padding: 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 13.5px; overflow-x: auto; } blockquote { border-left: 3px solid #6366f1; padding-left: 14px; color: #475569; font-style: italic; margin: 14px 0; } h2 { font-size: 22px; font-weight: 700; color: #0f172a; margin-top: 28px; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; } h3 { font-size: 18px; font-weight: 600; color: #1e293b; margin-top: 22px; margin-bottom: 10px; } p { margin-bottom: 16px; } ul, ol { margin-bottom: 16px; padding-left: 24px; } li { margin-bottom: 6px; }',
                setup: function (editor) {
                    editor.on('change', function () {
                        editor.save();
                    });
                }
            });
        }
    });
</script>
@endsection
