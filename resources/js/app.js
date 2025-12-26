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

    // PRIMERO: Limpiar instancias huérfanas de Flatpickr (elementos que tienen la clase pero no la instancia)
    // Esto puede pasar cuando Livewire recrea elementos del DOM
    document.querySelectorAll('input.flatpickr-initialized').forEach(function(input) {
        // Si tiene la clase pero no tiene instancia de Flatpickr, remover la clase para reinicializar
        if (!input._flatpickr) {
            // Preservar el valor antes de remover la clase
            const savedValue = input.getAttribute('data-date-value') || input.value;
            if (savedValue) {
                input.setAttribute('data-date-value', savedValue);
            }
            input.classList.remove('flatpickr-initialized');
        } else {
            // Si tiene instancia, preservar el valor
            if (input._flatpickr.selectedDates.length > 0) {
                const currentDate = input._flatpickr.formatDate(input._flatpickr.selectedDates[0], 'Y-m-d');
                input.setAttribute('data-date-value', currentDate);
            } else if (input.value) {
                input.setAttribute('data-date-value', input.value);
            }
        }
    });

    // SEGUNDO: Seleccionar todos los campos type="date" que no estén ya inicializados
    // También verificar inputs type="text" que puedan haber sido recreados por Livewire
    // Buscar por wire:model que contenga "date" (case insensitive)
    const dateInputs = [];

    // Buscar inputs type="date"
    document.querySelectorAll('input[type="date"]:not(.flatpickr-initialized)').forEach(function(input) {
        dateInputs.push(input);
    });

    // Buscar inputs type="text" con wire:model relacionado con fechas
    document.querySelectorAll('input[type="text"]:not(.flatpickr-initialized)').forEach(function(input) {
        const wireModel = input.getAttribute('wire:model') ||
                          input.getAttribute('wire:model.live') ||
                          input.getAttribute('wire:model.defer') ||
                          input.getAttribute('wire:model.lazy');
        if (wireModel) {
            const wireModelLower = wireModel.toLowerCase();
            // Verificar si el wire:model contiene "date" o si tiene data-date-value (fue un datepicker antes)
            if (wireModelLower.includes('date') || input.hasAttribute('data-date-value')) {
                dateInputs.push(input);
            }
        }
    });

    // Procesar todos los inputs de fecha encontrados
    dateInputs.forEach(function(input) {
        // Si ya tiene una instancia de Flatpickr válida, solo sincronizar
        if (input._flatpickr && typeof input._flatpickr.destroy === 'function') {
            input.classList.add('flatpickr-initialized');
            const savedValue = input.getAttribute('data-date-value') || input.value;
            if (savedValue) {
                const fpValue = input._flatpickr.selectedDates.length > 0
                    ? input._flatpickr.formatDate(input._flatpickr.selectedDates[0], 'Y-m-d')
                    : '';
                if (savedValue !== fpValue) {
                    input._flatpickr.setDate(savedValue, false);
                }
            }
            return; // No reinicializar, solo sincronizar
        }

        // Si tiene una instancia inválida o huérfana, destruirla primero
        if (input._flatpickr) {
            try {
                input._flatpickr.destroy();
            } catch (e) {
                // Ignorar errores al destruir
            }
            input._flatpickr = null;
        }

        // Guardar el valor antes de cambiar el tipo (preservar durante refresh)
        const savedValue = input.getAttribute('data-date-value') || input.value;
        if (savedValue) {
            input.setAttribute('data-date-value', savedValue);
        }

        // Cambiar el tipo a text INMEDIATAMENTE para evitar el flash visual
        if (input.type === 'date') {
            input.type = 'text';
        }

        // Marcar como inicializado ANTES de continuar para evitar procesamiento duplicado
        input.classList.add('flatpickr-initialized');

        // Obtener el wire:model si existe
        const wireModel = input.getAttribute('wire:model') ||
                          input.getAttribute('wire:model.live') ||
                          input.getAttribute('wire:model.defer') ||
                          input.getAttribute('wire:model.lazy');

        // Guardar el valor original antes de convertir
        // Priorizar el valor guardado en data-date-value si existe (preservado durante refresh)
        const originalValue = savedValue;

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
                // Actualizar el input original con el valor ISO ANTES de disparar eventos
                input.value = dateStr;

                // Guardar el valor en un atributo data para preservarlo durante el refresh
                if (dateStr) {
                    input.setAttribute('data-date-value', dateStr);
                } else {
                    // Si está vacío, remover el atributo
                    input.removeAttribute('data-date-value');
                }

                // Actualizar placeholder del input alternativo si está vacío
                const altInput = instance.altInput;
                if (altInput && !dateStr) {
                    altInput.placeholder = placeholder;
                } else if (altInput && dateStr) {
                    altInput.placeholder = '';
                }

                // Usar requestAnimationFrame para asegurar que el valor se establezca antes del refresh
                requestAnimationFrame(() => {
                // Disparar evento de input para Livewire
                    const inputEvent = new Event('input', { bubbles: true, cancelable: true });
                input.dispatchEvent(inputEvent);

                // También disparar change para wire:model
                    const changeEvent = new Event('change', { bubbles: true, cancelable: true });
                input.dispatchEvent(changeEvent);

                    // Si hay wire:model, actualizar directamente usando defer para evitar refresh inmediato
                if (wireModel && window.Livewire) {
                        const wireId = input.closest('[wire\\:id]')?.getAttribute('wire:id');
                        if (wireId) {
                            const component = Livewire.find(wireId);
                    if (component) {
                                // Usar set con defer para evitar refresh inmediato
                                component.set(wireModel, dateStr, false);
                            }
                        }
                    }
                });
            },
            // Manejar cuando se cierra el datepicker
            onClose: function(selectedDates, dateStr, instance) {
                // Asegurar que el valor esté guardado en data-date-value
                if (dateStr) {
                    input.setAttribute('data-date-value', dateStr);
                } else {
                    input.removeAttribute('data-date-value');
                }
            }
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
            // Asegurar que el input tenga el valor antes de establecerlo en Flatpickr
            input.value = originalValue;
            // Establecer la fecha en Flatpickr
            fp.setDate(originalValue, false);
            // Asegurar que el atributo data-date-value esté sincronizado
            input.setAttribute('data-date-value', originalValue);
        } else {
            // Si no hay valor, asegurar que el placeholder se muestre
            if (fp.altInput) {
                fp.altInput.value = '';
                fp.altInput.placeholder = placeholder;
            }
            // Limpiar el atributo data-date-value si no hay valor
            input.removeAttribute('data-date-value');
        }

        // Asegurar que el input siempre tenga el tipo correcto después de inicializar
        if (input.type === 'date') {
            input.type = 'text';
        }

        // Sincronizar cuando Livewire actualiza el valor del input
        // Usar un observer para detectar cambios en el atributo value
        const observer = new MutationObserver(function() {
            // Priorizar el valor guardado en data-date-value
            const savedValue = input.getAttribute('data-date-value');
            const currentValue = savedValue || input.value;

            // Si hay un valor guardado, restaurarlo en el input
            if (savedValue && savedValue !== input.value) {
                input.value = savedValue;
            }

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
            attributeFilter: ['value', 'data-date-value']
        });

        // Función para sincronizar el valor
        const syncValue = function() {
            // Priorizar el valor guardado en data-date-value
            const savedValue = input.getAttribute('data-date-value');
            const currentValue = savedValue || input.value;

            // Si hay un valor guardado, restaurarlo en el input
            if (savedValue && savedValue !== input.value) {
                input.value = savedValue;
            }

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

// Observer global para detectar cuando Livewire restaura type="date" y cambiarlo inmediatamente
const dateInputObserver = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'type') {
            const target = mutation.target;
            // Si Livewire cambió el tipo de vuelta a "date" y es un campo que debería ser Flatpickr
            if (target.type === 'date' && target.hasAttribute('wire:model')) {
                const wireModel = target.getAttribute('wire:model') ||
                                  target.getAttribute('wire:model.live') ||
                                  target.getAttribute('wire:model.defer') ||
                                  target.getAttribute('wire:model.lazy');
                // Si tiene wire:model relacionado con fechas
                if (wireModel && (wireModel.includes('date') || wireModel.includes('Date'))) {
                    // Preservar el valor
                    const savedValue = target.getAttribute('data-date-value') || target.value;
                    if (savedValue) {
                        target.setAttribute('data-date-value', savedValue);
                    }
                    // Cambiar inmediatamente a text para evitar el flash
                    target.type = 'text';
                    // Si no está inicializado, inicializarlo
                    if (!target.classList.contains('flatpickr-initialized')) {
                        setTimeout(() => initializeDatePickers(), 0);
                    }
                }
            }
        }
        // También detectar cuando se agregan nuevos nodos al DOM
        if (mutation.type === 'childList') {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    // Buscar inputs type="date" dentro del nodo agregado
                    const dateInputs = node.querySelectorAll ? node.querySelectorAll('input[type="date"]') : [];
                    dateInputs.forEach(function(input) {
                        const wireModel = input.getAttribute('wire:model') ||
                                          input.getAttribute('wire:model.live') ||
                                          input.getAttribute('wire:model.defer') ||
                                          input.getAttribute('wire:model.lazy');
                        if (wireModel && (wireModel.includes('date') || wireModel.includes('Date'))) {
                            const savedValue = input.getAttribute('data-date-value') || input.value;
                            if (savedValue) {
                                input.setAttribute('data-date-value', savedValue);
                            }
                            input.type = 'text';
                            if (!input.classList.contains('flatpickr-initialized')) {
                                setTimeout(() => initializeDatePickers(), 0);
                            }
                        }
                    });
                }
            });
        }
    });
});

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar date pickers
    initializeDatePickers();

    // Observar cambios en el body para detectar cuando Livewire restaura inputs
    dateInputObserver.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['type']
    });

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
    // Hook que se ejecuta ANTES del morph - preservar valores antes de que se destruyan elementos
    Livewire.hook('morph.before', ({ el, component }) => {
        // Preservar TODOS los valores de datepickers antes de que Livewire destruya el DOM
        document.querySelectorAll('input.flatpickr-initialized').forEach(function(input) {
            if (input._flatpickr) {
                // Si tiene una instancia de Flatpickr activa, preservar su valor
                if (input._flatpickr.selectedDates.length > 0) {
                    const currentDate = input._flatpickr.formatDate(input._flatpickr.selectedDates[0], 'Y-m-d');
                    input.setAttribute('data-date-value', currentDate);
                    input.value = currentDate;
                } else if (input.value) {
                    input.setAttribute('data-date-value', input.value);
                }
            } else if (input.value) {
                // Si no tiene instancia pero tiene valor, preservarlo
                input.setAttribute('data-date-value', input.value);
            }
        });
    });

    Livewire.hook('morph.updated', ({ el, component }) => {
        // Sincronizar valores desde Livewire hacia inputs con wire:ignore
        // Esto es necesario porque wire:ignore previene que Livewire actualice esos elementos
        // Nota: Esta sincronización se hace principalmente usando data-date-value que se preserva antes del morph
        // Por lo tanto, no necesitamos acceder directamente a component.get() que puede no estar disponible

        // PASO 1: Cambiar inmediatamente todos los inputs type="date" a "text" antes de inicializar
        // Esto evita el flash visual (solo los que NO tienen wire:ignore)
        const dateInputs = el.querySelectorAll ? el.querySelectorAll('input[type="date"]:not([wire\\:ignore] input)') : [];
        dateInputs.forEach(function(input) {
            // Verificar que no esté dentro de un div con wire:ignore
            if (input.closest('[wire\\:ignore]')) {
                return; // Saltar inputs dentro de wire:ignore
            }

            const wireModel = input.getAttribute('wire:model') ||
                              input.getAttribute('wire:model.live') ||
                              input.getAttribute('wire:model.defer') ||
                              input.getAttribute('wire:model.lazy');
            if (wireModel && (wireModel.includes('date') || wireModel.includes('Date'))) {
                // Preservar valor si existe
                const savedValue = input.getAttribute('data-date-value') || input.value;
                if (savedValue) {
                    input.setAttribute('data-date-value', savedValue);
                    input.value = savedValue;
                }
                input.type = 'text';
                // Remover clase de inicializado para forzar reinicialización
                input.classList.remove('flatpickr-initialized');
            }
        });

        // PASO 2: Limpiar instancias huérfanas en el elemento actualizado
        if (el && el.querySelectorAll) {
            el.querySelectorAll('input.flatpickr-initialized').forEach(function(input) {
                // Si tiene la clase pero no tiene instancia válida, remover la clase
                if (!input._flatpickr || typeof input._flatpickr.destroy !== 'function') {
                    const savedValue = input.getAttribute('data-date-value') || input.value;
                    if (savedValue) {
                        input.setAttribute('data-date-value', savedValue);
                    }
                    input.classList.remove('flatpickr-initialized');
                    if (input._flatpickr) {
                        try {
                            input._flatpickr.destroy();
                        } catch (e) {
                            // Ignorar errores
                        }
                        input._flatpickr = null;
                    }
                }
            });
        }

        // PASO 3: Pequeño delay para asegurar que el DOM esté completamente listo
        setTimeout(function() {
            // Re-inicializar date pickers - esto limpiará instancias huérfanas y creará nuevas
            initializeDatePickers();

            // PASO 4: Sincronizar valores después de la inicialización
            const placeholder = getDatePlaceholder();
            document.querySelectorAll('input.flatpickr-initialized').forEach(function(input) {
                if (input._flatpickr && typeof input._flatpickr.setDate === 'function') {
                    const savedValue = input.getAttribute('data-date-value');
                    const currentValue = savedValue || input.value;

                    // Si hay un valor guardado, restaurarlo en el input y en Flatpickr
                    if (savedValue) {
                        input.value = savedValue;
                        const fpValue = input._flatpickr.selectedDates.length > 0
                            ? input._flatpickr.formatDate(input._flatpickr.selectedDates[0], 'Y-m-d')
                            : '';
                        if (savedValue !== fpValue) {
                            input._flatpickr.setDate(savedValue, false);
                        }
                    } else if (currentValue) {
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
        }, 250);
    });

    // Escuchar cuando Livewire actualiza el DOM después de un commit
    Livewire.hook('morph', (params) => {
        const { el, component, cleanup } = params || {};

        // ANTES del morph: Preservar TODOS los valores de datepickers en toda la página
        // Esto es crítico cuando Livewire hace un refresh completo del componente
        document.querySelectorAll('input.flatpickr-initialized').forEach(function(input) {
            if (input._flatpickr) {
                // Si tiene una instancia de Flatpickr activa, preservar su valor
                if (input._flatpickr.selectedDates.length > 0) {
                    const currentDate = input._flatpickr.formatDate(input._flatpickr.selectedDates[0], 'Y-m-d');
                    input.setAttribute('data-date-value', currentDate);
                    input.value = currentDate;
                } else if (input.value) {
                    input.setAttribute('data-date-value', input.value);
                }
            } else if (input.value) {
                // Si no tiene instancia pero tiene valor, preservarlo
                input.setAttribute('data-date-value', input.value);
            }
        });

        // Cambiar inmediatamente todos los inputs type="date" a "text" antes de sincronizar
        // Esto evita el flash visual durante el morph
        if (el && el.querySelectorAll) {
            const dateInputs = el.querySelectorAll('input[type="date"]');
            dateInputs.forEach(function(input) {
                const wireModel = input.getAttribute('wire:model') ||
                                  input.getAttribute('wire:model.live') ||
                                  input.getAttribute('wire:model.defer') ||
                                  input.getAttribute('wire:model.lazy');
                if (wireModel && (wireModel.includes('date') || wireModel.includes('Date'))) {
                    const savedValue = input.getAttribute('data-date-value') || input.value;
                    if (savedValue) {
                        input.setAttribute('data-date-value', savedValue);
                        input.value = savedValue;
                    }
                    input.type = 'text';
                }
            });
        }

        // Función para sincronizar los date pickers
        const syncDatePickers = () => {
            // Primero cambiar tipos inmediatamente
            if (el && el.querySelectorAll) {
                const dateInputs = el.querySelectorAll('input[type="date"]');
                dateInputs.forEach(function(input) {
                    const wireModel = input.getAttribute('wire:model') ||
                                      input.getAttribute('wire:model.live') ||
                                      input.getAttribute('wire:model.defer') ||
                                      input.getAttribute('wire:model.lazy');
                    if (wireModel && (wireModel.includes('date') || wireModel.includes('Date'))) {
                        const savedValue = input.getAttribute('data-date-value') || input.value;
                        if (savedValue) {
                            input.setAttribute('data-date-value', savedValue);
                            input.value = savedValue;
                        }
                        input.type = 'text';
                    }
                });
            }

            setTimeout(function() {
                // Re-inicializar date pickers para nuevos campos
                initializeDatePickers();
                const placeholder = getDatePlaceholder();
                document.querySelectorAll('input.flatpickr-initialized').forEach(function(input) {
                    if (input._flatpickr) {
                        // Usar el valor guardado en data-date-value si existe, sino el value del input
                        const savedValue = input.getAttribute('data-date-value');
                        const currentValue = savedValue || input.value;

                        // Si hay un valor guardado, restaurarlo en el input
                        if (savedValue && savedValue !== input.value) {
                            input.value = savedValue;
                        }

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
        };

        // Si cleanup existe y es una función, usarlo; de lo contrario, ejecutar directamente
        if (cleanup && typeof cleanup === 'function') {
            cleanup(syncDatePickers);
        } else {
            syncDatePickers();
        }
    });
});
