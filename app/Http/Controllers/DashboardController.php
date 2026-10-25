<?php

namespace App\Http\Controllers;

use App\Models\Hashtag;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $stats = [
            'posts_count' => $user->posts()->count(),
            'following_count' => $user->following()->count(),
            'followers_count' => $user->followers()->count(),
            'likes_received' => (int) $user->posts()->sum('likes_count'),
        ];

        // Feed: posts from followed users + own posts
        $followingIds = $user->following()->pluck('users.id')->push($user->id);

        $feedPosts = Post::with(['user', 'hashtags', 'likes'])
            ->whereIn('user_id', $followingIds)
            ->latest()
            ->paginate(15);

        $trendingHashtags = Hashtag::orderByDesc('posts_count')->take(6)->get();

        return view('dashboard', compact('stats', 'feedPosts', 'trendingHashtags'));
    }
}
