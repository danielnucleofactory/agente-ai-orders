import "./bootstrap";
import Sortable from "sortablejs";
import flatpickr from "flatpickr";
import { Spanish } from "flatpickr/dist/l10n/es.js";

window.Sortable = Sortable;

// Configuración de Flatpickr
flatpickr.localize(Spanish);

// Mapeo de formatos de usuario a formatos de Flatpickr
const dateFormatMap = {
    'DD/MM/YYYY': 'd/m/Y',
    'MM/DD/YYYY': 'm/d/Y',
    'YYYY/MM/DD': 'Y/m/d',
};

// Función para inicializar Flatpickr en campos de fecha
function initializeDatePickers() {
    const userFormat = document.body.getAttribute('data-date-format') || 'DD/MM/YYYY';
    const displayFormat = dateFormatMap[userFormat] || 'd/m/Y';
    
    // Seleccionar todos los campos type="date" que no estén ya inicializados
    document.querySelectorAll('input[type="date"]:not(.flatpickr-initialized)').forEach(function(input) {
        // Marcar como inicializado
        input.classList.add('flatpickr-initialized');
        
        // Guardar el valor original antes de convertir
        const originalValue = input.value;
        
        // Obtener el wire:model si existe
        const wireModel = input.getAttribute('wire:model') || 
                          input.getAttribute('wire:model.live') || 
                          input.getAttribute('wire:model.defer') ||
                          input.getAttribute('wire:model.lazy');
        
        // Cambiar el tipo a text para que Flatpickr pueda controlarlo
        input.type = 'text';
        
        // Crear placeholder basado en el formato del usuario
        const placeholderMap = {
            'DD/MM/YYYY': 'dd/mm/aaaa',
            'MM/DD/YYYY': 'mm/dd/aaaa',
            'YYYY/MM/DD': 'aaaa/mm/dd',
        };
        const placeholder = placeholderMap[userFormat] || 'dd/mm/aaaa';
        
        // Inicializar Flatpickr
        const fp = flatpickr(input, {
            // El valor interno siempre en formato ISO para la integración
            dateFormat: 'Y-m-d',
            // El input alternativo muestra el formato del usuario
            altInput: true,
            altFormat: displayFormat,
            allowInput: true,
            locale: 'es',
            // Clase para el input alternativo
            altInputClass: 'flatpickr-alt-input',
            // Cuando cambia la fecha, actualizar el modelo de Livewire
            onChange: function(selectedDates, dateStr, instance) {
                // El dateStr ya está en formato Y-m-d (ISO) - este es el valor del input original
                // Actualizar el input original con el valor ISO
                input.value = dateStr;
                
                // Actualizar placeholder del input alternativo si está vacío
                const altInput = instance.altInput;
                if (altInput && !dateStr) {
                    altInput.placeholder = placeholder;
                } else if (altInput && dateStr) {
                    altInput.placeholder = '';
                }
                
                // Disparar evento de input para Livewire
                const inputEvent = new Event('input', { bubbles: true });
                input.dispatchEvent(inputEvent);
                
                // También disparar change para wire:model
                const changeEvent = new Event('change', { bubbles: true });
                input.dispatchEvent(changeEvent);
                
                // Si hay wire:model, actualizar directamente
                if (wireModel && window.Livewire) {
                    const component = Livewire.find(input.closest('[wire\\:id]')?.getAttribute('wire:id'));
                    if (component) {
                        component.set(wireModel, dateStr);
                    }
                }
            },
        });
        
        // Guardar referencia a la instancia de Flatpickr en el input
        input._flatpickr = fp;
        
        // Aplicar estilos al input alternativo después de que Flatpickr lo crea
        if (fp.altInput) {
            // Asegurar que tenga la clase correcta
            fp.altInput.classList.add('flatpickr-alt-input');
            // Configurar placeholder
            fp.altInput.placeholder = placeholder;
            // Si no hay valor, asegurar que el placeholder se muestre
            if (!originalValue) {
                fp.altInput.value = '';
            }
        }
        
        // Si había un valor original, establecerlo
        if (originalValue) {
            fp.setDate(originalValue, false);
        } else {
            // Si no hay valor, asegurar que el placeholder se muestre
            if (fp.altInput) {
                fp.altInput.value = '';
                fp.altInput.placeholder = placeholder;
            }
        }
        
        // Sincronizar cuando Livewire actualiza el valor del input
        // Usar un observer para detectar cambios en el atributo value
        const observer = new MutationObserver(function() {
            const currentValue = input.value;
            if (currentValue) {
                // Verificar si el valor cambió
                const fpValue = fp.selectedDates.length > 0 
                    ? fp.formatDate(fp.selectedDates[0], 'Y-m-d')
                    : '';
                if (currentValue !== fpValue) {
                    fp.setDate(currentValue, false);
                }
            } else if (fp.selectedDates.length > 0) {
                fp.clear();
                // Restaurar placeholder cuando se limpia
                if (fp.altInput) {
                    fp.altInput.value = '';
                    fp.altInput.placeholder = placeholder;
                }
            } else {
                // Asegurar placeholder cuando está vacío
                if (fp.altInput && !fp.altInput.value) {
                    fp.altInput.placeholder = placeholder;
                }
            }
        });
        
        observer.observe(input, {
            attributes: true,
            attributeFilter: ['value']
        });
        
        // Función para sincronizar el valor
        const syncValue = function() {
            const currentValue = input.value;
            if (currentValue) {
                const fpValue = fp.selectedDates.length > 0 
                    ? fp.formatDate(fp.selectedDates[0], 'Y-m-d')
                    : '';
                if (currentValue !== fpValue) {
                    fp.setDate(currentValue, false);
                }
            } else if (fp.selectedDates.length > 0) {
                fp.clear();
                // Restaurar placeholder cuando se limpia
                if (fp.altInput) {
                    fp.altInput.value = '';
                    fp.altInput.placeholder = placeholder;
                }
            } else {
                // Asegurar placeholder cuando está vacío
                if (fp.altInput && !fp.altInput.value) {
                    fp.altInput.placeholder = placeholder;
                }
            }
        };
        
        // También escuchar el evento 'input' para detectar cambios programáticos
        input.addEventListener('input', syncValue, { passive: true });
    });
}

