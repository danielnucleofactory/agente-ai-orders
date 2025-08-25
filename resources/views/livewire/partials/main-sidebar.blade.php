<sidebar
    class="main-sidebar grid h-full grid-rows-[auto_1fr_auto] rounded-br-[1.25rem] rounded-tr-[1.25rem] bg-white font-inter text-[0.875rem] transition-none">
    <div class="relative flex items-center justify-center border-b border-[#D2D2D2] bg-[#f7f7f7] px-6 py-6">
        <a href="{{ route('dashboard') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="32" viewBox="0 0 28 32" fill="none">
                <path
                    d="M25.7333 26.863C26.2287 26.3401 26.6871 25.7854 27.1085 25.2029C26.0507 21.9229 24.2271 18.9064 21.7187 16.3642C24.2784 13.6085 26.1205 10.3743 27.1683 6.87932C26.744 6.28435 26.2799 5.7185 25.7803 5.18732C24.9033 8.85286 23.0996 12.248 20.4802 15.1008C16.6664 10.9457 14.578 5.64083 14.5723 0.0239373C14.0427 -0.00380042 13.506 -0.00796108 12.965 0.0142291C12.8953 0.0170029 12.8269 0.0239373 12.7586 0.028098C12.7515 5.6436 10.6645 10.9471 6.85065 15.1008C4.23408 12.2535 2.4318 8.86534 1.55486 5.20673C1.05375 5.74069 0.592507 6.30792 0.168274 6.9029C1.21747 10.3895 3.05818 13.6154 5.6107 16.3642C3.10801 18.8995 1.28865 21.909 0.229489 25.1793C0.649451 25.7632 1.10785 26.3179 1.60326 26.8408C2.49728 23.4069 4.28248 20.2434 6.85065 17.6235C10.6588 21.5096 12.7529 26.5842 12.7586 31.975C13.2967 32.0042 13.842 32.0083 14.3915 31.9847C14.4527 31.982 14.5125 31.9764 14.5737 31.9723C14.5808 26.5828 16.6735 21.5082 20.4816 17.6221C23.0555 20.2475 24.8421 23.4193 25.7347 26.8616L25.7333 26.863ZM13.664 25.7951C12.6404 22.2862 10.7456 19.059 8.08776 16.3642C10.7485 13.4989 12.6419 10.1163 13.664 6.46048C14.6861 10.1163 16.5795 13.4989 19.2417 16.3642C16.5824 19.059 14.689 22.2862 13.664 25.7951Z"
                    fill="url(#paint0_linear_5007_7951)" />
                <defs>
                    <linearGradient id="paint0_linear_5007_7951" x1="-3.43913" y1="32.6782" x2="26.6876"
                        y2="1.75253" gradientUnits="userSpaceOnUse">
                        <stop stop-color="white" />
                        <stop offset="0.01" stop-color="#F2FDFE" />
                        <stop offset="0.09" stop-color="#A0F1F8" />
                        <stop offset="0.15" stop-color="#6DE9F5" />
                        <stop offset="0.17" stop-color="#5AE7F4" />
                        <stop offset="0.2" stop-color="#5AE1F4" />
                        <stop offset="0.23" stop-color="#5BD0F5" />
                        <stop offset="0.27" stop-color="#5DB5F8" />
                        <stop offset="0.31" stop-color="#6090FB" />
                        <stop offset="0.35" stop-color="#6367FF" />
                        <stop offset="0.4" stop-color="#5556F8" />
                        <stop offset="0.49" stop-color="#312BE6" />
                        <stop offset="0.55" stop-color="#190FDB" />
                        <stop offset="0.62" stop-color="#4922B9" />
                        <stop offset="0.72" stop-color="#853B90" />
                        <stop offset="0.81" stop-color="#B54E6F" />
                        <stop offset="0.89" stop-color="#D75C57" />
                        <stop offset="0.95" stop-color="#EC6449" />
                        <stop offset="1" stop-color="#F46844" />
                    </linearGradient>
                </defs>
            </svg>
        </a>

        <button
            class="sidebar-toggler-btn absolute -right-[calc(28px/2)] flex h-7 w-7 items-center justify-center rounded-full bg-[#190FDB]"
            onclick="document.querySelector('.main-sidebar').classList.toggle('sidebar-expanded'); localStorage.setItem('sidebarExpanded', document.querySelector('.main-sidebar').classList.contains('sidebar-expanded'));">
            <svg xmlns="http://www.w3.org/2000/svg" width="8" height="14" viewBox="0 0 8 14" fill="none">
                <path d="M7 1L1 7L7 13" stroke="#F7F7F7" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </button>
    </div>

    <nav class="flex w-full flex-col items-center space-y-2 p-6 text-[#898989]">
        <a href="{{ route('settings.profile') }}" class="flex items-center mb-10 overflow-hidden profile-container">
            <div class="avatar-container h-[2.625rem] w-[2.625rem] overflow-hidden rounded-full bg-[#190FDB] flex items-center justify-center text-white font-medium"
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

            <div class="profile-name font-inter text-[#2E2E2E]">
                <span class="text-sm">Hola 👋</span>
                <div class="text-2xl" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"
                    x-on:profile-updated.window="name = $event.detail.name"></div>
            </div>
        </a>

        <span class="text-[0.625rem] font-medium uppercase hidden">Main</span>

        <ul class="w-full space-y-2">
            @can('has_view_dashboard')
            <li>
                <x-sidebar-link href="{{ route('dashboard') }}"
                    class="{{ request()->routeIs('dashboard') ? 'bg-[#E0E5FF]' : '' }}">
                    <div class="flex items-center justify-center w-5 h-5" onclick="document.querySelector('.main-sidebar').classList.toggle('sidebar-expanded'); localStorage.setItem('sidebarExpanded', document.querySelector('.main-sidebar').classList.contains('sidebar-expanded'));">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 18 17"
                            fill="none">
                            <path
                                class="{{ request()->routeIs('dashboard') ? 'fill-[#565AFF]' : 'group-hover:fill-black fill-[#898989]' }} transition-colors duration-500"
                                fill-rule="evenodd" clip-rule="evenodd"
                                d="M9.00008 0.231445C8.2387 0.231445 7.49186 0.440069 6.84071 0.834657L2.67404 3.35966C2.06132 3.73095 1.55468 4.25397 1.20307 4.87819C0.851456 5.50241 0.666736 6.20675 0.666748 6.92318V12.1665C0.666748 13.2716 1.10573 14.3314 1.88714 15.1128C2.66854 15.8942 3.72835 16.3332 4.83341 16.3332H13.1667C14.2718 16.3332 15.3316 15.8942 16.113 15.1128C16.8944 14.3314 17.3334 13.2716 17.3334 12.1665V6.92235C17.3332 6.20608 17.1484 5.50176 16.7968 4.87774C16.4452 4.25372 15.9387 3.73087 15.3261 3.35966L11.1595 0.834664C10.5083 0.440076 9.76147 0.231445 9.00008 0.231445ZM7.70447 2.26003C8.09516 2.02328 8.54325 1.89811 9.00008 1.89811C9.45691 1.89811 9.905 2.02328 10.2957 2.26003L14.4624 4.78503C14.8299 5.00776 15.1338 5.32147 15.3448 5.69589C15.5557 6.07025 15.6666 6.49266 15.6667 6.92235V12.1665C15.6667 12.8296 15.4034 13.4654 14.9345 13.9343C14.4657 14.4031 13.8298 14.6665 13.1667 14.6665H12.3334V12.1665C12.3334 11.2825 11.9822 10.4346 11.3571 9.80949C10.732 9.18437 9.88413 8.83318 9.00008 8.83318C8.11603 8.83318 7.26818 9.18437 6.64306 9.80949C6.01794 10.4346 5.66675 11.2825 5.66675 12.1665V14.6665H4.83341C4.17037 14.6665 3.53449 14.4031 3.06565 13.9343C2.59681 13.4654 2.33341 12.8296 2.33341 12.1665V6.92318C2.33341 6.49332 2.44424 6.07069 2.65521 5.69616C2.86618 5.32163 3.17016 5.00782 3.53779 4.78504L7.70447 2.26003ZM10.1786 10.988C10.4912 11.3006 10.6667 11.7245 10.6667 12.1665V14.6665H7.33341V12.1665C7.33341 11.7245 7.50901 11.3006 7.82157 10.988C8.13413 10.6754 8.55805 10.4999 9.00008 10.4999C9.44211 10.4999 9.86603 10.6754 10.1786 10.988Z" />
                        </svg>
                    </div>

                    <div
                        class="link-text {{ request()->routeIs('dashboard') ? 'text-[#565AFF]' : 'group-hover:text-black' }} transition-colors duration-500">
                        <span>Inicio</span>
                    </div>
                </x-sidebar-link>
            </li>
            @endcan

            @if(auth()->user()->can('has_view_orders') || auth()->user()->can('has_create_orders') || auth()->user()->can('has_view_products') || auth()->user()->can('has_view_forecast'))
            <li>
                <x-sidebar-dropdown active="{{ request()->is('purchase-orders') || request()->is('purchase-orders/*') }}" route="{{ route('purchase-orders.index') }}">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="18" viewBox="0 0 16 20 "
                            fill="none">
                            <path
                                d="M4.66667 7.49998C4.20643 7.49998 3.83333 7.87308 3.83333 8.33331C3.83333 8.79355 4.20643 9.16665 4.66667 9.16665H11.3333C11.7936 9.16665 12.1667 8.79355 12.1667 8.33331C12.1667 7.87308 11.7936 7.49998 11.3333 7.49998H4.66667Z"
                                class="{{ request()->is('purchase-orders') || request()->is('purchase-orders/*') ? 'fill-[#565AFF]' : 'group-hover:fill-black fill-[#898989]' }} transition-colors duration-500" />
                            <path
                                d="M4.66667 10.8333C4.20643 10.8333 3.83333 11.2064 3.83333 11.6666C3.83333 12.1269 4.20643 12.5 4.66667 12.5H8C8.46024 12.5 8.83333 12.1269 8.83333 11.6666C8.83333 11.2064 8.46024 10.8333 8 10.8333H4.66667Z"
                                class="{{ request()->is('purchase-orders') || request()->is('purchase-orders/*') ? 'fill-[#565AFF]' : 'group-hover:fill-black fill-[#898989]' }} transition-colors duration-500" />
                            <path
                                d="M4.66667 14.1666C4.20643 14.1666 3.83333 14.5397 3.83333 15C3.83333 15.4602 4.20643 15.8333 4.66667 15.8333H11.3333C11.7936 15.8333 12.1667 15.4602 12.1667 15C12.1667 14.5397 11.7936 14.1666 11.3333 14.1666H4.66667Z"
                                class="{{ request()->is('purchase-orders') || request()->is('purchase-orders/*') ? 'fill-[#565AFF]' : 'group-hover:fill-black fill-[#898989]' }} transition-colors duration-500" />
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M15.399 4.2813C15.3319 4.11899 15.2335 3.97153 15.1093 3.84739L12.4863 1.22442C12.2365 0.974305 11.8976 0.833625 11.5441 0.833313H1.83333C1.47971 0.833313 1.14057 0.973789 0.890524 1.22384C0.640476 1.47389 0.5 1.81302 0.5 2.16665V17.8333C0.5 18.0084 0.534488 18.1818 0.601494 18.3436C0.668499 18.5053 0.766711 18.6523 0.890524 18.7761C1.01433 18.8999 1.16132 18.9981 1.32309 19.0652C1.48486 19.1322 1.65824 19.1666 1.83333 19.1666H14.1667C14.3418 19.1666 14.5151 19.1322 14.6769 19.0652C14.8387 18.9981 14.9857 18.8999 15.1095 18.7761C15.2333 18.6523 15.3315 18.5053 15.3985 18.3436C15.4655 18.1818 15.5 18.0084 15.5 17.8333V4.7919L14.6667 4.79165L15.5 4.79369L15.5 4.7919C15.5002 4.61672 15.4659 4.44321 15.399 4.2813ZM10.5 4.58331C10.5 5.27367 11.0596 5.83331 11.75 5.83331H13.8333V17.5H2.16667V2.49998H10.5V4.58331ZM13.0715 4.16665L12.1667 3.26182V4.16665H13.0715Z"
                                class="{{ request()->is('purchase-orders') || request()->is('purchase-orders/*') ? 'fill-[#565AFF]' : 'group-hover:fill-black fill-[#898989]' }} transition-colors duration-500" />
                        </svg>
                    </x-slot:icon>

                    <x-slot:title>Operaciones</x-slot:title>

                    <ul
                        class="{{ request()->is('purchase-orders') || request()->is('purchase-orders/*') ? 'border-[#565AFF]' : 'border-gray-200' }} ml-[0.625rem] mt-2 flex flex-col space-y-1 border-l-2 pl-2.5">
                        @can('has_create_orders')
                        <li>
                            <x-sidebar-dropdown-item href="{{ route('purchase-orders.create') }}" :active="request()->routeIs('purchase-orders.create')">
                                Generar Órdenes
                            </x-sidebar-dropdown-item>
                        </li>
                        @endcan

                        @can('has_view_orders')
                        <li>
                            <x-sidebar-dropdown-item href="{{ route('purchase-orders.index') }}" :active="request()->routeIs('purchase-orders.index')">
                                Seguimiento Órdenes
                            </x-sidebar-dropdown-item>
                        </li>
                        @endcan

                        @can('has_view_consolidated_orders')
                        <li>
                            <x-sidebar-dropdown-item href="{{ route('shipping-documentation.index') }}"
                                :active="request()->routeIs('shipping-documentation.index')">
                                Órdenes Consolidadas
                            </x-sidebar-dropdown-item>
                        </li>
                        @endcan

                        @can('has_view_forecast_graph')
                        <li>
                            <x-sidebar-dropdown-item href="{{ route('products.forecast-graph') }}"
                                :active="request()->routeIs('products.forecast-graph')">
                                Forecast de materiales
                            </x-sidebar-dropdown-item>
                        </li>
                        @endcan

                        @can('has_view_forecast_table')
                        <li>
                            <x-sidebar-dropdown-item href="{{ route('products.forecast') }}"
                                :active="request()->routeIs('products.forecast')">
                                Tabla de forecast
                            </x-sidebar-dropdown-item>
                        </li>
                        @endcan

                        @can('has_view_products')
                        <li>
                            <x-sidebar-dropdown-item href="{{ route('products.index') }}"
                                :active="request()->routeIs('products.index')">
                                Productos
                            </x-sidebar-dropdown-item>
                        </li>
                        @endcan

                        @can('has_view_requests')
                        <li>
                            <x-sidebar-dropdown-item href="{{ route('purchase-orders.requests') }}"
                                :active="request()->routeIs('purchase-orders.requests')">
                                Solicitudes y aprobaciones
                            </x-sidebar-dropdown-item>
                        </li>
                        @endcan
                    </ul>
                </x-sidebar-dropdown>
            </li>
            @endif
            <li>
                <x-sidebar-dropdown active="{{ request()->is('settings') || request()->is('settings/*') }}" route="{{ route('settings.index') }}">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20"
                            fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M9.99992 6.66665C9.11586 6.66665 8.26802 7.01783 7.6429 7.64296C7.01777 8.26808 6.66658 9.11592 6.66658 9.99998C6.66658 10.884 7.01777 11.7319 7.6429 12.357C8.26802 12.9821 9.11586 13.3333 9.99992 13.3333C10.884 13.3333 11.7318 12.9821 12.3569 12.357C12.9821 11.7319 13.3333 10.884 13.3333 9.99998C13.3333 9.11592 12.9821 8.26808 12.3569 7.64296C11.7318 7.01783 10.884 6.66665 9.99992 6.66665ZM8.82141 8.82147C9.13397 8.50891 9.55789 8.33331 9.99992 8.33331C10.4419 8.33331 10.8659 8.50891 11.1784 8.82147C11.491 9.13403 11.6666 9.55795 11.6666 9.99998C11.6666 10.442 11.491 10.8659 11.1784 11.1785C10.8659 11.4911 10.4419 11.6666 9.99992 11.6666C9.55789 11.6666 9.13397 11.4911 8.82141 11.1785C8.50885 10.8659 8.33325 10.442 8.33325 9.99998C8.33325 9.55795 8.50885 9.13403 8.82141 8.82147Z"
                                class="{{ request()->is('settings') || request()->is('settings/*') ? 'fill-[#565AFF]' : 'group-hover:fill-black fill-[#898989]' }} transition-colors duration-500" />
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M9.15075 0.833313C8.77221 0.833313 8.44123 1.08845 8.34487 1.45452L7.92257 3.05885L6.58087 3.62445L5.55383 2.71071C5.22407 2.41734 4.72276 2.43196 4.41066 2.74406L2.744 4.41072C2.44208 4.71264 2.4172 5.19392 2.68639 5.52535L3.5865 6.63361L3.03453 7.99154L1.47713 8.35513C1.10002 8.44317 0.833252 8.77939 0.833252 9.16665V10.8333C0.833252 11.2091 1.08474 11.5384 1.44726 11.6373L3.057 12.0764L3.62412 13.4217L2.7113 14.4453C2.41729 14.775 2.43163 15.2769 2.744 15.5892L4.41066 17.2559C4.71299 17.5582 5.19506 17.5827 5.52646 17.3126L6.63432 16.4094L7.95888 16.9543L8.35861 18.5373C8.45205 18.9074 8.78493 19.1666 9.16658 19.1666H10.8333C11.2148 19.1666 11.5475 18.9076 11.6411 18.5377L12.0419 16.954L13.4107 16.3871C13.5485 16.5026 13.7065 16.6382 13.8637 16.7751C14.0199 16.9111 14.1651 17.0393 14.2714 17.1336C14.3244 17.1807 14.3677 17.2192 14.3975 17.2459L14.4428 17.2864C14.7723 17.582 15.2761 17.5689 15.5892 17.2559L17.2558 15.5892C17.5625 15.2825 17.5827 14.7918 17.3022 14.461L16.3944 13.3905L16.9561 12.0338L18.5428 11.6215C18.9102 11.5261 19.1666 11.1945 19.1666 10.815V9.16665C19.1666 8.78555 18.9081 8.453 18.5387 8.35904L16.9618 7.95785L16.4015 6.60443L17.303 5.53794C17.5827 5.20706 17.5622 4.71707 17.2558 4.41072L15.5892 2.74406C15.2816 2.43646 14.7891 2.41721 14.4585 2.69985L13.3998 3.60471L11.9988 3.02864L11.585 1.45479C11.4888 1.08859 11.1577 0.833313 10.7791 0.833313H9.15075ZM15.5346 14.9535L14.9644 15.5236L14.9582 15.5182C14.6504 15.2502 14.254 14.9111 14.0304 14.7512C13.7964 14.5839 13.4926 14.5492 13.2269 14.6592L11.0177 15.5742C10.7746 15.6749 10.5933 15.8846 10.5287 16.1397L10.1845 17.5H9.81565L9.47206 16.1393C9.40742 15.8833 9.22528 15.6731 8.98111 15.5726L6.80944 14.6793C6.52666 14.563 6.20288 14.6109 5.96588 14.8041L5.05687 15.5451L4.47901 14.9672L5.21854 14.1379C5.43263 13.8979 5.48943 13.556 5.36448 13.2596L4.43531 11.0554C4.3359 10.8196 4.13364 10.6425 3.88674 10.5752L2.49992 10.1969V9.82784L3.83354 9.51649C4.097 9.45498 4.31421 9.26941 4.41608 9.01877L5.31608 6.80461C5.43055 6.523 5.38259 6.2014 5.19095 5.96544L4.45385 5.05789L5.03332 4.47842L5.86601 5.21924C6.10607 5.43282 6.44754 5.48935 6.74362 5.36454L8.94779 4.43537C9.18602 4.33494 9.36416 4.12963 9.42997 3.87961L9.79312 2.49998H10.1365L10.4923 3.85351C10.5587 4.10615 10.7398 4.31304 10.9814 4.41238L13.2372 5.33988C13.5264 5.45879 13.8578 5.40578 14.0955 5.20261L14.9556 4.46748L15.5354 5.04732L14.801 5.91619C14.5997 6.15429 14.5482 6.48482 14.6674 6.77288L15.5816 8.98122C15.6822 9.22408 15.8914 9.40528 16.1461 9.47009L17.4999 9.81451V10.1705L16.1353 10.5251C15.8824 10.5908 15.6749 10.7714 15.575 11.0129L14.66 13.2229C14.5405 13.5114 14.5924 13.8425 14.7943 14.0806L15.5346 14.9535Z"
                                class="{{ request()->is('settings') || request()->is('settings/*') ? 'fill-[#565AFF]' : 'group-hover:fill-black fill-[#898989]' }} transition-colors duration-500" />
                        </svg>
                    </x-slot:icon>

                    <x-slot:title>Configuraciones</x-slot:title>

                    <ul
                        class="{{ request()->is('settings') || request()->is('settings/*') ? 'border-[#565AFF]' : 'border-gray-200' }} ml-[0.625rem] mt-2 flex flex-col space-y-1 border-l-2 pl-2.5">
                        <li>
                            <x-sidebar-dropdown-item href="{{ route('settings.index') }}" :active="request()->routeIs('settings.index')">
                                Generales
                            </x-sidebar-dropdown-item>
                        </li>

                        <li>
                            <x-sidebar-dropdown-item href="{{ route('settings.notifications') }}" :active="request()->routeIs('settings.notifications')">
                                Notificaciones
                            </x-sidebar-dropdown-item>
                        </li>

                        <li>
                            <x-sidebar-dropdown-item href="{{ route('settings.password') }}" :active="request()->routeIs('settings.password')">
                                Cambiar Contraseña
                            </x-sidebar-dropdown-item>
                        </li>

                        <li>
                            <x-sidebar-dropdown-item href="{{ route('settings.stages') }}" :active="request()->routeIs('settings.stages')">
                                Etapas
                            </x-sidebar-dropdown-item>
                        </li>

                        <li>
                            <x-sidebar-dropdown-item href="{{ route('vendors.index') }}"
                                :active="request()->routeIs('vendors.index')">
                                Proveedores
                            </x-sidebar-dropdown-item>
                        </li>

                        <li>
                            <x-sidebar-dropdown-item href="{{ route('ship-to.index') }}"
                                :active="request()->routeIs('ship-to.index')">
                                Direcciones
                            </x-sidebar-dropdown-item>
                        </li>

                        <li>
                            <x-sidebar-dropdown-item href="{{ route('hub.index') }}"
                                :active="request()->routeIs('hub.index')">
                                Hubs
                            </x-sidebar-dropdown-item>
                        </li>

                        <li>
                            <x-sidebar-dropdown-item href="{{ route('settings.users') }}" :active="request()->routeIs('settings.users')">
                                Usuarios
                            </x-sidebar-dropdown-item>
                        </li>

                        <li>
                            <x-sidebar-dropdown-item href="{{ route('settings.roles') }}" :active="request()->routeIs('settings.roles')">
                                Roles
                            </x-sidebar-dropdown-item>
                        </li>

                        <li>
                            <x-sidebar-dropdown-item href="{{ route('settings.sessions') }}" :active="request()->routeIs('settings.sessions')">
                                Sesiones activas
                            </x-sidebar-dropdown-item>
                        </li>

                        <li>
                            <x-sidebar-dropdown-item href="{{ route('settings.history') }}" :active="request()->routeIs('settings.history')">
                                Log Histórico
                            </x-sidebar-dropdown-item>
                        </li>

                        <li>
                            <x-sidebar-dropdown-item href="{{ route('settings.api-tokens') }}" :active="request()->routeIs('settings.api-tokens')">
                                Tokens API
                            </x-sidebar-dropdown-item>
                        </li>

                        @if(config('po-confirmation.enabled', false))
                        <li>
                            <x-sidebar-dropdown-item href="{{ route('settings.po-confirmation') }}" :active="request()->routeIs('settings.po-confirmation')">
                                Confirmación PO
                            </x-sidebar-dropdown-item>
                        </li>
                        @endif
                    </ul>
                </x-sidebar-dropdown>
            </li>

            @can('has_view_support')
            <li>
                <x-sidebar-dropdown active="{{ request()->is('support') || request()->is('support/*') }}" route="{{ route('support.index') }}">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="18" viewBox="0 0 20 18"
                            fill="none">
                            <path
                                d="M9.38475 17.5V16H16.6923C16.7756 16 16.8477 15.9743 16.9088 15.923C16.9696 15.8718 17 15.8046 17 15.7212V8.727C17 6.81533 16.316 5.206 14.948 3.899C13.5802 2.592 11.9308 1.9385 10 1.9385C8.06917 1.9385 6.41983 2.592 5.052 3.899C3.684 5.206 3 6.81533 3 8.727V14.6152H2.25C1.7705 14.6152 1.359 14.4483 1.0155 14.1145C0.671833 13.7805 0.5 13.3737 0.5 12.8942V10.952C0.5 10.6277 0.592333 10.3305 0.777 10.0605C0.961667 9.79067 1.20267 9.57375 1.5 9.40975L1.54625 8.13275C1.62825 7.04425 1.91092 6.03592 2.39425 5.10775C2.87758 4.17958 3.49742 3.37158 4.25375 2.68375C5.01025 1.99592 5.88333 1.46 6.873 1.076C7.86283 0.691999 8.90517 0.5 10 0.5C11.0948 0.5 12.1346 0.691999 13.1193 1.076C14.1039 1.46 14.977 1.99333 15.7385 2.676C16.5 3.35867 17.1198 4.16408 17.598 5.09225C18.0762 6.02058 18.3614 7.02892 18.4538 8.11725L18.5 9.36925C18.791 9.50642 19.0304 9.70258 19.2183 9.95775C19.4061 10.2129 19.5 10.4975 19.5 10.8115V13.0442C19.5 13.3582 19.4061 13.6428 19.2183 13.898C19.0304 14.1532 18.791 14.3493 18.5 14.4865V15.7212C18.5 16.2134 18.3234 16.6329 17.9703 16.9797C17.6169 17.3266 17.1909 17.5 16.6923 17.5H9.38475ZM7.19225 10.7692C6.94742 10.7692 6.73875 10.6863 6.56625 10.5203C6.39392 10.3542 6.30775 10.1488 6.30775 9.90375C6.30775 9.65892 6.39392 9.45192 6.56625 9.28275C6.73875 9.11342 6.94742 9.02875 7.19225 9.02875C7.43725 9.02875 7.64592 9.11342 7.81825 9.28275C7.99075 9.45192 8.077 9.65892 8.077 9.90375C8.077 10.1488 7.99075 10.3542 7.81825 10.5203C7.64592 10.6863 7.43725 10.7692 7.19225 10.7692ZM12.8077 10.7692C12.5627 10.7692 12.3541 10.6863 12.1818 10.5203C12.0093 10.3542 11.923 10.1488 11.923 9.90375C11.923 9.65892 12.0093 9.45192 12.1818 9.28275C12.3541 9.11342 12.5627 9.02875 12.8077 9.02875C13.0526 9.02875 13.2612 9.11342 13.4337 9.28275C13.6061 9.45192 13.6923 9.65892 13.6923 9.90375C13.6923 10.1488 13.6061 10.3542 13.4337 10.5203C13.2612 10.6863 13.0526 10.7692 12.8077 10.7692ZM4.37125 9.2C4.26742 7.568 4.77508 6.1715 5.89425 5.0105C7.01342 3.84967 8.39867 3.26925 10.05 3.26925C11.4372 3.26925 12.6612 3.69842 13.722 4.55675C14.783 5.41508 15.4269 6.52625 15.6538 7.89025C14.2333 7.87358 12.9208 7.50058 11.7163 6.77125C10.5118 6.04175 9.58708 5.04175 8.94225 3.77125C8.68842 5.01475 8.15642 6.11408 7.34625 7.06925C6.53592 8.02442 5.54425 8.73467 4.37125 9.2Z"
                                class="{{ request()->is('support') || request()->is('support/*') ? 'fill-[#565AFF]' : 'group-hover:fill-black fill-[#898989]' }} transition-colors duration-500" />
                        </svg>
                    </x-slot:icon>

                    <x-slot:title>Soporte</x-slot:title>

                    <ul
                        class="{{ request()->is('support') || request()->is('support/*') ? 'border-[#565AFF]' : 'border-gray-200' }} ml-[0.625rem] mt-2 flex flex-col space-y-1 border-l-2 pl-2.5">
                        <li>
                            <x-sidebar-dropdown-item href="{{ route('support.index') }}" :active="request()->routeIs('support.index')">
                                FAQS
                            </x-sidebar-dropdown-item>
                        </li>
                    </ul>
                </x-sidebar-dropdown>
            </li>
            @endcan
        </ul>
    </nav>

    <div class="flex items-center justify-center border-t border-[#D2D2D2] bg-[#f7f7f7] px-6 py-6">
        <form method="POST" action="{{ route('logout-session') }}" class="w-full">
            @csrf
            <button type="submit"
                class="logout-btn group flex w-full items-center justify-center rounded-[0.375rem] bg-[#565AFF] px-3 py-[0.625rem] text-white transition-colors duration-500 hover:bg-[#7477ff]">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="18" viewBox="0 0 14 18"
                    fill="none">
                    <path
                        d="M2.83325 0.666626C2.17021 0.666626 1.53433 0.930018 1.06548 1.39886C0.596644 1.8677 0.333252 2.50358 0.333252 3.16663V14.8333C0.333252 15.4963 0.596644 16.1322 1.06548 16.6011C1.53433 17.0699 2.17021 17.3333 2.83325 17.3333H11.1666C11.8296 17.3333 12.4655 17.0699 12.9344 16.6011C13.4032 16.1322 13.6666 15.4963 13.6666 14.8333V14C13.6666 13.5397 13.2935 13.1666 12.8333 13.1666C12.373 13.1666 11.9999 13.5397 11.9999 14V14.8333C11.9999 15.0543 11.9121 15.2663 11.7558 15.4225C11.5996 15.5788 11.3876 15.6666 11.1666 15.6666H2.83325C2.61224 15.6666 2.40028 15.5788 2.244 15.4225C2.08772 15.2663 1.99992 15.0543 1.99992 14.8333V3.16663C1.99992 2.94561 2.08772 2.73365 2.244 2.57737C2.40028 2.42109 2.61224 2.33329 2.83325 2.33329H11.1666C11.3876 2.33329 11.5996 2.42109 11.7558 2.57737C11.9121 2.73365 11.9999 2.94561 11.9999 3.16663V3.99996C11.9999 4.4602 12.373 4.83329 12.8333 4.83329C13.2935 4.83329 13.6666 4.4602 13.6666 3.99996V3.16663C13.6666 2.50358 13.4032 1.8677 12.9344 1.39886C12.4655 0.930018 11.8296 0.666626 11.1666 0.666626H2.83325Z"
                        class="transition-colors duration-500 fill-white group-hover:fill-slate-300" />
                    <path
                        d="M10.9225 5.9107C10.5971 5.58527 10.0694 5.58527 9.744 5.9107C9.41856 6.23614 9.41856 6.76378 9.744 7.08921L10.8214 8.16663H6.99992C6.53968 8.16663 6.16658 8.53972 6.16658 8.99996C6.16658 9.4602 6.53968 9.83329 6.99992 9.83329H10.8214L9.744 10.9107C9.41856 11.2361 9.41856 11.7638 9.744 12.0892C10.0694 12.4147 10.5971 12.4147 10.9225 12.0892L13.4225 9.58921C13.7479 9.26378 13.7479 8.73614 13.4225 8.4107L10.9225 5.9107Z"
                        class="transition-colors duration-500 fill-white group-hover:fill-slate-300" />
                </svg>

                <div class="font-sans text-lg font-extrabold link-text group-hover:text-slate-300">
                    <span class="transition-colors duration-500">Cerrar sesión</span>
                </div>
            </button>
        </form>
    </div>
</sidebar>
