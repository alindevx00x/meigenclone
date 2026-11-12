<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Support\WebpImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    /**
     * Display a listing of blog posts for admin/editors.
     */
    public function index(Request $request): View
    {
        $query = BlogPost::with(['category', 'author'])->latest();

        if ($request->filled('q')) {
            $search = trim($request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('blog_category_id', $request->input('category'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $posts = $query->paginate(15)->withQueryString();
        $categories = BlogCategory::orderBy('sort_order')->orderBy('name')->get();

        $stats = [
            'total' => BlogPost::count(),
            'published' => BlogPost::where('status', 'published')->count(),
            'draft' => BlogPost::where('status', 'draft')->count(),
            'views' => BlogPost::sum('views_count'),
        ];

        return view('admin.blog.index', compact('posts', 'categories', 'stats'));
    }

    /**
     * Show form to create a new blog post.
     */
    public function create(): View
    {
        $categories = BlogCategory::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.blog.create', compact('categories'));
    }

    /**
     * Store a newly created blog post.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:blog_posts,slug'],
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'cover_image_file' => ['nullable', 'image', 'max:5120'],
            'cover_image_url' => ['nullable', 'url', 'max:500'],
            'reading_time_minutes' => ['nullable', 'integer', 'min:1', 'max:120'],
            'status' => ['required', 'in:published,draft'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
        ]);

        $coverImagePath = null;

        if ($request->hasFile('cover_image_file')) {
            $coverImagePath = WebpImageStorage::storeUpload($request->file('cover_image_file'), 'blog-covers');
        } elseif (!empty($validated['cover_image_url'])) {
            $coverImagePath = $validated['cover_image_url'];
        }

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);
        $uniqueSlug = $slug;
        $counter = 1;
        while (BlogPost::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = "{$slug}-{$counter}";
            $counter++;
        }

        $post = new BlogPost();
        $post->user_id = auth()->id();
        $post->blog_category_id = $validated['blog_category_id'] ?? null;
        $post->title = $validated['title'];
        $post->slug = $uniqueSlug;
        $post->excerpt = $validated['excerpt'] ?? null;
        $post->content = $validated['content'];
        $post->cover_image = $coverImagePath;
        $post->status = $validated['status'];
        $post->published_at = $validated['status'] === 'published'
            ? ($validated['published_at'] ? date('Y-m-d H:i:s', strtotime($validated['published_at'])) : now())
            : null;
        $post->reading_time_minutes = $validated['reading_time_minutes'] ?: max(1, (int) ceil(str_word_count(strip_tags($validated['content'])) / 200));
        $post->meta_title = $validated['meta_title'] ?? null;
        $post->meta_description = $validated['meta_description'] ?? null;
        $post->meta_keywords = $validated['meta_keywords'] ?? null;
        $post->save();

        return redirect()->route('admin.blog.index')->with('success', "Blog post '{$post->title}' created successfully.");
    }

    /**
     * Show form to edit an existing blog post.
     */
    public function edit(BlogPost $post): View
    {
        $categories = BlogCategory::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.blog.edit', compact('post', 'categories'));
    }

    /**
     * Update an existing blog post.
     */
    public function update(Request $request, BlogPost $post): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:blog_posts,slug,' . $post->id],
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'cover_image_file' => ['nullable', 'image', 'max:5120'],
            'cover_image_url' => ['nullable', 'url', 'max:500'],
            'reading_time_minutes' => ['nullable', 'integer', 'min:1', 'max:120'],
            'status' => ['required', 'in:published,draft'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('cover_image_file')) {
            $post->cover_image = WebpImageStorage::storeUpload($request->file('cover_image_file'), 'blog-covers');
        } elseif (!empty($validated['cover_image_url'])) {
            $post->cover_image = $validated['cover_image_url'];
        }

        $post->blog_category_id = $validated['blog_category_id'] ?? null;
        $post->title = $validated['title'];
        $post->slug = Str::slug($validated['slug']);
        $post->excerpt = $validated['excerpt'] ?? null;
        $post->content = $validated['content'];
        $post->status = $validated['status'];
        
        if ($validated['status'] === 'published') {
            if (empty($post->published_at) || !empty($validated['published_at'])) {
                $post->published_at = !empty($validated['published_at']) 
                    ? date('Y-m-d H:i:s', strtotime($validated['published_at'])) 
                    : now();
            }
        } else {
            $post->published_at = null;
        }

        $post->reading_time_minutes = $validated['reading_time_minutes'] ?: max(1, (int) ceil(str_word_count(strip_tags($validated['content'])) / 200));
        $post->meta_title = $validated['meta_title'] ?? null;
        $post->meta_description = $validated['meta_description'] ?? null;
        $post->meta_keywords = $validated['meta_keywords'] ?? null;
        $post->save();

        return redirect()->route('admin.blog.index')->with('success', "Blog post '{$post->title}' updated successfully.");
    }

    /**
     * Delete a blog post.
     */
    public function destroy(BlogPost $post): RedirectResponse
    {
        $title = $post->title;
        $post->delete();

        return redirect()->route('admin.blog.index')->with('success', "Blog post '{$title}' has been deleted.");
    }

    /**
     * Manage Blog Categories list.
     */
    public function categories(): View
    {
        $categories = BlogCategory::withCount('posts')->orderBy('sort_order')->orderBy('name')->get();
        return view('admin.blog.categories', compact('categories'));
    }

    /**
     * Store a new blog category.
     */
    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:blog_categories,slug'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        BlogCategory::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()->route('admin.blog.categories.index')->with('success', "Category '{$validated['name']}' created successfully.");
    }

    /**
     * Update an existing blog category.
     */
    public function updateCategory(Request $request, BlogCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['required', 'string', 'max:100', 'unique:blog_categories,slug,' . $category->id],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'description' => $validated['description'] ?? null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()->route('admin.blog.categories.index')->with('success', "Category '{$category->name}' updated.");
    }

    /**
     * Delete a blog category.
     */
    public function destroyCategory(BlogCategory $category): RedirectResponse
    {
        if ($category->posts()->count() > 0) {
            return back()->with('error', "Cannot delete category '{$category->name}' because it contains posts. Reassign them first.");
        }

        $category->delete();
        return redirect()->route('admin.blog.categories.index')->with('success', 'Category deleted.');
    }

    /**
     * Upload image handler for TinyMCE.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'max:8192'],
        ]);

        try {
            $path = WebpImageStorage::storeUpload($request->file('file'), 'blog-images');
            $url = Storage::disk('public')->url($path);

            return response()->json([
                'location' => $url,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Failed to upload image: ' . $e->getMessage(),
            ], 422);
        }
    }
}
