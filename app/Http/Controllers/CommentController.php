<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user, $post, $validated) {
            Comment::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
                'content' => $validated['content'],
            ]);

            $post->increment('comments_count');
        });

        return back()->with('success', 'Comment posted!');
    }
}
