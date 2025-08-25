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
        <div class="flex gap-4">
            <x-search-input class="w-[760px]" id="support-search" />

            <x-primary-button class="group" onclick="handleSearch()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path
                        d="M19 19L15.5001 15.5M18 9.5C18 14.1944 14.1944 18 9.5 18C4.80558 18 1 14.1944 1 9.5C1 4.80558 4.80558 1 9.5 1C14.1944 1 18 4.80558 18 9.5Z"
                        stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-disabled:stroke-[#C2C2C2]"/>
                </svg>
            </x-primary-button>
        </div>

        <ul class="grid grid-cols-4 gap-x-6 gap-y-8" id="support-cards">
            <li>
                <x-card-icon class="w-full !flex-col shadow-2xl">
                    <x-slot:icon
                        class="flex items-center self-start justify-center p-2 rounded-full min-h-11 min-w-11 bg-neutral-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="14" viewBox="0 0 17 14"
                            fill="none">
                            <path
                                d="M16.0066 2.23992C16.0066 1.43093 15.3448 0.769035 14.5358 0.769035H2.76873C1.95975 0.769035 1.29785 1.43093 1.29785 2.23992M16.0066 2.23992V11.0652C16.0066 11.8742 15.3448 12.5361 14.5358 12.5361H2.76873C1.95975 12.5361 1.29785 11.8742 1.29785 11.0652V2.23992M16.0066 2.23992L8.65225 7.38799L1.29785 2.23992"
                                stroke="white" stroke-width="1.47088" stroke-linecap="round" stroke-linejoin="round" />
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
                        class="flex items-center self-start justify-center p-2 rounded-full min-h-11 min-w-11 bg-neutral-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17"
                            fill="none">
                            <path
                                d="M3.471 3.45416L13.8701 13.8533M16.025 8.65372C16.025 12.7154 12.7323 16.0081 8.67056 16.0081C4.60884 16.0081 1.31616 12.7154 1.31616 8.65372C1.31616 4.59199 4.60884 1.29932 8.67056 1.29932C12.7323 1.29932 16.025 4.59199 16.025 8.65372Z"
                                stroke="white" stroke-width="1.47088" stroke-linecap="round" stroke-linejoin="round" />
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
                        class="flex items-center self-start justify-center p-2 rounded-full min-h-11 min-w-11 bg-neutral-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="14" viewBox="0 0 18 14"
                            fill="none">
                            <path
                                d="M1.07214 5.18167H17.2518M2.54302 0.769035H15.7809C16.5933 0.769035 17.2518 1.42757 17.2518 2.23992V11.0652C17.2518 11.8775 16.5933 12.5361 15.7809 12.5361H2.54302C1.73068 12.5361 1.07214 11.8775 1.07214 11.0652V2.23992C1.07214 1.42757 1.73068 0.769035 2.54302 0.769035Z"
                                stroke="white" stroke-width="1.47088" stroke-linecap="round" stroke-linejoin="round" />
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
                        class="flex items-center self-start justify-center p-2 rounded-full min-h-11 min-w-11 bg-neutral-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18"
                            fill="none">
                            <g clip-path="url(#clip0_3161_105211)">
                                <path d="M12.5907 2.21783H1.55908V11.7786H12.5907V2.21783Z" stroke="white"
                                    stroke-width="1.47088" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M12.5933 5.89586H15.535L17.7413 8.10218V11.7794H12.5933V5.89586Z"
                                    stroke="white" stroke-width="1.47088" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path
                                    d="M4.87131 15.4557C5.88675 15.4557 6.70991 14.6325 6.70991 13.6171C6.70991 12.6016 5.88675 11.7785 4.87131 11.7785C3.85588 11.7785 3.03271 12.6016 3.03271 13.6171C3.03271 14.6325 3.85588 15.4557 4.87131 15.4557Z"
                                    stroke="white" stroke-width="1.47088" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path
                                    d="M14.4319 15.4557C15.4473 15.4557 16.2705 14.6325 16.2705 13.6171C16.2705 12.6016 15.4473 11.7785 14.4319 11.7785C13.4164 11.7785 12.5933 12.6016 12.5933 13.6171C12.5933 14.6325 13.4164 15.4557 14.4319 15.4557Z"
                                    stroke="white" stroke-width="1.47088" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </g>
                            <defs>
                                <clipPath id="clip0_3161_105211">
                                    <rect width="17.6506" height="17.6506" fill="white"
                                        transform="translate(0.826172 0.0111389)" />
                                </clipPath>
                            </defs>
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
                        class="flex items-center self-start justify-center p-2 rounded-full min-h-11 min-w-11 bg-neutral-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="19" viewBox="0 0 18 19"
                            fill="none">
                            <g clip-path="url(#clip0_3161_105221)">
                                <path d="M8.87378 1.7124V17.8921" stroke="white" stroke-width="1.47088"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M12.5507 4.65556H7.03486C6.35218 4.65556 5.69746 4.92676 5.21473 5.40948C4.73201 5.89221 4.46082 6.54693 4.46082 7.2296C4.46082 7.91228 4.73201 8.567 5.21473 9.04972C5.69746 9.53245 6.35218 9.80364 7.03486 9.80364H10.7121C11.3947 9.80364 12.0495 10.0748 12.5322 10.5576C13.0149 11.0403 13.2861 11.695 13.2861 12.3777C13.2861 13.0604 13.0149 13.7151 12.5322 14.1978C12.0495 14.6805 11.3947 14.9517 10.7121 14.9517H4.46082"
                                    stroke="white" stroke-width="1.47088" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </g>
                            <defs>
                                <clipPath id="clip0_3161_105221">
                                    <rect width="17.6506" height="17.6506" fill="white"
                                        transform="translate(0.0491943 0.97757)" />
                                </clipPath>
                            </defs>
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
                        class="flex items-center self-start justify-center p-2 rounded-full min-h-11 min-w-11 bg-neutral-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18"
                            fill="none">
                            <g clip-path="url(#clip0_3161_105229)">
                                <path
                                    d="M16.0346 10.0638L10.7615 15.3369C10.6249 15.4736 10.4627 15.5821 10.2841 15.6561C10.1056 15.7302 9.91417 15.7683 9.72087 15.7683C9.52757 15.7683 9.33617 15.7302 9.15761 15.6561C8.97905 15.5821 8.81683 15.4736 8.68022 15.3369L2.36279 9.02679V1.67239H9.71719L16.0346 7.98982C16.3086 8.26541 16.4623 8.63821 16.4623 9.02679C16.4623 9.41538 16.3086 9.78817 16.0346 10.0638V10.0638Z"
                                    stroke="white" stroke-width="1.47088" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M6.04077 5.34669H6.04946" stroke="white" stroke-width="1.47088"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </g>
                            <defs>
                                <clipPath id="clip0_3161_105229">
                                    <rect width="17.6506" height="17.6506" fill="white"
                                        transform="translate(0.891479 0.197647)" />
                                </clipPath>
                            </defs>
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
