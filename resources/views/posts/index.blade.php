<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Explore PulseNet') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Search & Filter Bar -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-4 items-center justify-between">
                <form method="GET" action="{{ route('posts.index') }}" class="w-full flex flex-col sm:flex-row gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search posts or creators..." class="flex-1 text-xs border-gray-200 rounded-xl focus:border-sky-500 focus:ring-sky-500">
                    <input type="text" name="tag" value="{{ request('tag') }}" placeholder="Filter hashtag (e.g. tech)..." class="w-full sm:w-48 text-xs border-gray-200 rounded-xl focus:border-sky-500 focus:ring-sky-500 font-mono">
                    <button type="submit" class="px-5 py-2 bg-sky-500 hover:bg-sky-600 text-white font-bold rounded-xl text-xs transition">
                        Search
                    </button>
                    @if(request()->hasAny(['search', 'tag']))
                        <a href="{{ route('posts.index') }}" class="px-3 py-2 text-xs text-gray-400 hover:text-gray-600 self-center">Clear</a>
                    @endif
                </form>
            </div>

            <!-- Trending Hashtags Ribbon -->
            @if($trendingHashtags->isNotEmpty())
                <div class="flex items-center space-x-2 overflow-x-auto pb-2 text-xs">
                    <span class="text-gray-400 font-bold uppercase text-[10px]">Trending:</span>
                    @foreach($trendingHashtags as $tag)
                        <a href="{{ route('posts.index', ['tag' => $tag->name]) }}" class="px-3 py-1 bg-white border border-gray-200 hover:border-sky-300 hover:text-sky-600 rounded-full font-bold whitespace-nowrap transition">
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- Posts List -->
            @if($posts->isEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center text-gray-400 text-xs">
                    No posts found matching your criteria.
                </div>
            @else
                <div class="max-w-2xl mx-auto space-y-4">
                    @foreach($posts as $post)
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-3 hover:border-gray-200 transition">
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
                                        <button type="submit" class="px-3 py-1 text-xs font-bold rounded-full {{ Auth::user()->isFollowing($post->user) ? 'bg-gray-100 text-gray-700' : 'bg-sky-50 text-sky-600 hover:bg-sky-100' }}">
                                            {{ Auth::user()->isFollowing($post->user) ? 'Following' : '+ Follow' }}
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <!-- Content -->
                            <p class="text-gray-800 text-sm leading-relaxed whitespace-pre-line">{{ $post->content }}</p>

                            <!-- Hashtags -->
                            @if($post->hashtags->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($post->hashtags as $tag)
                                        <a href="{{ route('posts.index', ['tag' => $tag->name]) }}" class="text-xs font-semibold text-sky-600 hover:underline">
                                            #{{ $tag->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Action Bar -->
                            <div class="pt-3 border-t border-gray-100 flex items-center space-x-6 text-xs text-gray-500">
                                <form method="POST" action="{{ route('posts.like', $post) }}">
                                    @csrf
                                    <button type="submit" class="flex items-center space-x-1.5 {{ $post->isLikedBy(Auth::user()) ? 'text-rose-600 font-bold' : 'hover:text-rose-600' }}">
                                        <span>{{ $post->isLikedBy(Auth::user()) ? '♥' : '♡' }}</span>
                                        <span>{{ $post->likes_count }}</span>
                                    </button>
                                </form>

                                <a href="{{ route('posts.show', $post) }}" class="flex items-center space-x-1.5 hover:text-sky-600">
                                    <span>💬</span>
                                    <span>{{ $post->comments_count }} comments</span>
                                </a>
                            </div>
                        </div>
                    @endforeach

                    <div>
                        {{ $posts->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
