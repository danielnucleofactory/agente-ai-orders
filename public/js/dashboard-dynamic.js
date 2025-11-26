// Dashboard dynamic functionality
class DashboardManager {
    constructor() {
        this.charts = {}; // Store chart instances
        this.initialized = false;
        this.init();
    }

    init() {
        console.log('DashboardManager initializing...');
        this.setupEventListeners();

        // Only initialize trend table if we have data and haven't initialized yet
        if (!this.initialized && window.dashboardData) {
            this.initializeTrendTable();
            this.initialized = true;
        }

        // Initialize panel filters if available
        if (window.filterOptions) {
            this.initializePanelFilters(window.filterOptions);
        }
    }

    setupEventListeners() {
        // Form submission for filters
        const form = document.getElementById('dashboard-filters');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.applyFilters();
            });
        }

        // Apply filters button from panel
        const applyFiltersBtn = document.getElementById('apply-filters-btn');
        if (applyFiltersBtn) {
            applyFiltersBtn.addEventListener('click', () => {
                this.applyFilters();
            });
        }

        // Export button
        const exportBtn = document.getElementById('export-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => {
                this.exportData();
            });
        }

        // Modal close events
        this.setupModalEvents();
    }

    setupModalEvents() {
        const closeSuccessBtn = document.getElementById('closeSuccessBtn');
        const closeErrorBtn = document.getElementById('closeErrorBtn');
        const successModal = document.getElementById('successModal');
        const errorModal = document.getElementById('errorModal');

        if (closeSuccessBtn) {
            closeSuccessBtn.addEventListener('click', () => {
                this.hideModal('successModal');
            });
        }

        if (closeErrorBtn) {
            closeErrorBtn.addEventListener('click', () => {
                this.hideModal('errorModal');
            });
        }

        // Close modals when clicking outside
        if (successModal) {
            successModal.addEventListener('click', (e) => {
                if (e.target.id === 'successModal') {
                    this.hideModal('successModal');
                }
            });
        }

        if (errorModal) {
            errorModal.addEventListener('click', (e) => {
                if (e.target.id === 'errorModal') {
                    this.hideModal('errorModal');
                }
            });
        }

        // Close modals with ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hideModal('successModal');
                this.hideModal('errorModal');
            }
        });
    }

    async applyFilters() {
        try {
            console.log('Applying filters...');
            this.showLoading();

            // Recopilar filtros del formulario principal (si existe)
            const form = document.getElementById('dashboard-filters');
            const searchParams = new URLSearchParams();
            
            if (form) {
                const formData = new FormData(form);
                for (const [key, value] of formData.entries()) {
                    if (value) {
                        searchParams.append(key, value);
                    }
                }
            }

            // Recopilar filtros del panel de filtros lateral
            this.collectPanelFilters(searchParams);

            console.log('Sending request to:', `/dashboard/data?${searchParams.toString()}`);

            const response = await fetch(`/dashboard/data?${searchParams.toString()}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json',
                }
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Response not ok:', response.status, errorText);
                throw new Error(`Network response was not ok: ${response.status}`);
            }

            const result = await response.json();
            console.log('AJAX Response:', result);

            if (result.success) {
                this.updateDashboard(result.data);
                // Update filter options if provided
                if (result.filterOptions) {
                    window.filterOptions = result.filterOptions;
                    this.initializePanelFilters(result.filterOptions);
                }
                // Update URL without page reload
                const url = new URL(window.location);
                for (const [key, value] of searchParams.entries()) {
                    if (value) {
                        url.searchParams.set(key, value);
                    } else {
                        url.searchParams.delete(key);
                    }
                }
                window.history.pushState({}, '', url);
            } else {
                throw new Error(result.message || 'Error al obtener los datos');
            }
        } catch (error) {
            console.error('Error applying filters:', error);
            this.showErrorModal('Error al aplicar filtros: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    collectPanelFilters(searchParams) {
        // Recopilar filtros de Vendor del panel
        const vendorSelect = document.querySelector('.filters-panel .multi-select[data-placeholder*="vendors"]');
        if (vendorSelect) {
            const selectedVendors = Array.from(vendorSelect.querySelectorAll('.multi-select-option.selected'))
                .map(opt => opt.dataset.value)
                .filter(Boolean);
            if (selectedVendors.length > 0) {
                selectedVendors.forEach(vendorId => {
                    searchParams.append('vendor_id[]', vendorId);
                });
            }
        }

        // Recopilar filtros de Hub del panel
        const hubSelect = document.querySelector('.filters-panel .multi-select[data-placeholder*="hubs"]');
        if (hubSelect) {
            const selectedHubs = Array.from(hubSelect.querySelectorAll('.multi-select-option.selected'))
                .map(opt => opt.dataset.value)
                .filter(Boolean);
            if (selectedHubs.length > 0) {
                selectedHubs.forEach(hubId => {
                    searchParams.append('hub_id[]', hubId);
                });
            }
        }

        // Recopilar filtros de Etapa del panel
        const stageSelect = document.querySelector('.filters-panel .multi-select[data-placeholder*="etapas"]');
        if (stageSelect) {
            const selectedStages = Array.from(stageSelect.querySelectorAll('.multi-select-option.selected'))
                .map(opt => opt.dataset.value)
                .filter(Boolean);
            if (selectedStages.length > 0) {
                selectedStages.forEach(stage => {
                    searchParams.append('stage[]', stage);
                });
            }
        }

        // Recopilar checkboxes de filtros adicionales
        const poRetrasoCl = document.getElementById('filter-po-retraso-cl');
        if (poRetrasoCl && poRetrasoCl.checked) {
            searchParams.append('po_retraso_cl', '1');
        }

        const poAdelantoCl = document.getElementById('filter-po-adelanto-cl');
        if (poAdelantoCl && poAdelantoCl.checked) {
            searchParams.append('po_adelanto_cl', '1');
        }

        const indicadorCapacidad = document.getElementById('filter-indicador-capacidad');
        if (indicadorCapacidad && indicadorCapacidad.checked) {
            searchParams.append('indicador_capacidad', '1');
        }
    }

    async exportData() {
        try {
            console.log('Exporting data...');
            this.showLoading();

            // La exportación de la tabla de tendencias no requiere filtros
            // Solo necesita el año actual y la compañía del usuario (manejado en el backend)
            const response = await fetch('/dashboard/export', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'text/csv',
                }
            });

            if (!response.ok) {
                throw new Error('Error al exportar los datos');
            }

            // Create blob and download
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');

            // Get filename from response headers if available
            const contentDisposition = response.headers.get('Content-Disposition');
            let filename = 'dashboard_export.csv';
            if (contentDisposition) {
                const matches = /filename="(.+)"/.exec(contentDisposition);
                if (matches) {
                    filename = matches[1];
                }
            }

            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

            this.showModal('successModal');
        } catch (error) {
            console.error('Error exporting data:', error);
            this.showErrorModal('Error al exportar los datos: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    updateDashboard(data) {
        console.log('Updating dashboard with data:', data);

        // Update metrics
        this.updateMetrics(data.metrics);

        // Update trend table
        this.updateTrendTable(data.trend_table);

        // Initialize panel filters if filterOptions are available
        if (window.filterOptions) {
            this.initializePanelFilters(window.filterOptions);
        }
    }

    initializePanelFilters(filterOptions) {
        // Initialize Vendor multi-select
        const vendorSelect = document.querySelector('.filters-panel .multi-select[data-placeholder*="vendors"]');
        if (vendorSelect && filterOptions.vendors) {
            this.populateMultiSelect(vendorSelect, filterOptions.vendors, 'id', 'name');
        }

        // Initialize Hub multi-select
        const hubSelect = document.querySelector('.filters-panel .multi-select[data-placeholder*="hubs"]');
        if (hubSelect && filterOptions.hubs) {
            this.populateMultiSelect(hubSelect, filterOptions.hubs, 'id', 'name');
        }

        // Initialize Stage multi-select with the 7 stages
        const stageSelect = document.querySelector('.filters-panel .multi-select[data-placeholder*="etapas"]');
        if (stageSelect) {
            const stages = [
                { id: 'Producción', name: 'Producción' },
                { id: 'Booking', name: 'Booking' },
                { id: 'Transito', name: 'Transito' },
                { id: 'Puerto', name: 'Puerto' },
                { id: 'Recibiendo CDI', name: 'Recibiendo CDI' },
                { id: 'Ingresada', name: 'Ingresada' },
                { id: 'Anulada', name: 'Anulada' }
            ];
            this.populateMultiSelect(stageSelect, stages, 'id', 'name');
        }
    }

    populateMultiSelect(container, items, valueKey, labelKey) {
        const optionsBox = container.querySelector('.multi-select-options');
        if (!optionsBox) return;

        optionsBox.innerHTML = '';
        
        // Handle both arrays and objects (Laravel collections)
        const itemsArray = Array.isArray(items) ? items : Object.values(items);
        
        itemsArray.forEach(item => {
            const value = item[valueKey] || item.id || item;
            const label = item[labelKey] || item.name || item.short_text || item;
            const option = document.createElement('div');
            option.className = 'multi-select-option';
            option.dataset.value = value;
            option.style.display = 'flex';
            option.innerHTML = `
                <label style="display: flex; align-items: center; cursor: pointer; width: 100%; padding: 8px;">
                    <input type="checkbox" value="${value}" style="margin-right: 8px; cursor: pointer; accent-color: #1AAD8A;">
                    <span>${label}</span>
                </label>
            `;
            optionsBox.appendChild(option);
        });

        // The existing multi-select system in main.js will handle the event listeners
        // We just need to trigger a re-initialization or let the existing system handle it
        // The checkboxes will work with the existing event listeners from main.js
    }

    updateMultiSelectDisplay(container) {
        const selected = container.querySelectorAll('.multi-select-option.selected');
        const valueSpan = container.querySelector('.multi-select-value');
        if (!valueSpan) return;

        if (selected.length === 0) {
            const placeholder = container.getAttribute('data-placeholder') || 'Seleccionar';
            valueSpan.textContent = placeholder;
            valueSpan.style.color = '#AFAFAF';
        } else if (selected.length === 1) {
            valueSpan.textContent = selected[0].querySelector('span').textContent;
            valueSpan.style.color = '#1AAD8A';
        } else {
            valueSpan.textContent = `${selected.length} seleccionados`;
            valueSpan.style.color = '#1AAD8A';
        }
    }

    updateMetrics(metrics) {
        console.log('Updating metrics:', metrics);

        // Update total PO's
        const totalPosElement = document.getElementById('totalPosValue');
        if (totalPosElement) {
            totalPosElement.textContent = metrics.total_pos || 0;
        }

        // Update on-time percentage
        const onTimeElement = document.getElementById('onTimePercentageValue');
        if (onTimeElement) {
            onTimeElement.textContent = (metrics.on_time_percentage || 0) + '%';
        }

        // Update delayed percentage
        const delayedElement = document.getElementById('delayedPercentageValue');
        if (delayedElement) {
            delayedElement.textContent = (metrics.delayed_percentage || 0) + '%';
        }
    }

    updateTrendTable(trendData) {
        try {
            console.log('Updating trend table:', trendData);

            const tbody = document.getElementById('trendTableBody');
            if (!tbody) {
                console.warn('Trend table body not found');
                return;
            }

            if (!trendData || !trendData.categories) {
                tbody.innerHTML = '<tr><td colspan="13" class="text-center text-gray-500">Sin datos disponibles</td></tr>';
                return;
            }

            // Update year in header if needed
            const yearElements = document.querySelectorAll('.year, #currentYear');
            yearElements.forEach(el => {
                if (trendData.year) {
                    el.textContent = trendData.year;
                }
            });

            // Define category order - 7 etapas del Kanban
            const categoryOrder = [
                'Producción',
                'Booking',
                'Transito',
                'Puerto',
                'Recibiendo CDI',
                'Ingresada',
                'Anulada'
            ];

            // Build table rows
            let tableHTML = '';
            categoryOrder.forEach(categoryName => {
                const categoryData = trendData.categories[categoryName] || {};
                tableHTML += '<tr>';
                tableHTML += `<td style="padding: 12px; border: 1px solid #e5e7eb; font-weight: 500; color: #374151; width: 15%;">${categoryName}</td>`;
                
                // Add data for each month (1-12) - mostrar conteos enteros
                for (let month = 1; month <= 12; month++) {
                    const value = categoryData[month.toString()] || 0;
                    const displayValue = value === 0 ? '-' : value.toString();
                    tableHTML += `<td style="padding: 12px; text-align: center; border: 1px solid #e5e7eb; color: #374151; width: calc(85% / 12);">${displayValue}</td>`;
                }
                
                tableHTML += '</tr>';
            });

            tbody.innerHTML = tableHTML;
        } catch (error) {
            console.error('Error updating trend table:', error);
        }
    }

    generateColors(count) {
        const colors = [
            '#127A62', '#1AAD8A', '#28C7A1', '#36D9B2',
            '#45E6BF', '#55F2CD', '#ff3459', '#f46844'
        ];

        while (colors.length < count) {
            colors.push(...colors);
        }

        return colors.slice(0, count);
    }

    initializeTrendTable() {
        try {
            console.log('Initializing trend table...');

            if (window.dashboardData && window.dashboardData.trend_table) {
                this.updateTrendTable(window.dashboardData.trend_table);
                console.log('Trend table initialized successfully');
            } else {
                console.warn('No trend table data available for initialization');
            }
        } catch (error) {
            console.error('Error initializing trend table:', error);
        }
    }

    showLoading() {
        const loading = document.getElementById('loading-indicator');
        if (loading) {
            loading.classList.remove('hidden');
        }
    }

    hideLoading() {
        const loading = document.getElementById('loading-indicator');
        if (loading) {
            loading.classList.add('hidden');
        }
    }

    showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    hideModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    }

    showErrorModal(message) {
        const errorModal = document.getElementById('errorModal');
        const errorText = errorModal?.querySelector('.modal-text');
        if (errorText) {
            errorText.textContent = message;
        }
        this.showModal('errorModal');
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing Dashboard');
    window.dashboardManager = new DashboardManager();
});

// Also handle case where this script loads after DOM is ready
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    console.log('DOM already ready - Initializing Dashboard');
    window.dashboardManager = new DashboardManager();
}

