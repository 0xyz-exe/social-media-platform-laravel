<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Social Home & Feed') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Left Profile Summary Widget -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-sky-400 to-indigo-600 text-white font-extrabold text-2xl flex items-center justify-center mx-auto shadow-sm">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <h3 class="font-bold text-gray-900 text-base mt-3">{{ Auth::user()->name }}</h3>
                        <p class="text-xs text-sky-600 font-mono">@<span>{{ Auth::user()->username }}</span></p>

                        <div class="grid grid-cols-3 gap-2 mt-5 pt-4 border-t border-gray-100 text-xs">
                            <div>
                                <span class="font-extrabold text-gray-900 block text-sm">{{ $stats['posts_count'] }}</span>
                                <span class="text-gray-400 text-[11px]">Posts</span>
                            </div>
                            <div>
                                <span class="font-extrabold text-gray-900 block text-sm">{{ $stats['following_count'] }}</span>
                                <span class="text-gray-400 text-[11px]">Following</span>
                            </div>
                            <div>
                                <span class="font-extrabold text-gray-900 block text-sm">{{ $stats['followers_count'] }}</span>
                                <span class="text-gray-400 text-[11px]">Followers</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Middle Feed & Post Composer -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Create Post Box -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <form method="POST" action="{{ route('posts.store') }}" class="space-y-3">
                            @csrf
                            <textarea name="content" rows="3" required placeholder="What's happening? Share thoughts or #hashtags..." class="w-full text-sm border-gray-200 rounded-xl focus:border-sky-500 focus:ring-sky-500 placeholder-gray-400"></textarea>

                            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                <select name="visibility" class="text-xs border-gray-200 rounded-lg text-gray-600">
                                    <option value="public">Public (Everyone)</option>
                                    <option value="followers">Followers Only</option>
                                    <option value="private">Private (Only Me)</option>
                                </select>
                                <button type="submit" class="px-5 py-2 bg-sky-500 hover:bg-sky-600 text-white font-bold rounded-xl text-xs shadow transition">
                                    Post
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Post Stream -->
                    @if($feedPosts->isEmpty())
                        <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center text-gray-400 text-xs space-y-2">
                            <p>Your timeline is quiet. Follow more creators or visit the Explore feed!</p>
                            <a href="{{ route('posts.index') }}" class="text-sky-600 font-bold hover:underline inline-block">Explore trending posts &rarr;</a>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($feedPosts as $post)
                                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-3 hover:border-gray-200 transition">
                                    <!-- Author Header -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-sky-400 to-indigo-500 text-white font-bold text-sm flex items-center justify-center">
                                                {{ strtoupper(substr($post->user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="flex items-center space-x-1.5">
                                                    <span class="font-bold text-gray-900 text-xs">{{ $post->user->name }}</span>
                                                    <span class="text-gray-400 font-mono text-[11px]">@<span>{{ $post->user->username }}</span></span>
                                                </div>
                                                <span class="text-gray-400 text-[10px]">{{ $post->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>

                                        @if($post->user_id !== Auth::id())
                                            <form method="POST" action="{{ route('users.follow', $post->user) }}">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 text-[11px] font-bold rounded-full {{ Auth::user()->isFollowing($post->user) ? 'bg-gray-100 text-gray-700 hover:bg-rose-50 hover:text-rose-600' : 'bg-sky-50 text-sky-600 hover:bg-sky-100' }}">
                                                    {{ Auth::user()->isFollowing($post->user) ? 'Following' : '+ Follow' }}
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-400 hover:text-rose-600 text-xs">✕</button>
                                            </form>
                                        @endif
                                    </div>

                                    <!-- Content -->
                                    <p class="text-gray-800 text-xs sm:text-sm leading-relaxed whitespace-pre-line">{{ $post->content }}</p>

                                    <!-- Hashtags -->
                                    @if($post->hashtags->isNotEmpty())
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($post->hashtags as $tag)
                                                <a href="{{ route('posts.index', ['tag' => $tag->name]) }}" class="text-[11px] font-semibold text-sky-600 hover:underline">
                                                    #{{ $tag->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Action Bar: Likes & Comments -->
                                    <div class="pt-2 border-t border-gray-100 flex items-center space-x-6 text-xs text-gray-500">
                                        <!-- Like Toggle -->
                                        <form method="POST" action="{{ route('posts.like', $post) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="flex items-center space-x-1.5 {{ $post->isLikedBy(Auth::user()) ? 'text-rose-600 font-bold' : 'hover:text-rose-600' }}">
                                                <span class="text-base">{{ $post->isLikedBy(Auth::user()) ? '♥' : '♡' }}</span>
                                                <span>{{ $post->likes_count }}</span>
                                            </button>
                                        </form>

                                        <!-- Comment Count / Link -->
                                        <a href="{{ route('posts.show', $post) }}" class="flex items-center space-x-1.5 hover:text-sky-600">
                                            <span>💬</span>
                                            <span>{{ $post->comments_count }} comments</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div>
                            {{ $feedPosts->links() }}
                        </div>
                    @endif
                </div>

                <!-- Right Trending Sidebar -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <h4 class="font-bold text-gray-900 text-sm mb-3">Trending Topics</h4>
                        @if($trendingHashtags->isEmpty())
                            <p class="text-xs text-gray-400">No hashtags trending yet.</p>
                        @else
                            <div class="space-y-2.5 text-xs">
                                @foreach($trendingHashtags as $t)
                                    <div class="flex justify-between items-center">
                                        <a href="{{ route('posts.index', ['tag' => $t->name]) }}" class="font-bold text-gray-800 hover:text-sky-600">
                                            #{{ $t->name }}
                                        </a>
                                        <span class="text-[11px] text-gray-400 font-mono">{{ $t->posts_count }} posts</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
