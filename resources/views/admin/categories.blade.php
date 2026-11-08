@extends('layouts.app')
@section('title', 'Manage categories')
@section('eyebrow', 'ADMIN STUDIO')
@section('toolbar-title', 'Categories')
@section('content')
<div class="workspace-page admin-page">
    <div class="page-heading list-heading"><div><span class="section-kicker">KEEP THE LIBRARY ORGANIZED</span><h1>Categories</h1><p>Categories make it easier for creators to find the right spark.</p></div></div>
    <section class="category-create panel-card"><div><span class="section-kicker">NEW COLLECTION</span><h2>Add a category</h2><p>Give a new style or use case a home in the sidebar.</p></div>
        <form class="category-create-form" method="POST" action="{{ route('admin.categories.store') }}">@csrf
            <input name="name" required maxlength="60" placeholder="e.g. Editorial & Fashion"><input name="icon" maxlength="12" placeholder="✦" aria-label="Category icon"><input name="description" maxlength="160" placeholder="Short description (optional)"><button class="primary-button" type="submit">Add category <span>＋</span></button>
        </form>
    </section>
    <div class="category-admin-list">
        @foreach ($categories as $category)
            <article class="category-admin-card"><div class="category-admin-info"><span class="category-large-icon">{{ $category->icon }}</span><div><strong>{{ $category->name }}</strong><small>{{ $category->prompts_count }} {{ \Illuminate\Support\Str::plural('prompt', $category->prompts_count) }} &middot; /{{ $category->slug }}</small>@if ($category->description)<div style="font-size: 11.5px; color: #888891; margin-top: 3px;">{{ $category->description }}</div>@endif</div></div>
                <form class="category-edit-form" method="POST" action="{{ route('admin.categories.update', $category) }}">@csrf @method('PUT')<input name="name" required maxlength="60" value="{{ $category->name }}" aria-label="Category name" placeholder="Name"><input name="slug" required maxlength="80" value="{{ $category->slug }}" aria-label="Category slug" placeholder="Slug"><input name="icon" maxlength="12" value="{{ $category->icon }}" aria-label="Category icon" placeholder="Icon" style="width: 50px;"><input name="description" maxlength="160" value="{{ $category->description }}" aria-label="Category description" placeholder="Short description (optional)"><button class="outline-button" type="submit">Save</button></form>
                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Remove this category? Categories with prompts cannot be deleted.')">@csrf @method('DELETE')<button class="icon-delete" type="submit" aria-label="Delete {{ $category->name }}">×</button></form>
            </article>
        @endforeach
    </div>
</div>
@endsection
