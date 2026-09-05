<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\MediaReferenceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->publishedPosts()->with('category');
        $search = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', ''));

        if ($search !== '') {
            $query->where(function (Builder $postQuery) use ($search): void {
                $postQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($category !== '') {
            $query->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('slug', $category));
        }

        return view('frontend.posts.index', [
            'posts' => $query->latest('published_at')->latest('id')->paginate(9)->withQueryString()->through(fn ($post) => $this->present($post)),
            'categories' => PostCategory::query()
                ->where('is_active', true)
                ->withCount(['posts' => fn (Builder $postQuery) => $this->publishedPosts($postQuery)])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'filters' => ['q' => $search, 'category' => $category],
        ]);
    }

    public function show(Post $post): View
    {
        abort_unless($this->isPublished($post), 404);

        $post->load('category', 'author');
        $post->increment('view_count');

        $relatedPosts = $this->publishedPosts()
            ->whereKeyNot($post->getKey())
            ->when($post->post_category_id, fn (Builder $query) => $query->where('post_category_id', $post->post_category_id))
            ->with('category')
            ->latest('published_at')
            ->limit(3)
            ->get();

        $sidebarPosts = $this->publishedPosts()
            ->whereKeyNot($post->getKey())
            ->with('category')
            ->latest('published_at')
            ->latest('id')
            ->limit(4)
            ->get();

        $sidebarCategories = PostCategory::query()
            ->where('is_active', true)
            ->withCount(['posts' => fn (Builder $postQuery) => $this->publishedPosts($postQuery)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $this->present($post);
        $relatedPosts->each(fn ($item) => $this->present($item));
        $sidebarPosts->each(fn ($item) => $this->present($item));

        return view('frontend.posts.show', compact('post', 'relatedPosts', 'sidebarPosts', 'sidebarCategories'));
    }

    private function present(Post $post): Post
    {
        $post->setAttribute('cover_url', app(MediaReferenceService::class)->url($post->cover_image));

        return $post;
    }

    private function publishedPosts(?Builder $query = null): Builder
    {
        return ($query ?: Post::query())
            ->where('is_active', true)
            ->where(fn (Builder $published) => $published->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    private function isPublished(Post $post): bool
    {
        return $post->is_active && ($post->published_at === null || $post->published_at->isPast());
    }
}
