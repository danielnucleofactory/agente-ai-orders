<div class="space-y-4">
    @foreach($comments as $comment)
        <div class="p-4 bg-white rounded-lg shadow">
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-semibold">{{ $comment['user'] }}</p>
                    <p class="text-sm text-gray-500">{{ $comment['created_at'] }}</p>
                </div>
            </div>
            <p class="mt-2">{{ $comment['comment'] }}</p>
            @if($comment['attachment'])
                <div class="flex items-center mt-2 space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" />
                    </svg>
                    <a href="{{ $comment['attachment']['url'] }}"
                       target="_blank"
                       class="text-sm text-blue-600 hover:text-blue-800">
                        {{ $comment['attachment']['name'] }}
                    </a>
                </div>
            @endif
        </div>
    @endforeach
</div>
