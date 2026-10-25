<?php

namespace App\Http\Controllers;

use App\Models\Hashtag;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $query = Post::with(['user', 'hashtags', 'likes'])->where('visibility', '!=', 'private')->latest();

        if ($request->filled('tag')) {
            $tag = $request->input('tag');
            $query->whereHas('hashtags', function ($q) use ($tag) {
                $q->where('name', ltrim($tag, '#'));
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        $posts = $query->paginate(20)->withQueryString();
        $trendingHashtags = Hashtag::orderByDesc('posts_count')->take(10)->get();

        return view('posts.index', compact('posts', 'trendingHashtags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|min:2|max:1000',
            'visibility' => 'nullable|in:public,followers,private',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user, $validated) {
            $post = Post::create([
                'user_id' => $user->id,
                'content' => $validated['content'],
                'visibility' => $validated['visibility'] ?? 'public',
                'likes_count' => 0,
                'comments_count' => 0,
            ]);

            // Extract hashtags with regex
            preg_match_all('/#([a-zA-Z0-9_]+)/', $validated['content'], $matches);

            if (!empty($matches[1])) {
                $uniqueTags = array_unique(array_map('strtolower', $matches[1]));
                foreach ($uniqueTags as $tagName) {
                    $hashtag = Hashtag::firstOrCreate(
                        ['name' => $tagName],
                        ['posts_count' => 0]
                    );

                    $hashtag->increment('posts_count');
                    $post->hashtags()->attach($hashtag->id);
                }
            }
        });

        return back()->with('success', 'Your thought has been shared!');
    }

    public function show(Request $request, Post $post): View
    {
        $post->load(['user', 'hashtags', 'comments.user', 'likes']);

        return view('posts.show', compact('post'));
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        if ($post->user_id !== $request->user()->id) {
            abort(403);
        }

        DB::transaction(function () use ($post) {
            foreach ($post->hashtags as $hashtag) {
                $hashtag->decrement('posts_count');
            }
            $post->delete();
        });

        return redirect()->route('posts.index')->with('success', 'Post deleted.');
    }
}
