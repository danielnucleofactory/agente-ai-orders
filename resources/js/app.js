import "./bootstrap";
import Sortable from "sortablejs";
import flatpickr from "flatpickr";
import { Spanish } from "flatpickr/dist/l10n/es.js";

window.Sortable = Sortable;

// Configuración global de Flatpickr
flatpickr.localize(Spanish);

// Mapeo de formatos de usuario a formatos de Flatpickr
const dateFormatMap = {
    'DD/MM/YYYY': 'd/m/Y',
    'MM/DD/YYYY': 'm/d/Y',
    'YYYY/MM/DD': 'Y/m/d',
};

const placeholderMap = {
    'DD/MM/YYYY': 'dd/mm/aaaa',
    'MM/DD/YYYY': 'mm/dd/aaaa',
    'YYYY/MM/DD': 'aaaa/mm/dd',
};

/**
 * Obtiene la configuración de formato de fecha del usuario actual.
 */
function getUserDateConfig() {
    const userFormat = document.body.getAttribute('data-date-format') || 'DD/MM/YYYY';
    return {
        userFormat,
        displayFormat: dateFormatMap[userFormat] || 'd/m/Y',
        placeholder: placeholderMap[userFormat] || 'dd/mm/aaaa',
    };
}

/**
 * Formatea una fecha ISO (Y-m-d) al formato de display del usuario.
 * Utilidad para campos readonly que no usan Flatpickr.
 */
function formatDateForDisplay(isoDate, displayFormat) {
    if (!isoDate) return '';
    const tempInput = document.createElement('input');
    const tempFp = flatpickr(tempInput, { dateFormat: 'Y-m-d' });
    const date = tempFp.parseDate(isoDate, 'Y-m-d');
    let result = '';
    if (date) {
        result = tempFp.formatDate(date, displayFormat);
    }
    tempFp.destroy();
    return result;
}

// Exponer utilidades globalmente (para uso en dashboards u otras páginas sin Livewire)
window.flatpickrConfig = { getUserDateConfig, formatDateForDisplay, dateFormatMap, placeholderMap };

// =============================================================================
// Componente Alpine.js: datePicker
// Se usa dentro del Blade component <x-date-picker> y también inline en vistas
// =============================================================================
document.addEventListener('alpine:init', () => {
    Alpine.data('datePicker', (modelValue) => ({
        value: modelValue,
        fp: null,

        init() {
            const { displayFormat, placeholder } = getUserDateConfig();
            const input = this.$refs.picker;

            if (!input) return;

            const isReadonly = input.hasAttribute('readonly') || input.hasAttribute('disabled');

            // --- Modo readonly: solo mostrar la fecha formateada, sin picker ---
            if (isReadonly) {
                const showFormatted = (val) => {
                    input.value = val ? formatDateForDisplay(val, displayFormat) : '';
                };
                showFormatted(this.value);
                this.$watch('value', showFormatted);
                return;
            }

            // --- Modo editable: inicializar Flatpickr ---
            this.fp = flatpickr(input, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: displayFormat,
                allowInput: true,
                locale: 'es',
                altInputClass: 'flatpickr-alt-input ' + input.className,
                defaultDate: this.value || null,
                onChange: (selectedDates, dateStr) => {
                    this.value = dateStr || null;
                },
            });

            // Configurar placeholder en el input visible (altInput)
            if (this.fp.altInput) {
                this.fp.altInput.placeholder = placeholder;
            }

            // Observar cambios externos (desde Livewire vía @entangle)
            this.$watch('value', (newVal) => {
                if (!this.fp) return;
                const currentVal = this.fp.selectedDates.length > 0
                    ? this.fp.formatDate(this.fp.selectedDates[0], 'Y-m-d')
                    : null;
                if (newVal !== currentVal) {
                    if (newVal) {
                        this.fp.setDate(newVal, false);
                    } else {
                        this.fp.clear();
                        if (this.fp.altInput) {
                            this.fp.altInput.value = '';
                            this.fp.altInput.placeholder = placeholder;
                        }
                    }
                }
            });
        },

        destroy() {
            if (this.fp) {
                this.fp.destroy();
                this.fp = null;
            }
        }
    }));
});

// =============================================================================
// Sidebar
// =============================================================================
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('.main-sidebar');
    if (sidebar) {
        if (localStorage.getItem('sidebarExpanded') === 'true') {
            sidebar.classList.add('sidebar-expanded');
        } else {
            sidebar.classList.remove('sidebar-expanded');
        }

        window.toggleSidebarSimple = function () {
            sidebar.classList.toggle('sidebar-expanded');
            localStorage.setItem('sidebarExpanded', sidebar.classList.contains('sidebar-expanded'));
        };
    }
});
