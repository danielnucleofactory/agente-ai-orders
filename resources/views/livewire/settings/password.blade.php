<form wire:submit="updatePassword" class="px-6 py-4 space-y-6 bg-white rounded-2xl">
    <div class="space-y-2">
        <label for="current_password" class="flex flex-col">
            <span class="text-lg font-bold text-[#7288FF]">Contraseña Actual</span>
        </label>
        <input wire:model="current_password" id="current_password" type="password" class="text-lg font-bold text-[#7288FF] rounded-xl w-full focus:border-2 focus:border-[#7288FF] transition-all duration-300 border-2 border-[#D2D2D2]">
        @error('current_password') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
    </div>

    <div class="space-y-2">
        <label for="password" class="flex flex-col">
            <span class="text-lg font-bold text-[#7288FF] mb-2">
                Nueva Contraseña
            </span>

            <span class="text-sm text-[#C2C2C2]">
                Debe contener al menos 8 caracteres, incluyendo una letra mayúscula y un carácter especial
            </span>
        </label>
        <input wire:model="password" id="password" type="password" class="text-lg font-bold text-[#7288FF] rounded-xl w-full focus:border-2 focus:border-[#7288FF] transition-all duration-300 border-2 border-[#D2D2D2]">
        @error('password') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
    </div>

    <div class="space-y-2">
        <label for="password_confirmation" class="flex flex-col">
            <span class="text-lg font-bold text-[#7288FF] mb-2">
                Confirmar Nueva Contraseña
            </span>
        </label>
        <input wire:model="password_confirmation" id="password_confirmation" type="password" class="text-lg font-bold text-[#7288FF] rounded-xl w-full focus:border-2 focus:border-[#7288FF] transition-all duration-300 border-2 border-[#D2D2D2]">
    </div>

    @if (session()->has('message'))
        <div class="p-2 text-sm text-green-600 bg-green-100 rounded">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-2 text-sm text-red-600 bg-red-100 rounded">
            {{ session('error') }}
        </div>
    @endif

    <button type="submit"
        class="w-full rounded-[0.375rem] bg-[#565AFF] px-3 py-[0.625rem] text-center font-sans text-lg font-extrabold text-white transition-colors duration-500 hover:bg-[#7477ff] hover:text-slate-300 active:scale-[0.99]">
        Guardar
    </button>
</form>
