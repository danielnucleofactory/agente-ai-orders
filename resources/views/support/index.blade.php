<x-app-layout>
    <x-view-title>
        <x-slot:title>
            Soporte
        </x-slot:title>

        <x-slot:content>
            Visualiza y administra los productos
        </x-slot:content>
    </x-view-title>

    <div class="space-y-11">
        <div class="flex gap-4 items-center">
            <x-search-input class="w-[760px]" id="support-search" />

            <x-primary-button class="group" onclick="handleSearch()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path
                        d="M19 19L15.5001 15.5M18 9.5C18 14.1944 14.1944 18 9.5 18C4.80558 18 1 14.1944 1 9.5C1 4.80558 4.80558 1 9.5 1C14.1944 1 18 4.80558 18 9.5Z"
                        stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-disabled:stroke-[#C2C2C2]"/>
                </svg>
            </x-primary-button>

            <a href="{{ route('support.contact') }}" class="px-6 py-2 text-white bg-[#1AAD8A] rounded-lg hover:bg-[#0F614D] transition">
                Contactar Soporte
            </a>
        </div>

        <ul class="grid grid-cols-4 gap-x-6 gap-y-8" id="support-cards">
            <li>
                <x-card-icon class="w-full !flex-col shadow-2xl">
                    <x-slot:icon
                        class="flex items-center self-start justify-center rounded-full h-14 w-14 bg-neutral-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="11" fill="#28C7A1" stroke="none"/>
                            <path d="M6 9L12 13L18 9M6 9V15C6 15.5523 6.44772 16 7 16H17C17.5523 16 18 15.5523 18 15V9M6 9L12 5L18 9" 
                                stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </x-slot:icon>
                    <x-slot:title class="text-lg font-bold text-neutral-blue">
                        ¿Cómo cambio el correo electrónico de mi cuenta?
                    </x-slot:title>
                    <x-slot:content class="text-sm text-[#666666]">
                        Puede iniciar sesión en su cuenta y cambiarla desde su Perfil > Editar perfil. A continuación,
                        ve a
                        la pestaña general para cambiar tu correo electrónico.
                    </x-slot:content>
                </x-card-icon>
            </li>
            <li>
                <x-card-icon class="w-full !flex-col shadow-2xl">
                    <x-slot:icon
                        class="flex items-center self-start justify-center rounded-full h-14 w-14 bg-neutral-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="11" fill="#28C7A1" stroke="none"/>
                            <circle cx="12" cy="12" r="7" stroke="white" stroke-width="1.5"/>
                            <path d="M7 7L17 17" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </x-slot:icon>
                    <x-slot:title class="text-lg font-bold text-neutral-blue">
                        ¿Qué debo hacer si mi pago falla?
                    </x-slot:title>
                    <x-slot:content class="text-sm text-[#666666]">
                        Si se produce un error en el pago, puede utilizar la opción de pago (contra reembolso), si está
                        disponible en ese pedido. Si su pago se debita de su cuenta después de un error de pago, se
                        devolverá en un plazo de 7 a 10 días.
                    </x-slot:content>
                </x-card-icon>
            </li>
            <li>
                <x-card-icon class="w-full !flex-col shadow-2xl">
                    <x-slot:icon
                        class="flex items-center self-start justify-center rounded-full h-14 w-14 bg-neutral-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="11" fill="#28C7A1" stroke="none"/>
                            <rect x="8" y="7" width="8" height="10" rx="1" stroke="white" stroke-width="1.5"/>
                            <path d="M8 10H16" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </x-slot:icon>
                    <x-slot:title class="text-lg font-bold text-neutral-blue">
                        ¿Cuál es su política de cancelación?
                    </x-slot:title>
                    <x-slot:content class="text-sm text-[#666666]">
                        Ahora puede cancelar un pedido cuando está en estado empaquetado/enviado. Cualquier monto pagado
                        se
                        acreditará en el mismo modo de pago utilizando el cual se realizó el pago
                    </x-slot:content>
                </x-card-icon>
            </li>
            <li>
                <x-card-icon class="w-full !flex-col shadow-2xl">
                    <x-slot:icon
                        class="flex items-center self-start justify-center rounded-full h-14 w-14 bg-neutral-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="11" fill="#28C7A1" stroke="none"/>
                            <path d="M5 10H13V14H5V10Z" stroke="white" stroke-width="1.5" stroke-linejoin="round"/>
                            <path d="M13 11H16L18 13V14H13V11Z" stroke="white" stroke-width="1.5" stroke-linejoin="round"/>
                            <circle cx="8" cy="16" r="1.5" stroke="white" stroke-width="1.5"/>
                            <circle cx="15" cy="16" r="1.5" stroke="white" stroke-width="1.5"/>
                        </svg>
                    </x-slot:icon>
                    <x-slot:title class="text-lg font-bold text-neutral-blue">
                        ¿Cómo compruebo el estado de entrega del pedido?
                    </x-slot:title>
                    <x-slot:content class="text-sm text-[#666666]">
                        Toque la sección "Mis pedidos" en el menú principal de la aplicación / sitio web / sitio M para
                        verificar el estado de su pedido.
                    </x-slot:content>
                </x-card-icon>
            </li>
            <li>
                <x-card-icon class="w-full !flex-col shadow-2xl">
                    <x-slot:icon
                        class="flex items-center self-start justify-center rounded-full h-14 w-14 bg-neutral-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="11" fill="#28C7A1" stroke="none"/>
                            <path d="M12 7V17M12 7C10.5 7 9 8 9 10C9 11.5 10 12 12 12M12 7C13.5 7 15 8 15 10C15 11.5 14 12 12 12M12 17C10.5 17 9 16 9 14C9 12.5 10 12 12 12M12 17C13.5 17 15 16 15 14C15 12.5 14 12 12 12" 
                                stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </x-slot:icon>
                    <x-slot:title class="text-lg font-bold text-neutral-blue">
                        ¿Qué son los reembolsos instantáneos?
                    </x-slot:title>
                    <x-slot:content class="text-sm text-[#666666]">
                        Una vez recogido con éxito el producto devuelto en la puerta de su casa, Myntra iniciará
                        instantáneamente el reembolso a su cuenta de origen o al método de reembolso elegido. Los
                        reembolsos
                        instantáneos no están disponibles en algunos códigos PIN seleccionados y para todas las
                        devoluciones
                        de autoenvío.
                    </x-slot:content>
                </x-card-icon>
            </li>
            <li>
                <x-card-icon class="w-full !flex-col shadow-2xl">
                    <x-slot:icon
                        class="flex items-center self-start justify-center rounded-full h-14 w-14 bg-neutral-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="11" fill="#28C7A1" stroke="none"/>
                            <path d="M7 9L11 13L17 7" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M18 12V16C18 16.5523 17.5523 17 17 17H7C6.44772 17 6 16.5523 6 16V8C6 7.44772 6.44772 7 7 7H14" 
                                stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </x-slot:icon>
                    <x-slot:title class="text-lg font-bold text-neutral-blue">
                        ¿Cómo aplico un cupón en mi pedido?
                    </x-slot:title>
                    <x-slot:content class="text-sm text-[#666666]">
                        Puede aplicar un cupón en la página del carrito antes de realizar el pedido. La lista completa
                        de
                        sus cupones válidos y no utilizados estará disponible en la pestaña "Mis cupones" de la
                        aplicación /
                        sitio web / sitio M.
                    </x-slot:content>
                </x-card-icon>
            </li>
        </ul>
    </div>
</x-app-layout>

<script>
function handleSearch() {
    const searchInput = document.getElementById('support-search');
    const searchTerm = searchInput.value.toLowerCase();
    const cards = document.querySelectorAll('#support-cards li');

    cards.forEach(card => {
        const title = card.querySelector('.text-lg').textContent.toLowerCase();
        const content = card.querySelector('.text-sm').textContent.toLowerCase();

        if (title.includes(searchTerm) || content.includes(searchTerm)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

// También realizar búsqueda al escribir
document.getElementById('support-search').addEventListener('input', handleSearch);
</script>
