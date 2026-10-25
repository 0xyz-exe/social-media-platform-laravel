<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Post Discussion') }}
            </h2>
            <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-700">
                &larr; Back to timeline
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 space-y-6">
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Main Post Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-sky-400 to-indigo-500 text-white font-bold text-base flex items-center justify-center">
                            {{ strtoupper(substr($post->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 text-sm">{{ $post->user->name }}</div>
                            <div class="text-gray-400 font-mono text-xs">@<span>{{ $post->user->username }}</span> &bull; {{ $post->created_at->format('M d, Y H:i') }}</div>
                        </div>
                    </div>

                    @if($post->user_id === Auth::id())
                        <form method="POST" action="{{ route('posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1 bg-rose-50 text-rose-600 rounded-lg text-xs font-bold hover:bg-rose-100 transition">
                                Delete Post
                            </button>
                        </form>
                    @endif
                </div>

                <p class="text-gray-800 text-base leading-relaxed whitespace-pre-line">{{ $post->content }}</p>

                @if($post->hashtags->isNotEmpty())
                    <div class="flex flex-wrap gap-1.5 pt-2">
                        @foreach($post->hashtags as $tag)
                            <a href="{{ route('posts.index', ['tag' => $tag->name]) }}" class="text-xs font-bold text-sky-600 hover:underline">
                                #{{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="pt-4 border-t border-gray-100 flex items-center space-x-6 text-sm text-gray-500">
                    <form method="POST" action="{{ route('posts.like', $post) }}">
                        @csrf
                        <button type="submit" class="flex items-center space-x-1.5 {{ $post->isLikedBy(Auth::user()) ? 'text-rose-600 font-bold' : 'hover:text-rose-600' }}">
                            <span class="text-lg">{{ $post->isLikedBy(Auth::user()) ? '♥' : '♡' }}</span>
                            <span>{{ $post->likes_count }} Likes</span>
                        </button>
                    </form>

                    <div class="flex items-center space-x-1.5">
                        <span>💬</span>
                        <span>{{ $post->comments_count }} Comments</span>
                    </div>
                </div>
            </div>

            <!-- Comment Composer -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <form method="POST" action="{{ route('posts.comments.store', $post) }}" class="space-y-3">
                    @csrf
                    <textarea name="content" rows="2" required placeholder="Write a thoughtful reply..." class="w-full text-xs border-gray-200 rounded-xl focus:border-sky-500 focus:ring-sky-500"></textarea>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-sky-500 hover:bg-sky-600 text-white font-bold rounded-xl text-xs transition">
                            Reply
                        </button>
                    </div>
                </form>
            </div>

            <!-- Comments Thread -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900 text-sm">Replies</h3>
                </div>
                @if($post->comments->isEmpty())
                    <div class="p-8 text-center text-gray-400 text-xs">
                        No replies yet. Be the first to join the conversation!
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($post->comments as $comment)
                            <div class="p-4 space-y-1">
                                <div class="flex justify-between items-baseline">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-bold text-gray-900 text-xs">{{ $comment->user->name }}</span>
                                        <span class="text-[11px] font-mono text-gray-400">@<span>{{ $comment->user->username }}</span></span>
                                    </div>
                                    <span class="text-[10px] text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-xs text-gray-700 whitespace-pre-line">{{ $comment->content }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
