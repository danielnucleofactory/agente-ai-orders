@props(['active' => false, 'route' => ''])

@php
    $path = parse_url($route, PHP_URL_PATH);
@endphp

<div x-data="{
    open: false,
    toggleDropdown() {
        // Verificar si el elemento con clase 'main-sidebar' también tiene 'sidebar-expanded'
        if (document.querySelector('.main-sidebar.sidebar-expanded')) {
            this.open = !this.open;
        } else {
            // Si el sidebar no está expandido, redirigir a /$route
            window.location.href = '{{ $path }}';
            document.querySelector('.main-sidebar').classList.toggle('sidebar-expanded'); localStorage.setItem('sidebarExpanded', document.querySelector('.main-sidebar').classList.contains('sidebar-expanded'));
        }
    },
    init() {
        // Observar cambios en las clases del sidebar
        const sidebarObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'class') {
                    const sidebarElement = document.querySelector('.main-sidebar');
                    // Si el sidebar existe pero ya no tiene la clase 'sidebar-expanded'
                    if (sidebarElement && !sidebarElement.classList.contains('sidebar-expanded')) {
                        // Cerrar el dropdown si está abierto
                        if (this.open) {
                            this.open = false;
                        }
                    }
                }
            });
        });

        // Buscar el elemento del sidebar y comenzar a observarlo
        const sidebar = document.querySelector('.main-sidebar');
        if (sidebar) {
            sidebarObserver.observe(sidebar, { attributes: true });
        }
    }
}" class="w-full overflow-hidden" x-init="init()">
    <button @click="toggleDropdown()"
        class="sidebar-dropdown {{ $active ? 'bg-[#E0E5FF]' : '' }} group flex w-full items-center justify-between rounded-lg px-2 py-1 sm:px-3 sm:py-[0.625rem]">
        <div class="flex items-center sidebar-dropdown-text-container">
            <div class="flex items-center justify-center w-5 h-5">
                {{ $icon }}
            </div>
            <span
                class="{{ $active ? 'text-[#565AFF]' : 'group-hover:text-black' }} sidebar-dropdown-text transition-colors duration-500">
                {{ $title }}
            </span>
        </div>

        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="6" viewBox="0 0 10 6" fill="none"
                class="transition-all duration-100" :class="{ 'transform rotate-180': open }">
                <path
                    d="M8.52868 5.47142C8.78903 5.73177 9.21114 5.73177 9.47149 5.47142C9.73184 5.21108 9.73184 4.78897 9.47149 4.52862L5.47149 0.528616C5.21114 0.268267 4.78903 0.268267 4.52868 0.528616L0.528678 4.52862C0.268328 4.78897 0.268328 5.21108 0.528678 5.47142C0.789027 5.73177 1.21114 5.73177 1.47149 5.47142L5.00008 1.94283L8.52868 5.47142Z"
                    fill="{{ $active ? '#565AFF' : '#898989' }}" />
            </svg>
        </div>
    </button>

    <div x-show="open" x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-2" class="pl-3">
        {{ $slot }}
    </div>
</div>
