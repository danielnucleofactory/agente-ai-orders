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
    Alpine.data('datePicker', (modelValue, modelName = null) => ({
        value: modelValue,
        modelName,
        fp: null,

        syncLivewireValue(isoValue) {
            if (!this.modelName || !this.$wire || typeof this.$wire.set !== 'function') {
                return;
            }

            this.$wire.set(this.modelName, isoValue);
        },

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

            const syncValueFromPicker = (rawValue = null) => {
                if (!this.fp) return;

                const normalizedRawValue = typeof rawValue === 'string' ? rawValue.trim() : rawValue;
                let isoValue = null;

                if (this.fp.selectedDates.length > 0) {
                    isoValue = this.fp.formatDate(this.fp.selectedDates[0], 'Y-m-d');
                } else if (normalizedRawValue) {
                    const parsedDate =
                        this.fp.parseDate(normalizedRawValue, displayFormat) ||
                        this.fp.parseDate(normalizedRawValue, 'Y-m-d');

                    if (parsedDate) {
                        isoValue = this.fp.formatDate(parsedDate, 'Y-m-d');
                        this.fp.setDate(parsedDate, false);
                    }
                }

                this.value = isoValue;
                this.syncLivewireValue(isoValue);

                if (!isoValue && this.fp.altInput) {
                    this.fp.altInput.value = '';
                    this.fp.altInput.placeholder = placeholder;
                }
            };

            const scheduleSyncFromAltInput = () => {
                if (!this.fp || !this.fp.altInput) return;

                window.requestAnimationFrame(() => {
                    syncValueFromPicker(this.fp.altInput.value);
                });
            };

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
                    this.syncLivewireValue(this.value);
                },
                onClose: (selectedDates, dateStr) => {
                    syncValueFromPicker(dateStr);
                },
                onValueUpdate: (selectedDates, dateStr) => {
                    syncValueFromPicker(dateStr);
                },
            });

            // Configurar placeholder en el input visible (altInput)
            if (this.fp.altInput) {
                this.fp.altInput.placeholder = placeholder;
                this.fp.altInput.addEventListener('blur', () => syncValueFromPicker(this.fp.altInput.value));
                this.fp.altInput.addEventListener('change', () => syncValueFromPicker(this.fp.altInput.value));
                this.fp.altInput.addEventListener('input', scheduleSyncFromAltInput);
                this.fp.altInput.addEventListener('paste', scheduleSyncFromAltInput);
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
// document.addEventListener('DOMContentLoaded', function () {
//     const ensureNavLoadingOverlay = () => {
//         let overlay = document.getElementById('nav-loading-overlay');
//         if (overlay) {
//             return overlay;
//         }

//         overlay = document.createElement('div');
//         overlay.id = 'nav-loading-overlay';
//         overlay.style.cssText = [
//             'position: fixed',
//             'inset: 0',
//             'z-index: 9999',
//             'display: none',
//             'align-items: center',
//             'justify-content: center',
//             'background: rgba(247,247,247,0.92)',
//             'backdrop-filter: blur(1px)',
//         ].join(';');
//         overlay.innerHTML = `
//             <div style="display:flex;flex-direction:column;align-items:center;gap:10px;padding:16px 20px;border-radius:10px;background:#fff;border:1px solid #e5e7eb;box-shadow:0 8px 24px rgba(0,0,0,.08);">
//                 <div style="width:28px;height:28px;border:3px solid #d1fae5;border-top-color:#1AAD8A;border-radius:9999px;animation:navLoaderSpin .8s linear infinite;"></div>
//                 <p id="nav-loading-overlay-message" style="margin:0;color:#374151;font-size:13px;font-weight:600;">Cargando...</p>
//             </div>
//         `;
//         document.body.appendChild(overlay);

//         if (!document.getElementById('nav-loading-overlay-style')) {
//             const style = document.createElement('style');
//             style.id = 'nav-loading-overlay-style';
//             style.textContent = '@keyframes navLoaderSpin { to { transform: rotate(360deg); } }';
//             document.head.appendChild(style);
//         }

//         return overlay;
//     };

//     const showNavLoadingOverlay = (message) => {
//         const overlay = ensureNavLoadingOverlay();
//         const messageEl = document.getElementById('nav-loading-overlay-message');
//         if (messageEl) {
//             messageEl.textContent = message || 'Cargando...';
//         }
//         overlay.style.display = 'flex';
//     };
//     const hideNavLoadingOverlay = () => {
//         const overlay = document.getElementById('nav-loading-overlay');
//         if (overlay) {
//             overlay.style.display = 'none';
//         }
//     };
//     window.showNavLoadingOverlay = showNavLoadingOverlay;

//     const isPurchaseOrdersRoute = (urlString) => {
//         try {
//             const url = new URL(urlString, window.location.origin);
//             if (url.origin !== window.location.origin) return false;
//             return url.pathname === '/purchase-orders' || url.pathname.startsWith('/purchase-orders/');
//         } catch (e) {
//             return false;
//         }
//     };

//     // Breadcrumbs, links internos u otros accesos (además del sidebar explícito).
//     document.addEventListener('click', (event) => {
//         const link = event.target?.closest?.('a[href]');
//         if (!link) return;
//         if (link.dataset.navLoading === 'true') return; // ya tiene handler específico
//         if (event.defaultPrevented) return;
//         if (event.button !== 0) return; // solo click izquierdo
//         if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
//         if (link.target && link.target !== '_self') return;
//         if (link.hasAttribute('download')) return;

//         const href = link.getAttribute('href');
//         if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
//         if (!isPurchaseOrdersRoute(href)) return;

//         showNavLoadingOverlay('Cargando tablero de órdenes...');
//     }, true);

//     // Botones atrás/adelante del navegador.
//     window.addEventListener('popstate', () => {
//         if (isPurchaseOrdersRoute(window.location.href)) {
//             showNavLoadingOverlay('Cargando tablero de órdenes...');
//         }
//     });

//     // Cuando se vuelve con atrás/adelante y el destino es Kanban de POs, mostrar feedback
//     // aunque la navegación provenga del historial del navegador (incluye bfcache).
//     window.addEventListener('pageshow', (event) => {
//         const navigationEntry = performance.getEntriesByType('navigation')[0];
//         const isBackForward = event.persisted || navigationEntry?.type === 'back_forward';
//         if (!isBackForward) return;
//         if (!isPurchaseOrdersRoute(window.location.href)) return;

//         showNavLoadingOverlay('Cargando tablero de órdenes...');
//         setTimeout(() => {
//             hideNavLoadingOverlay();
//         }, 900);
//     });

//     document.querySelectorAll('a[data-nav-loading="true"]').forEach((link) => {
//         link.addEventListener('click', (event) => {
//             const href = link.getAttribute('href');
//             if (!href || href.startsWith('#')) {
//                 return;
//             }

//             event.preventDefault();
//             showNavLoadingOverlay(link.dataset.navLoadingMessage);
//             setTimeout(() => {
//                 window.location.href = href;
//             }, 30);
//         });
//     });

//     const sidebar = document.querySelector('.main-sidebar');
//     if (sidebar) {
//         if (localStorage.getItem('sidebarExpanded') === 'true') {
//             sidebar.classList.add('sidebar-expanded');
//         } else {
//             sidebar.classList.remove('sidebar-expanded');
//         }

//         window.toggleSidebarSimple = function () {
//             sidebar.classList.toggle('sidebar-expanded');
//             localStorage.setItem('sidebarExpanded', sidebar.classList.contains('sidebar-expanded'));
//         };
//     }
// });

function initSidebar() {
    const sidebar = document.querySelector('.main-sidebar');
    if (sidebar) {
        if (localStorage.getItem('sidebarExpanded') === 'true') {
            sidebar.classList.add('sidebar-expanded');
        } else {
            sidebar.classList.remove('sidebar-expanded');
        }
    }
}

/**
 * Overlay de carga del tablero Kanban (PO): mismo elemento en /purchase-orders y vistas con tablero.
 */
function poKanbanOverlayShow(message) {
    const overlay = document.querySelector('.po-kanban-loading-overlay');
    const msgEl = document.querySelector('.po-kanban-loading-message');
    if (!overlay) {
        return;
    }
    if (msgEl && typeof message === 'string' && message.length > 0) {
        msgEl.textContent = message;
    }
    overlay.classList.remove('opacity-0', 'pointer-events-none');
    overlay.classList.add('opacity-100', 'po-kanban-loading-overlay-visible');
    overlay.setAttribute('aria-busy', 'true');
}

function poKanbanOverlayHide() {
    const overlay = document.querySelector('.po-kanban-loading-overlay');
    if (!overlay) {
        return;
    }
    overlay.classList.remove('opacity-100', 'po-kanban-loading-overlay-visible');
    overlay.classList.add('opacity-0', 'pointer-events-none');
    overlay.setAttribute('aria-busy', 'false');
}

window.poKanbanOverlayShow = poKanbanOverlayShow;
window.poKanbanOverlayHide = poKanbanOverlayHide;

function revealDeferredContent(el) {
    if (!el) {
        return;
    }
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            el.classList.remove('opacity-0', 'pointer-events-none');
            el.classList.add('opacity-100');
        });
    });
}

/**
 * Vistas con tablero Kanban PO: tras DOMContentLoaded, asegura overlay visible y mensaje
 * hasta que Livewire lazy on-load ejecute mount → loadData (que oculta el overlay en finally).
 * El arranque real del componente lo dispara Livewire (x-init en el placeholder lazy on-load).
 */
function initPoKanbanBoardAfterDomReady() {
    const host = document.querySelector('[data-po-kanban-board-host]');
    if (!host) {
        return;
    }
    window.poKanbanOverlayShow?.('Cargando tablero de órdenes…');
}

document.addEventListener('DOMContentLoaded', function () {
    initSidebar();
    initPoKanbanBoardAfterDomReady();
    const poIndexShell = document.getElementById('po-index-content-defer');
    if (poIndexShell) {
        revealDeferredContent(poIndexShell);
    }
    const poKanbanStandalone = document.querySelector('[data-po-kanban-defer-main]');
    if (poKanbanStandalone) {
        revealDeferredContent(poKanbanStandalone);
    }
});
