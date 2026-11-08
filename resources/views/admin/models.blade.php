@extends('layouts.app')
@section('title', 'Manage AI models')
@section('eyebrow', 'ADMIN STUDIO')
@section('toolbar-title', 'AI Models')
@section('content')
<div class="workspace-page admin-page">
    <div class="page-heading list-heading">
        <div>
            <span class="section-kicker">CURATE GENERATION ENGINES</span>
            <h1>AI Models</h1>
            <p>Manage the AI engines and models available for prompts, submissions, and filters.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert-box alert-error" style="margin-bottom: 20px;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
    @if (session('success'))
        <div class="alert-box alert-success" style="margin-bottom: 20px;">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <section class="category-create panel-card">
        <div>
            <span class="section-kicker">NEW ENGINE</span>
            <h2>Add an AI model</h2>
            <p>Add a new AI model to prompt forms, filtering tabs, and creator badges.</p>
        </div>
        <form class="category-create-form" method="POST" action="{{ route('admin.models.store') }}">
            @csrf
            <input name="name" required maxlength="60" placeholder="Model name (e.g. Flux Pro, Sora)">
            <input name="icon" maxlength="12" placeholder="Icon / Glyph (e.g. ◈, ✦)" aria-label="Model glyph">
            <input name="description" maxlength="255" placeholder="Short description (e.g. High-fidelity rendering engine)">
            <input name="sort_order" type="number" min="0" max="999" value="0" style="width: 80px;" placeholder="Order" aria-label="Sort order">
            <button class="primary-button" type="submit">Add AI model <span>＋</span></button>
        </form>
    </section>

    <div class="category-admin-list">
        @foreach ($models as $aiModel)
            <article class="category-admin-card">
                <div class="category-admin-info">
                    <span class="category-large-icon">{{ $aiModel->icon }}</span>
                    <div>
                        <strong>{{ $aiModel->name }}</strong>
                        <small>{{ $aiModel->prompts_count }} {{ \Illuminate\Support\Str::plural('prompt', $aiModel->prompts_count) }} · /{{ $aiModel->slug }} {{ $aiModel->is_active ? '· Active' : '· Disabled' }}</small>
                        @if ($aiModel->description)
                            <div style="font-size: 11.5px; color: #888891; margin-top: 3px;">{{ $aiModel->description }}</div>
                        @endif
                    </div>
                </div>
                <form class="category-edit-form" method="POST" action="{{ route('admin.models.update', $aiModel) }}">
                    @csrf
                    @method('PUT')
                    <input name="name" required maxlength="60" value="{{ $aiModel->name }}" aria-label="Model name" title="Model name">
                    <input name="slug" required maxlength="80" value="{{ $aiModel->slug }}" aria-label="Model slug" title="Model slug">
                    <input name="icon" maxlength="12" value="{{ $aiModel->icon }}" aria-label="Model glyph" title="Model glyph" style="width: 55px;">
                    <input name="sort_order" type="number" min="0" max="999" value="{{ $aiModel->sort_order }}" aria-label="Sort order" title="Sort order" style="width: 65px;">
                    <button class="outline-button" type="submit">Save</button>
                </form>
                <form method="POST" action="{{ route('admin.models.destroy', $aiModel) }}" onsubmit="return confirm('Remove {{ $aiModel->name }}? Models with existing prompts cannot be deleted.')">
                    @csrf
                    @method('DELETE')
                    <button class="icon-delete" type="submit" aria-label="Delete {{ $aiModel->name }}">×</button>
                </form>
            </article>
        @endforeach
    </div>
</div>
@endsection
