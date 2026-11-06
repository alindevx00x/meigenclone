@extends('layouts.app')
@section('title', $editing ? 'Edit prompt' : 'Submit a prompt')
@section('eyebrow', 'YOUR CREATOR SPACE')
@section('toolbar-title', 'Share an idea')
@section('content')
<div class="workspace-page form-page">
    <a class="back-link" href="{{ route('dashboard.index') }}"><span>←</span> Back to my studio</a>
    <div class="page-heading form-heading"><div><span class="section-kicker">{{ $editing ? 'REFINE YOUR IDEA' : 'MAKE SOMETHING SHAREABLE' }}</span><h1>{{ $editing ? 'Edit your prompt' : 'Share a prompt with the world.' }}</h1><p>{{ $directorySettings['require_approval'] === '1' ? 'Every prompt is reviewed by our team before it appears in the public gallery.' : 'Your prompt will appear in the public gallery as soon as you publish it.' }}</p></div></div>
    <div class="review-banner"><span>✦</span><div><strong>Good prompts make great things possible.</strong><p>Add a clear title, choose the right category, and include a preview image if you have one.</p></div><small>{{ $directorySettings['require_approval'] === '1' ? 'Admin review' : 'Instant publish' }}<br>for every prompt</small></div>
    <form class="prompt-form panel-card" method="POST" action="{{ $editing ? route('dashboard.prompts.update', $prompt) : route('dashboard.prompts.store') }}" enctype="multipart/form-data">
        @csrf @if ($editing) @method('PUT') @endif
        <div class="form-grid">
            <label class="wide-field"><span>Prompt title <span class="required-star">*</span></span><input type="text" name="title" required maxlength="120" value="{{ old('title', $prompt->title) }}" placeholder="A quiet morning in Kyoto, rendered in soft film grain"><small>A short, descriptive name helps people discover your work.</small></label>
            <label><span>Category <span class="required-star">*</span></span><select name="category_id" required><option value="">Choose a category</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $prompt->category_id) == $category->id)>{{ $category->icon }} &nbsp;{{ $category->name }}</option>@endforeach</select></label>
            <label><span>AI model <span class="required-star">*</span></span><select name="model" required>@foreach ($models as $aiModel)<option value="{{ $aiModel->name }}" @selected(old('model', $prompt->model ?: 'GPT Image') === $aiModel->name)>{{ $aiModel->icon }} &nbsp;{{ $aiModel->name }}</option>@endforeach</select></label>
            <label class="wide-field"><span>Your prompt <span class="required-star">*</span></span><textarea name="prompt_text" rows="10" required minlength="20" maxlength="12000" placeholder="Describe the subject, composition, lighting, style, mood, and any details that make this prompt work…">{{ old('prompt_text', $prompt->prompt_text) }}</textarea><small>Be specific and useful. Include the parts someone can customize.</small></label>
            <label class="wide-field"><span>Preview image <span class="optional-label">Optional</span></span><input class="file-input" type="file" name="image" accept="image/*"><small>Upload a JPG, PNG, or WebP up to 5 MB. A cover image makes your prompt stand out.</small></label>
            <label class="wide-field"><span>Or add an image URL <span class="optional-label">Optional</span></span><input type="url" name="image_url" value="{{ old('image_url', $prompt->image_url) }}" placeholder="https://…"><small>If you upload a cover image, it will be used instead of this URL.</small></label>
        </div>
        @if ($editing && $prompt->cover_url)<div class="current-cover"><img src="{{ $prompt->cover_url }}" alt="Current cover"><span>Current cover preview</span></div>@endif
        <div class="form-footer"><span><i>◎</i> {{ $directorySettings['require_approval'] === '1' ? 'Submissions are reviewed before they go live' : 'Submissions go live immediately' }}</span><div><a class="outline-button" href="{{ route('dashboard.index') }}">Cancel</a><button class="primary-button" type="submit">{{ $editing ? ($directorySettings['require_approval'] === '1' ? 'Save & resubmit' : 'Save changes') : ($directorySettings['require_approval'] === '1' ? 'Submit for review' : 'Publish prompt') }} <span>→</span></button></div></div>
    </form>
</div>
@endsection
