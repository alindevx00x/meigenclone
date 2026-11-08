@extends('layouts.app')
@section('title', 'Edit community prompt')
@section('eyebrow', 'ADMIN STUDIO')
@section('toolbar-title', 'Edit submission')
@section('content')
<div class="workspace-page form-page">
    <a class="back-link" href="{{ route('admin.prompts.index') }}"><span>←</span> Back to prompt management</a>
    <div class="page-heading form-heading"><div><span class="section-kicker">ADMIN EDITOR</span><h1>Review and refine.</h1><p>Edit the creator’s submission, change its status, or feature it in the gallery.</p></div></div>
    <form class="prompt-form panel-card" method="POST" action="{{ route('admin.prompts.update', $prompt) }}" enctype="multipart/form-data">@csrf @method('PUT')
        <div class="form-grid">
            <label class="wide-field"><span>Prompt title <span class="required-star">*</span></span><input type="text" name="title" required maxlength="120" value="{{ old('title', $prompt->title) }}"></label>
            <label><span>Category <span class="required-star">*</span></span><select name="category_id" required>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $prompt->category_id) == $category->id)>{{ $category->icon }} &nbsp;{{ $category->name }}</option>@endforeach</select></label>
            <label><span>AI model <span class="required-star">*</span></span><select name="model" required>@foreach ($models as $aiModel)<option value="{{ $aiModel->name }}" @selected(old('model', $prompt->model) === $aiModel->name)>{{ $aiModel->icon }} &nbsp;{{ $aiModel->name }}</option>@endforeach</select></label>
            <label class="wide-field"><span>Prompt text <span class="required-star">*</span></span><textarea name="prompt_text" rows="10" required minlength="20" maxlength="12000">{{ old('prompt_text', $prompt->prompt_text) }}</textarea></label>
            <label class="wide-field">Image URL <span class="optional-label">Optional</span><input type="url" name="image_url" value="{{ old('image_url', $prompt->image_url) }}" placeholder="https://…"></label>
            <label class="wide-field">Replace cover image <span class="optional-label">Optional</span><input class="file-input" type="file" name="image" accept="image/*"></label>
            <label>Status<select name="status"><option value="pending" @selected($prompt->status === 'pending')>Pending review</option><option value="approved" @selected($prompt->status === 'approved')>Published</option><option value="rejected" @selected($prompt->status === 'rejected')>Declined</option></select></label>
            <label>Review note <span class="optional-label">Shown to creator</span><input type="text" name="admin_note" maxlength="1000" value="{{ old('admin_note', $prompt->admin_note) }}" placeholder="Add a note if changes are needed"></label>
            <label class="check-label wide-field"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $prompt->is_featured))> Feature this prompt in the public gallery</label>
        </div>
        <div class="form-footer"><span><i>◎</i> Changes are reflected in the public gallery</span><div><a class="outline-button" href="{{ route('admin.prompts.index') }}">Cancel</a><button class="primary-button" type="submit">Save changes <span>→</span></button></div></div>
    </form>
</div>
@endsection
