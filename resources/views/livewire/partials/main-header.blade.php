<header x-data="{ open: false }">
    <div class="flex justify-between items-center py-6 px-2 sm:pt-[2.625rem] sm:pb-5 sm:px-10">
        <div class="text-left grow">
            @if(file_exists(app_path('View/Components/Breadcrumb.php')))
                <x-breadcrumb />
            @else
                <x-simple-breadcrumb />
            @endif
        </div>

        <!-- Settings Dropdown -->
        <div class="hidden space-x-4 sm:flex sm:items-center sm:ms-6">
            <x-dropdown align="left" width="48">
                <x-slot name="trigger">
                    <button
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out border border-transparent rounded-md hover:bg-white focus:out line-none">

                        <div class="flex items-center justify-center bg-gray-400 rounded-full">
                            @if(auth()->user()->getFirstMediaUrl('profile-photo'))
                                <div class="h-[40px] w-[40px] overflow-hidden rounded-full bg-[#190FDB] flex items-center justify-center text-white font-medium">
                                    <img src="{{ auth()->user()->getFirstMediaUrl('profile-photo') }}" alt="Profile" class="object-cover w-full h-full rounded-full">
                                </div>
                            @else
                                <div class="avatar-container h-[40px] w-[40px] overflow-hidden rounded-full bg-[#190FDB] flex items-center justify-center text-white font-medium"
                                    x-data="{
                                        name: '{{ auth()->user()->name }}',
                                        initials() {
                                            return this.name.split(' ')
                                                .map(part => part.charAt(0))
                                                .slice(0, 2)
                                                .join('')
                                                .toUpperCase();
                                        }
                                    }"
                                    x-text="initials()"
                                    x-on:profile-updated.window="name = $event.detail.name">
                                </div>
                            @endif
                        </div>


                        <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"
                            x-on:profile-updated.window="name = $event.detail.name"></div>
                        <div class="ms-1">
                            <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>
                </x-slot>
                <x-slot name="content">
                    <x-responsive-nav-link href="{{ route('settings.profile') }}" wire:navigate>
                        {{ __('Perfil') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <button wire:click="logout" class="w-full text-start">
                        <x-responsive-nav-link>
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </button>
                </x-slot>
            </x-dropdown>

            <div class="h-10 bg-[#B9B9B9] w-[1px]"></div>

            <div class="flex gap-2 px-3 py-2">
                <button
                    class="h-6 w-6 bg-white border border-[#E4E7EC] rounded-[0.25rem] flex items-center justify-center hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="13" viewBox="0 0 14 13"
                        fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M0 7.23997C0 8.22297 0.713 9.06497 1.69 9.18297C2.59467 9.29164 3.507 9.37264 4.427 9.42597C4.79 9.44597 5.115 9.65697 5.277 9.98197L6.329 12.085C6.39125 12.2096 6.48701 12.3145 6.60553 12.3878C6.72405 12.4611 6.86065 12.4999 7 12.4999C7.13935 12.4999 7.27595 12.4611 7.39447 12.3878C7.51299 12.3145 7.60875 12.2096 7.671 12.085L8.723 9.98197C8.885 9.65697 9.21 9.44697 9.573 9.42597C10.493 9.37264 11.4057 9.29164 12.311 9.18297C13.287 9.06497 14 8.22297 14 7.23997V2.75897C14 1.77697 13.287 0.934969 12.31 0.816969C8.78269 0.393041 5.21731 0.393041 1.69 0.816969C0.712 0.934969 0 1.77697 0 2.75997V7.23997ZM3 3.74997C3 3.55106 3.07902 3.36029 3.21967 3.21964C3.36032 3.07899 3.55109 2.99997 3.75 2.99997H10.25C10.4489 2.99997 10.6397 3.07899 10.7803 3.21964C10.921 3.36029 11 3.55106 11 3.74997C11 3.94888 10.921 4.13965 10.7803 4.2803C10.6397 4.42095 10.4489 4.49997 10.25 4.49997H3.75C3.55109 4.49997 3.36032 4.42095 3.21967 4.2803C3.07902 4.13965 3 3.94888 3 3.74997ZM3.75 5.49997C3.55109 5.49997 3.36032 5.57899 3.21967 5.71964C3.07902 5.86029 3 6.05106 3 6.24997C3 6.44888 3.07902 6.63965 3.21967 6.7803C3.36032 6.92095 3.55109 6.99997 3.75 6.99997H6.25C6.44891 6.99997 6.63968 6.92095 6.78033 6.7803C6.92098 6.63965 7 6.44888 7 6.24997C7 6.05106 6.92098 5.86029 6.78033 5.71964C6.63968 5.57899 6.44891 5.49997 6.25 5.49997H3.75Z"
                            fill="black" />
                    </svg>
                </button>
                <div x-data="{ open: false }" class="relative">
                    <livewire:ui.notifications-dropdown />
                </div>
            </div>
        </div>

        <!-- Hamburger -->
        <div class="flex items-center sm:hidden">
            <button @click="open = ! open"
                class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500">
                <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round"
                        stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                        stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="text-base font-medium text-gray-800" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"
                    x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="text-sm font-medium text-gray-500">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link href="/settings/profile" wire:navigate>
                    {{ __('Perfil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</header>
