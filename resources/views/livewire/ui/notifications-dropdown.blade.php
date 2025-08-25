<div>
    <button @click="open = !open" class="h-6 w-6 bg-white border border-[#E4E7EC] rounded-[0.25rem] flex items-center justify-center relative">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="15" viewBox="0 0 12 15" fill="none">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M9.99998 4.5C9.99998 3.43913 9.57855 2.42172 8.8284 1.67157C8.07826 0.921427 7.06084 0.5 5.99998 0.5C4.93911 0.5 3.92169 0.921427 3.17155 1.67157C2.4214 2.42172 1.99998 3.43913 1.99998 4.5V6.879C1.99963 7.27669 1.84136 7.65797 1.55998 7.939L0.293977 9.207C0.106427 9.39449 0.0010332 9.6488 0.000976562 9.914V10.5C0.000976562 10.7652 0.106334 11.0196 0.29387 11.2071C0.481406 11.3946 0.73576 11.5 1.00098 11.5H3.00098C3.00098 12.2956 3.31705 13.0587 3.87966 13.6213C4.44227 14.1839 5.20533 14.5 6.00098 14.5C6.79663 14.5 7.55969 14.1839 8.1223 13.6213C8.68491 13.0587 9.00098 12.2956 9.00098 11.5H11.001C11.2662 11.5 11.5205 11.3946 11.7081 11.2071C11.8956 11.0196 12.001 10.7652 12.001 10.5V9.914C12.0009 9.6488 11.8955 9.39449 11.708 9.207L10.44 7.94C10.1586 7.65897 10.0003 7.27769 9.99998 6.88V4.5ZM4.49998 11.5C4.49998 11.8978 4.65801 12.2794 4.93932 12.5607C5.22062 12.842 5.60215 13 5.99998 13C6.3978 13 6.77933 12.842 7.06064 12.5607C7.34194 12.2794 7.49998 11.8978 7.49998 11.5H4.49998Z" fill="black" />
        </svg>

        @if($unreadCount > 0)
            <span class="absolute flex items-center justify-center w-4 h-4 text-xs text-white bg-red-500 rounded-full -top-1 -right-1">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Panel de Notificaciones -->
    <div x-show="open" x-cloak @click.away="open = false" class="absolute right-0 z-10 w-[420px] mt-2 bg-white border border-gray-200 rounded-md shadow-lg">
        <div class="flex items-center justify-between p-3 border-b">
            <h3 class="font-semibold text-gray-700">Notificaciones</h3>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs text-blue-600 hover:text-blue-800">
                    Marcar todas como leídas
                </button>
            @endif
        </div>

        <div class="max-h-[350px] overflow-y-auto">
            @if(count($notifications) > 0)
                @foreach($notifications as $notification)
                    <div class="flex p-3 border-b hover:bg-gray-50 {{ !$notification->isRead() ? 'bg-blue-50' : '' }}">
                        <div class="flex-1 w-full">
                            <p class="text-sm font-semibold text-gray-800">{{ $notification->title }}</p>
                            <p class="text-sm text-gray-600">{{ $notification->message }}</p>
                            <div class="flex items-center justify-between mt-1">
                                <p class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                                @if(!$notification->isRead())
                                    <button wire:click="markAsRead({{ $notification->id }})" class="text-xs text-blue-600 hover:text-blue-800">
                                        Marcar como leída
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="p-4 text-center text-gray-500">
                    No tienes notificaciones.
                </div>
            @endif
        </div>
    </div>
</div>
