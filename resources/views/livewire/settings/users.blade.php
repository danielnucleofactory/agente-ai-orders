<div class="space-y-6 rounded-2xl bg-white p-8">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-[#1AAD8A]">Lista de usuarios</h2>

        <div class="flex space-x-4">
            <a href="{{ route('settings.users.create') }}">
                <x-primary-button class="flex items-center gap-[0.625rem]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M8 1V15M1 8H15" stroke="#F7F7F7" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                    <span>Crear usuario</span>
                </x-primary-button>
            </a>
        </div>
    </div>

    @include('livewire.components.reusable-table')
</div>