// Exponer función globalmente para Livewire
window.initializeDatePickers = initializeDatePickers;

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar date pickers
    initializeDatePickers();
    
    // Configuración del sidebar
    const sidebar = document.querySelector('.main-sidebar');
    if (sidebar) {
        // Aplicar el estado guardado
        if (localStorage.getItem('sidebarExpanded') === 'true') {
            sidebar.classList.add('sidebar-expanded');
        } else {
            sidebar.classList.remove('sidebar-expanded');
        }

        // Función global para compatibilidad
        window.toggleSidebarSimple = function() {
            sidebar.classList.toggle('sidebar-expanded');
            localStorage.setItem('sidebarExpanded', sidebar.classList.contains('sidebar-expanded'));
        };
    }
});

// Re-inicializar después de que Livewire actualice el DOM
document.addEventListener('livewire:navigated', function() {
    initializeDatePickers();
});

// Función auxiliar para obtener el placeholder según el formato del usuario
function getDatePlaceholder() {
    const userFormat = document.body.getAttribute('data-date-format') || 'DD/MM/YYYY';
    const placeholderMap = {
        'DD/MM/YYYY': 'dd/mm/aaaa',
        'MM/DD/YYYY': 'mm/dd/aaaa',
        'YYYY/MM/DD': 'aaaa/mm/dd',
    };
    return placeholderMap[userFormat] || 'dd/mm/aaaa';
}

// También escuchar el evento de Livewire cuando actualiza componentes
document.addEventListener('livewire:init', function() {
    Livewire.hook('morph.updated', ({ el, component }) => {
        // Pequeño delay para asegurar que el DOM esté listo
        setTimeout(function() {
            // Re-inicializar date pickers para nuevos campos
            initializeDatePickers();
            
            // Sincronizar valores existentes con Flatpickr
            const placeholder = getDatePlaceholder();
            document.querySelectorAll('input.flatpickr-initialized').forEach(function(input) {
                if (input._flatpickr) {
                    const currentValue = input.value;
                    if (currentValue) {
                        const fpValue = input._flatpickr.selectedDates.length > 0 
                            ? input._flatpickr.formatDate(input._flatpickr.selectedDates[0], 'Y-m-d')
                            : '';
                        if (currentValue !== fpValue) {
                            input._flatpickr.setDate(currentValue, false);
                        }
                    } else {
                        // Si está vacío, asegurar placeholder
                        if (input._flatpickr.selectedDates.length > 0) {
                            input._flatpickr.clear();
                        }
                        if (input._flatpickr.altInput) {
                            input._flatpickr.altInput.value = '';
                            input._flatpickr.altInput.placeholder = placeholder;
                        }
                    }
                }
            });
        }, 150);
    });
    
    // Escuchar cuando Livewire actualiza el DOM después de un commit
    Livewire.hook('morph', ({ el, component, cleanup }) => {
        cleanup(() => {
            // Después de que Livewire actualiza el DOM, sincronizar los date pickers
            setTimeout(function() {
                const placeholder = getDatePlaceholder();
                document.querySelectorAll('input.flatpickr-initialized').forEach(function(input) {
                    if (input._flatpickr) {
                        const currentValue = input.value;
                        if (currentValue) {
                            const fpValue = input._flatpickr.selectedDates.length > 0 
                                ? input._flatpickr.formatDate(input._flatpickr.selectedDates[0], 'Y-m-d')
                                : '';
                            if (currentValue !== fpValue) {
                                input._flatpickr.setDate(currentValue, false);
                            }
                        } else {
                            // Si está vacío, asegurar placeholder
                            if (input._flatpickr.altInput) {
                                input._flatpickr.altInput.value = '';
                                input._flatpickr.altInput.placeholder = placeholder;
                            }
                        }
                    }
                });
            }, 100);
        });
    });
});
