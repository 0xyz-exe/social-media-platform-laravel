<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LikeController extends Controller
{
    public function toggle(Request $request, Post $post): RedirectResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user, $post) {
            $existing = Like::where('user_id', $user->id)->where('post_id', $post->id)->first();

            if ($existing) {
                $existing->delete();
                $post->decrement('likes_count');
            } else {
                Like::create([
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                ]);
                $post->increment('likes_count');
            }
        });

        return back();
    }
}
