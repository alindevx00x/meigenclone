@extends('layouts.app')
@section('title', 'Manage blog categories')
@section('eyebrow', 'EDITORIAL STUDIO')
@section('toolbar-title', 'Blog categories')
@section('content')
<div class="workspace-page admin-page">
    <div style="margin-bottom: 12px;">
        <a class="back-link" href="{{ route('admin.blog.index') }}" style="margin-bottom: 0;"><span>←</span> Back to blog articles</a>
    </div>

    <div class="page-heading list-heading">
        <div>
            <span class="section-kicker">EDITORIAL TAXONOMY</span>
            <h1>Blog Categories</h1>
            <p>Organize blog posts into thematic sections to help visitors and search engines discover guides.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-box alert-success" style="margin-bottom: 20px;">
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="alert-box alert-error" style="margin-bottom: 20px;">
            <p>{{ session('error') }}</p>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert-box alert-error" style="margin-bottom: 20px;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Add Category Form -->
    <section class="category-create panel-card" style="margin-bottom: 24px;">
        <div>
            <span class="section-kicker">NEW TOPIC</span>
            <h2>Add blog category</h2>
            <p>Create a category for tutorials, case studies, or prompt engineering techniques.</p>
        </div>
        <form class="category-create-form" method="POST" action="{{ route('admin.blog.categories.store') }}">
            @csrf
            <input name="name" required maxlength="80" placeholder="e.g. Prompt Engineering Guides">
            <input name="slug" maxlength="100" placeholder="e.g. prompt-engineering (optional)">
            <input name="description" maxlength="500" placeholder="Short description for SEO and archive page">
            <input name="sort_order" type="number" value="0" style="width: 70px;" placeholder="Order" aria-label="Sort order">
            <button class="primary-button" type="submit">Add category <span>＋</span></button>
        </form>
    </section>

    <!-- Categories List -->
    <div class="category-admin-list">
        @forelse ($categories as $category)
            <article class="category-admin-card" style="align-items: center;">
                <div class="category-admin-info" style="min-width: 220px;">
                    <div>
                        <strong>{{ $category->name }}</strong>
                        <small>{{ $category->posts_count }} {{ \Illuminate\Support\Str::plural('article', $category->posts_count) }} &middot; /blog/category/{{ $category->slug }}</small>
                        @if ($category->description)
                            <div style="font-size: 11.5px; color: #888891; margin-top: 3px;">{{ $category->description }}</div>
                        @endif
                    </div>
                </div>

                <form class="category-edit-form" method="POST" action="{{ route('admin.blog.categories.update', $category) }}" style="flex: 1; display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                    @csrf
                    @method('PUT')
                    <input name="name" required maxlength="80" value="{{ $category->name }}" aria-label="Category name" placeholder="Name" style="flex: 1 1 140px;">
                    <input name="slug" required maxlength="100" value="{{ $category->slug }}" aria-label="Category slug" placeholder="Slug" style="flex: 1 1 140px;">
                    <input name="description" maxlength="500" value="{{ $category->description }}" aria-label="Category description" placeholder="Short description" style="flex: 2 1 200px;">
                    <input name="sort_order" type="number" value="{{ $category->sort_order }}" style="width: 60px;" placeholder="0" aria-label="Sort order">
                    <button class="outline-button" type="submit">Save</button>
                </form>

                <form method="POST" action="{{ route('admin.blog.categories.destroy', $category) }}" onsubmit="return confirm('Remove this category? Categories with articles cannot be deleted.')">
                    @csrf
                    @method('DELETE')
                    <button class="icon-delete" type="submit" aria-label="Delete {{ $category->name }}">×</button>
                </form>
            </article>
        @empty
            <div class="panel-card" style="text-align: center; padding: 32px; color: #64748b;">
                No blog categories yet. Create your first category above!
            </div>
        @endforelse
    </div>
</div>
@endsection
