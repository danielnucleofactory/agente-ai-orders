@php
    $etapaArray = ["e1" => "Etapa 1", "e2" => "Etapa 2"];
@endphp

<x-app-layout>
    <div class="flex justify-between items-center">
        <x-view-title>
            <x-slot:title>
                Etapas PO
            </x-slot:title>

            <x-slot:content>
                Gestiona todas las etapas de tus órdenes de compra
            </x-slot:content>
        </x-view-title>

        <a href="{{route('purchase-orders.create')}}" class="block w-fit rounded-[0.375rem] bg-[#0F172A] px-4 py-2 text-white">
            Cargar nueva orden de compra
        </a>
    </div>

    <div x-data="{ menuOpen: false }" class="relative">
        <button x-on:click="menuOpen = ! menuOpen" class="ml-auto block rounded-[0.375rem] bg-[#DDDDDD] px-2 py-4">
            <span>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="4" viewBox="0 0 18 4" fill="none">
                    <path
                        d="M4 2C4 2.53043 3.78929 3.03914 3.41421 3.41421C3.03914 3.78929 2.53043 4 2 4C1.46957 4 0.960859 3.78929 0.585786 3.41421C0.210714 3.03914 0 2.53043 0 2C0 1.46957 0.210714 0.96086 0.585786 0.585787C0.960859 0.210714 1.46957 0 2 0C2.53043 0 3.03914 0.210714 3.41421 0.585787C3.78929 0.96086 4 1.46957 4 2ZM11 2C11 2.53043 10.7893 3.03914 10.4142 3.41421C10.0391 3.78929 9.53043 4 9 4C8.46957 4 7.96086 3.78929 7.58579 3.41421C7.21071 3.03914 7 2.53043 7 2C7 1.46957 7.21071 0.96086 7.58579 0.585787C7.96086 0.210714 8.46957 0 9 0C9.53043 0 10.0391 0.210714 10.4142 0.585787C10.7893 0.96086 11 1.46957 11 2ZM18 2C18 2.53043 17.7893 3.03914 17.4142 3.41421C17.0391 3.78929 16.5304 4 16 4C15.4696 4 14.9609 3.78929 14.5858 3.41421C14.2107 3.03914 14 2.53043 14 2C14 1.46957 14.2107 0.96086 14.5858 0.585787C14.9609 0.210714 15.4696 0 16 0C16.5304 0 17.0391 0.210714 17.4142 0.585787C17.7893 0.96086 18 1.46957 18 2Z"
                        fill="black" />
                </svg>
            </span>
        </button>

        <ul x-show="menuOpen"
            class="absolute right-0 top-full mt-[0.375rem] space-y-1 bg-white p-2 font-inter font-medium shadow-[0_4px_4px_0_rgba(0,0,0,0.25)]">
            <li>
                <button x-data="" x-on:click="$dispatch('open-modal', 'change-oc-stage'); menuOpen = false"
                    class="w-full rounded-md px-2 py-1 text-left hover:bg-[#E9E9E9]">
                    Cambiar etapa
                </button>
            </li>
            <li>
                <button x-on:click="$dispatch('open-modal', 'modify-oc'); menuOpen = false"
                    class="w-full rounded-md px-2 py-1 text-left hover:bg-[#E9E9E9]">
                    Editar PO
                </button>
            </li>
            <li>
                <button class="w-full rounded-md px-2 py-1 text-left hover:bg-[#E9E9E9]"
                    x-on:click="$dispatch('open-modal', 'delete-oc'); menuOpen = false">
                    Eliminar PO
                </button>
            </li>
        </ul>
    </div>

    <x-modal name="change-oc-stage" maxWidth="lg">
        <h3 class="mb-12 text-2xl font-bold">
            ¿Cambiar la Orden de compra de etapa?
        </h3>

        <div class="mb-8">
            <x-form-select label="" name="etapa" :options="$etapaArray" optionPlaceholder="Seleccionar etapa" />
        </div>

        <x-form-textarea label="Comentarios" placeholder="Ingrese su texto..." class="mb-[0.375rem]" />

        <div class="mb-12">
            <button
                class="flex w-full items-center justify-center gap-[0.625rem] rounded-[0.375rem] border border-[#E2E8F0] bg-white px-4 py-2 font-inter text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15"
                    fill="none">
                    <path
                        d="M7.39618 1.434L7.39621 1.43402L7.40063 1.42949C7.6625 1.16093 7.9751 0.947046 8.32027 0.800239C8.66545 0.653431 9.03634 0.576625 9.41144 0.574274C9.78653 0.571924 10.1584 0.644077 10.5053 0.786547C10.8523 0.929018 11.1676 1.13897 11.4328 1.40422C11.698 1.66948 11.9079 1.98476 12.0503 2.33177C12.1928 2.67878 12.2649 3.05061 12.2625 3.4257C12.2601 3.8008 12.1832 4.17168 12.0363 4.51684C11.8895 4.86199 11.6756 5.17455 11.407 5.43639L11.4069 5.43636L11.4024 5.44089L6.21774 10.6262L6.21773 10.6262L6.21486 10.6291C6.076 10.7703 5.91057 10.8826 5.72811 10.9595C5.54565 11.0364 5.34976 11.0764 5.15175 11.0773C4.95374 11.0781 4.75753 11.0397 4.57443 10.9643C4.39133 10.8889 4.22497 10.7781 4.08493 10.6381C3.9449 10.4981 3.83398 10.3317 3.75855 10.1486C3.68313 9.96555 3.6447 9.76935 3.64548 9.57134C3.64626 9.37333 3.68624 9.17743 3.76311 8.99495C3.83998 8.81247 3.95222 8.64702 4.09336 8.50813L4.09337 8.50814L4.0962 8.50531L8.92798 3.67353L9.16354 3.90909L4.33443 8.7382C4.22465 8.84513 4.13701 8.97264 4.07651 9.11347C4.01537 9.25581 3.98318 9.4089 3.98184 9.56381C3.98049 9.71872 4.01001 9.87234 4.06867 10.0157C4.12733 10.1591 4.21396 10.2894 4.3235 10.3989C4.43304 10.5084 4.5633 10.5951 4.70668 10.6537C4.85006 10.7124 5.00369 10.7419 5.1586 10.7406C5.3135 10.7392 5.46659 10.707 5.60893 10.6459C5.74975 10.5854 5.87725 10.4978 5.98417 10.388L11.1675 5.20533L11.1675 5.20531C11.3997 4.97314 11.5839 4.69753 11.7095 4.39419C11.8351 4.09086 11.8998 3.76575 11.8998 3.43742C11.8998 3.10909 11.8351 2.78398 11.7095 2.48065C11.5839 2.17731 11.3997 1.90169 11.1675 1.66953C10.9354 1.43737 10.6598 1.25321 10.3564 1.12756C10.0531 1.00192 9.72798 0.93725 9.39965 0.93725C9.07132 0.93725 8.74621 1.00192 8.44288 1.12756C8.13954 1.25321 7.86393 1.43737 7.63176 1.66953L7.63174 1.66955L2.44641 6.85556L2.44635 6.8555L2.44034 6.86173C1.74207 7.5847 1.35569 8.55301 1.36442 9.5581C1.37316 10.5632 1.7763 11.5246 2.48704 12.2354C3.19777 12.9461 4.15921 13.3492 5.1643 13.358C6.16939 13.3667 7.1377 12.9803 7.86067 12.2821L7.86073 12.2821L7.86685 12.276L13.1705 6.97296L13.4063 7.20896L8.1031 12.5122C7.32165 13.2936 6.26178 13.7327 5.15665 13.7327C4.05152 13.7327 2.99165 13.2936 2.2102 12.5122C1.42876 11.7308 0.989746 10.6709 0.989746 9.56575C0.989746 8.46063 1.42875 7.40077 2.21018 6.61933C2.21019 6.61932 2.2102 6.61931 2.2102 6.61931L7.39618 1.434Z"
                        fill="black" stroke="#0F172A" />
                </svg>

                <span>Adjuntar documentación...</span>
            </button>
        </div>

        <div class="flex gap-[1.875rem]">
            <x-black-btn x-on:click="$dispatch('close-modal', 'change-oc-stage')" class="w-full">
                Continuar
            </x-black-btn>
            <x-white-btn x-on:click="$dispatch('close-modal', 'change-oc-stage')" class="w-full">
                Cancelar
            </x-white-btn>
        </div>
    </x-modal>

    <x-modal name="modify-oc" maxWidth="lg">
        <h3 class="mb-12 text-2xl font-bold">
            Editar Orden de Compra
        </h3>

        <p class="mb-6 text-gray-700">
            Para editar los detalles de una orden de compra, seleccione primero la tarjeta
            correspondiente en el tablero Kanban y luego haga clic en el botón "Ver detalle"
            que aparece en la esquina superior derecha de cada tarjeta.
        </p>

        <p class="mb-6 text-sm text-blue-600">
            <b>Nota:</b> Cada tarjeta tiene un botón "Ver detalle" para acceder directamente a la
            información de la orden de compra seleccionada.
        </p>

        <div class="flex justify-center mb-8">
            <img src="https://placehold.co/300x150?text=Ejemplo+de+tarjeta" alt="Ejemplo de tarjeta" class="rounded-md shadow-md">
        </div>

        <div class="flex gap-[1.875rem]">
            <x-white-btn x-on:click="$dispatch('close-modal', 'modify-oc')" class="w-full">
                Entendido
            </x-white-btn>
        </div>
    </x-modal>

    <x-modal-success title="Operación exitosa"
        content="La operación se encuentra pendiente de aprobación por parte de su supervisor" maxWidth="xs" />

    <div class="flex overflow-auto gap-x-10 w-full">
        <livewire:kanban.kanban-board />
    </div>
</x-app-layout>
