// Forecast dashboard dynamic functionality
class ForecastManager {
    constructor() {
        this.charts = {}; // Store chart instances
        this.initialized = false;
        this.activeFilters = {
            vendor_id: [],
            product_id: [],
            material_type: [],
            date_from: null,
            date_to: null,
        };
        this.init();
    }

    init() {
        console.log('ForecastManager initializing...');
        this.setupEventListeners();
        this.populateFilters();

        // Only initialize charts if we have data and haven't initialized yet
        if (!this.initialized && window.forecastData) {
            this.initializeCharts();
            this.updateClearFiltersButton();
            this.initialized = true;
        }
    }

    setupEventListeners() {
        // Export button
        const exportBtn = document.getElementById('export-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => {
                this.exportData();
            });
        }

        // Clear filters button
        const clearFiltersBtn = document.getElementById('clearFiltersBtn');
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', () => {
                this.clearAllFilters();
            });
        }

        // Accept button
        const aceptarBtn = document.querySelector('.btn-primary');
        if (aceptarBtn) {
            aceptarBtn.addEventListener('click', () => {
                this.applyTopFilters();
                this.updateForecastUI();
            });
        }

        // Date inputs
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        if (startDateInput) {
            startDateInput.addEventListener('change', () => {
                this.activeFilters.date_from = startDateInput.value || null;
                this.updateForecastUI();
            });
        }
        if (endDateInput) {
            endDateInput.addEventListener('change', () => {
                this.activeFilters.date_to = endDateInput.value || null;
                this.updateForecastUI();
            });
        }

        // Setup multi-select filters (using dashboard principal logic)
        this.setupMultiSelectFilters();
    }

    // Populate filters from backend data (using dashboard principal logic)
    populateFilters() {
        // Get filter options from window (passed from backend)
        if (!window.forecastData || !window.forecastData.filterOptions) {
            console.warn('Filter options not available');
            return;
        }

        const filterOptions = window.forecastData.filterOptions;

        // Vendor
        const vendorGroup = document.querySelector('.filter-group[data-filter="vendor"]');
        if (vendorGroup && filterOptions.vendors) {
            const optionsBox = vendorGroup.querySelector('.multi-select-options');
            while (optionsBox.firstChild) optionsBox.removeChild(optionsBox.firstChild);
            filterOptions.vendors.forEach(vendor => {
                const label = document.createElement('label');
                label.className = 'multi-select-option';
                label.innerHTML = `<input type="checkbox" value="${vendor.id}"> ${vendor.name}`;
                optionsBox.appendChild(label);
            });
        }

        // Product
        const productGroup = document.querySelector('.filter-group[data-filter="product"]');
        if (productGroup && filterOptions.products) {
            const optionsBox = productGroup.querySelector('.multi-select-options');
            while (optionsBox.firstChild) optionsBox.removeChild(optionsBox.firstChild);
            filterOptions.products.forEach(product => {
                const label = document.createElement('label');
                label.className = 'multi-select-option';
                label.innerHTML = `<input type="checkbox" value="${product.id}"> ${product.name} (${product.material_id})`;
                optionsBox.appendChild(label);
            });
        }

        // Material
        const materialGroup = document.querySelector('.filter-group[data-filter="material"]');
        if (materialGroup && filterOptions.materials) {
            const optionsBox = materialGroup.querySelector('.multi-select-options');
            while (optionsBox.firstChild) optionsBox.removeChild(optionsBox.firstChild);
            let materials = filterOptions.materials;
            if (materials && !Array.isArray(materials)) {
                materials = Object.values(materials);
            }
            if (materials && Array.isArray(materials)) {
                materials.forEach(material => {
                    const label = document.createElement('label');
                    label.className = 'multi-select-option';
                    label.innerHTML = `<input type="checkbox" value="${material}"> ${material}`;
                    optionsBox.appendChild(label);
                });
            }
        }
    }

    // Multi-select setup (using exact dashboard principal logic)
    setupMultiSelectFilters() {
        function closeAllDropdowns(except) {
            document.querySelectorAll('.multi-select-content').forEach(content => {
                if (content !== except) content.classList.remove('active');
            });
            document.querySelectorAll('.multi-select-trigger').forEach(trigger => {
                if (!except || trigger.parentElement.querySelector('.multi-select-content') !== except) {
                    trigger.classList.remove('active');
                }
            });
        }

        document.querySelectorAll('[data-multiselect]').forEach(ms => {
            const trigger = ms.querySelector('.multi-select-trigger');
            const content = ms.querySelector('.multi-select-content');
            const valueSpan = ms.querySelector('.multi-select-value');
            const searchInput = ms.querySelector('.multi-select-search-input');
            const optionsBox = ms.querySelector('.multi-select-options');
            const clearBtn = ms.querySelector('.multi-select-clear');
            const placeholder = ms.getAttribute('data-placeholder') || 'Seleccionar';

            // Abrir/cerrar dropdown
            trigger.addEventListener('click', e => {
                e.stopPropagation();
                const isActive = content.classList.contains('active');
                closeAllDropdowns(content);
                content.classList.toggle('active', !isActive);
                trigger.classList.toggle('active', !isActive);
                if (!isActive && searchInput) {
                    searchInput.value = '';
                    Array.from(optionsBox.children).forEach(opt => opt.style.display = 'flex');
                    searchInput.focus();
                }
            });

            // Búsqueda
            if (searchInput) {
                searchInput.addEventListener('input', e => {
                    const term = e.target.value.toLowerCase();
                    Array.from(optionsBox.children).forEach(opt => {
                        const label = opt.textContent.toLowerCase();
                        opt.style.display = label.includes(term) ? 'flex' : 'none';
                    });
                });
            }

            // Limpiar selección
            if (clearBtn) {
                clearBtn.addEventListener('click', e => {
                    e.stopPropagation();
                    const checkboxes = optionsBox.querySelectorAll('input[type="checkbox"]');
                    checkboxes.forEach(cb => cb.checked = false);
                    if (searchInput) {
                        searchInput.value = '';
                        Array.from(optionsBox.children).forEach(opt => opt.style.display = 'flex');
                    }
                    updateDisplay();
                    content.classList.remove('active');
                    trigger.classList.remove('active');
                });
            }

            // Cerrar al hacer click fuera
            document.addEventListener('click', e => {
                if (!ms.contains(e.target)) {
                    content.classList.remove('active');
                    trigger.classList.remove('active');
                }
            });

            function updateDisplay() {
                const checkboxes = optionsBox.querySelectorAll('input[type="checkbox"]');
                const selected = Array.from(checkboxes).filter(cb => cb.checked);
                if (selected.length === 0) {
                    valueSpan.textContent = placeholder;
                } else if (selected.length === 1) {
                    valueSpan.textContent = selected[0].parentElement.textContent.trim();
                } else {
                    valueSpan.textContent = `${selected.length} seleccionados`;
                }
            }

            // Listen for checkbox changes
            const observer = new MutationObserver(() => {
                updateDisplay();
            });
            observer.observe(optionsBox, { childList: true, subtree: true });

            updateDisplay();
        });
    }

    // Apply top filters (get values from multi-selects and dates)
    applyTopFilters() {
        // Get vendor filter values
        const vendorGroup = document.querySelector('.filter-group[data-filter="vendor"]');
        if (vendorGroup) {
            const checkedBoxes = vendorGroup.querySelectorAll('input[type="checkbox"]:checked');
            this.activeFilters.vendor_id = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
        }

        // Get product filter values
        const productGroup = document.querySelector('.filter-group[data-filter="product"]');
        if (productGroup) {
            const checkedBoxes = productGroup.querySelectorAll('input[type="checkbox"]:checked');
            this.activeFilters.product_id = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
        }

        // Get material filter values
        const materialGroup = document.querySelector('.filter-group[data-filter="material"]');
        if (materialGroup) {
            const checkedBoxes = materialGroup.querySelectorAll('input[type="checkbox"]:checked');
            this.activeFilters.material_type = Array.from(checkedBoxes).map(cb => cb.value);
        }

        this.updateClearFiltersButton();
    }

    // Clear all filters (using dashboard principal logic)
    clearAllFilters() {
        this.activeFilters.vendor_id = [];
        this.activeFilters.product_id = [];
        this.activeFilters.material_type = [];
        this.activeFilters.date_from = null;
        this.activeFilters.date_to = null;

        // Clear date inputs
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        if (startDateInput) startDateInput.value = '';
        if (endDateInput) endDateInput.value = '';

        // Clear all checkboxes
        document.querySelectorAll('[data-multiselect] input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
        });

        // Update displays
        document.querySelectorAll('[data-multiselect] .multi-select-value').forEach(valueSpan => {
            const trigger = valueSpan.closest('.multi-select-trigger');
            const placeholder = trigger.closest('[data-multiselect]').getAttribute('data-placeholder') || 'Seleccionar';
            valueSpan.textContent = placeholder;
        });

        this.updateForecastUI();
        this.updateClearFiltersButton();
    }

    // Update clear filters button visibility
    updateClearFiltersButton() {
        const clearBtn = document.getElementById('clearFiltersBtn');
        if (!clearBtn) return;

        const hasActiveFilters = (
            this.activeFilters.vendor_id.length > 0 ||
            this.activeFilters.product_id.length > 0 ||
            this.activeFilters.material_type.length > 0 ||
            this.activeFilters.date_from ||
            this.activeFilters.date_to
        );

        clearBtn.style.display = hasActiveFilters ? 'block' : 'none';
    }

    // Handle vendor click from charts (using dashboard principal logic)
    handleVendorClick(vendorName) {
        console.log('Vendor clicked:', vendorName);
        
        // Find vendor ID from filter options
        const filterOptions = window.forecastData?.filterOptions;
        if (!filterOptions?.vendors) return;

        const vendor = filterOptions.vendors.find(v => v.name === vendorName);
        if (!vendor) {
            console.warn('Vendor not found in filter options:', vendorName);
            return;
        }

        // Toggle vendor in activeFilters
        const vendorId = vendor.id;
        if (this.activeFilters.vendor_id.includes(vendorId)) {
            this.activeFilters.vendor_id = this.activeFilters.vendor_id.filter(id => id !== vendorId);
        } else {
            this.activeFilters.vendor_id.push(vendorId);
        }

        // Update checkbox state
        const vendorGroup = document.querySelector('.filter-group[data-filter="vendor"]');
        if (vendorGroup) {
            const checkbox = vendorGroup.querySelector(`input[value="${vendorId}"]`);
            if (checkbox) {
                checkbox.checked = this.activeFilters.vendor_id.includes(vendorId);
            }
        }

        this.updateForecastUI();
        this.updateClearFiltersButton();
    }

    // Handle month click from bar chart (using dashboard principal logic)
    handleMonthClick(monthString) {
        console.log('Month clicked:', monthString);
        
        // Convert month string (YYYY-MM) to date range
        try {
            const [year, month] = monthString.split('-');
            const firstDay = `${year}-${month}-01`;
            const lastDay = new Date(parseInt(year), parseInt(month), 0).getDate();
            const lastDayStr = `${year}-${month}-${lastDay.toString().padStart(2, '0')}`;
            
            // Update active filters
            this.activeFilters.date_from = firstDay;
            this.activeFilters.date_to = lastDayStr;
            
            // Update date inputs
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            
            if (startDateInput) startDateInput.value = firstDay;
            if (endDateInput) endDateInput.value = lastDayStr;
            
            this.updateForecastUI();
            this.updateClearFiltersButton();
        } catch (error) {
            console.error('Error parsing month string:', error);
        }
    }

    // Handle material click from treemap (using dashboard principal logic)
    handleMaterialClick(materialName) {
        console.log('Material clicked:', materialName);

        // Toggle material in activeFilters
        if (this.activeFilters.material_type.includes(materialName)) {
            this.activeFilters.material_type = this.activeFilters.material_type.filter(m => m !== materialName);
        } else {
            this.activeFilters.material_type.push(materialName);
        }

        // Update checkbox state
        const materialGroup = document.querySelector('.filter-group[data-filter="material"]');
        if (materialGroup) {
            const checkbox = materialGroup.querySelector(`input[value="${materialName}"]`);
            if (checkbox) {
                checkbox.checked = this.activeFilters.material_type.includes(materialName);
            }
        }

        this.updateForecastUI();
        this.updateClearFiltersButton();
    }

    // Clear all filters
    clearAllFilters() {
        console.log('Clearing all filters...');
        
        // Clear date filters
        const dateFromInput = document.querySelector('input[name="date_from"]');
        const dateToInput = document.querySelector('input[name="date_to"]');
        
        if (dateFromInput) dateFromInput.value = '';
        if (dateToInput) dateToInput.value = '';

        // Clear all multi-select filters
        const allCheckboxes = document.querySelectorAll('.multi-select input[type="checkbox"]');
        allCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });

        // Update all multi-select displays
        const multiSelects = document.querySelectorAll('.multi-select');
        multiSelects.forEach(select => {
            this.updateMultiSelectDisplay(select);
        });
        
        // Close all dropdowns
        this.closeAllMultiSelects();

        // Apply cleared filters (reload with no filters)
        this.applyFilters();
    }

    // Update forecast UI (using dashboard principal logic)
    async updateForecastUI() {
        try {
            console.log('Updating forecast UI...');
            this.showLoading();

            // Build query parameters from active filters
            const params = new URLSearchParams();
            
            if (this.activeFilters.date_from) params.append('date_from', this.activeFilters.date_from);
            if (this.activeFilters.date_to) params.append('date_to', this.activeFilters.date_to);
            
            if (this.activeFilters.vendor_id.length > 0) {
                this.activeFilters.vendor_id.forEach(id => params.append('vendor_id[]', id.toString()));
            }
            
            if (this.activeFilters.product_id.length > 0) {
                this.activeFilters.product_id.forEach(id => params.append('product_id[]', id.toString()));
            }
            
            if (this.activeFilters.material_type.length > 0) {
                this.activeFilters.material_type.forEach(material => params.append('material_type[]', material));
            }

            console.log('Sending request with filters:', this.activeFilters);

            const response = await fetch(`/products/forecast-graph/data?${params.toString()}`, {
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
            console.log('Forecast AJAX Response:', result);

            if (result.success) {
                this.updateForecast(result.data);
                // Update URL without page reload
                const url = new URL(window.location);
                url.search = params.toString();
                window.history.pushState({}, '', url);
            } else {
                throw new Error(result.message || 'Error al obtener los datos del forecast');
            }
        } catch (error) {
            console.error('Error updating forecast UI:', error);
            this.showErrorModal('Error al actualizar los datos: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    async exportData() {
        try {
            console.log('Exporting forecast data...');
            this.showLoading();

            const form = document.getElementById('forecast-filters');
            const formData = new FormData(form);
            const searchParams = new URLSearchParams(formData);

            const response = await fetch(`/products/forecast-graph/export?${searchParams.toString()}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                }
            });

            if (!response.ok) {
                throw new Error('Error al exportar los datos del forecast');
            }

            // Create blob and download
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');

            // Get filename from response headers if available
            const contentDisposition = response.headers.get('Content-Disposition');
            let filename = 'forecast_export.csv';
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

            console.log('Forecast export completed successfully');
        } catch (error) {
            console.error('Error exporting forecast data:', error);
            this.showErrorModal('Error al exportar los datos: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    updateForecast(data) {
        console.log('Updating forecast with data:', data);

        // Update metrics
        this.updateMetrics(data.metrics);

        // Update charts
        this.updateCharts(data.charts);

        // Update forecast table
        this.updateForecastTable(data.forecast_table);
    }

    updateMetrics(metrics) {
        console.log('Updating forecast metrics:', metrics);

        const elements = {
            'total-pos': metrics.total_pos,
            'on-time-percentage': metrics.on_time_percentage + '%',
            'delayed-percentage': metrics.delayed_percentage + '%',
            'total-amount': '$' + new Intl.NumberFormat().format(metrics.total_amount)
        };

        for (const [id, value] of Object.entries(elements)) {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            } else {
                console.warn(`Element with id '${id}' not found`);
            }
        }
    }

    updateCharts(chartsData) {
        console.log('Updating forecast charts with data:', chartsData);

        // Update treemap (preserve existing design)
        if (chartsData.material_deviation) {
            this.updateTreemap(chartsData.material_deviation);
        }

        // Update bar chart
        if (chartsData.monthly_kgs) {
            this.updateBarChart(chartsData.monthly_kgs);
        }

        // Update vendor pie chart
        if (chartsData.vendor_deviation) {
            this.updateVendorChart(chartsData.vendor_deviation);
        }
    }

    updateTreemap(data) {
        try {
            console.log('Updating treemap with data:', data);

            const container = document.getElementById('treemapContainer');
            if (!container) {
                console.warn('Treemap container not found');
                return;
            }

            // Clear existing treemap
            container.innerHTML = '';

            if (!data || data.length === 0) {
                container.innerHTML = '<div class="text-gray-500 text-sm">Sin datos disponibles</div>';
                return;
            }

            // Create treemap items based on data
            const colors = this.generateColors(data.length);

            data.forEach((item, index) => {
                const element = document.createElement('div');
                element.className = 'treemap-item';
                element.style.backgroundColor = colors[index];
                element.style.cursor = 'pointer';
                element.textContent = item.name;

                // Apply opacity effect based on material filters (using dashboard principal logic)
                if (this.activeFilters.material_type.length > 0) {
                    const isSelected = this.activeFilters.material_type.includes(item.name);
                    element.style.opacity = isSelected ? '1' : '0.3';
                } else {
                    element.style.opacity = '1';
                }

                // Calculate size based on deviation value
                const size = this.calculateTreemapSize(item.value, data);
                element.style.gridArea = size.gridArea;

                // Add click handler for material filtering
                element.addEventListener('click', () => {
                    this.handleMaterialClick(item.name);
                });

                container.appendChild(element);
            });

        } catch (error) {
            console.error('Error updating treemap:', error);
        }
    }

    calculateTreemapSize(value, allData) {
        // Simple implementation - in a real scenario, you'd use a proper treemap algorithm
        const maxValue = Math.max(...allData.map(d => d.value));
        const ratio = value / maxValue;

        if (ratio > 0.7) {
            return { gridArea: "1 / 1 / 3 / 3" }; // large
        } else if (ratio > 0.4) {
            return { gridArea: "1 / 3 / 2 / 4" }; // medium
        } else {
            return { gridArea: "2 / 3 / 3 / 4" }; // small
        }
    }

    updateBarChart(data) {
        try {
            console.log('Updating bar chart with data:', data);

            const canvas = document.getElementById('barChart');
            if (!canvas) {
                console.warn('Bar chart canvas not found');
                return;
            }

            // Destroy existing chart if it exists
            if (this.charts.barChart) {
                this.charts.barChart.destroy();
            }

            if (!data || data.length === 0) {
                console.log('No data for bar chart');
                return;
            }

            const ctx = canvas.getContext('2d');

            // For temporal bar chart, keep normal colors but respond to clicks
            // Bar chart shows monthly data - doesn't need opacity effects from other filters
            const backgroundColors = data.map(() => '#565AFF');
            
            this.charts.barChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(item => item.month),
                    datasets: [{
                        label: 'Cantidad kgs',
                        data: data.map(item => item.total_kgs),
                        backgroundColor: backgroundColors,
                        borderWidth: 0,
                        barThickness: 80,
                    }]
                },
                options: {
                    responsive: false,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    onClick: (event, elements) => {
                        if (elements.length > 0) {
                            const dataIndex = elements[0].index;
                            const monthData = data[dataIndex];
                            this.handleMonthClick(monthData.month);
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => (value / 1000).toFixed(0) + 'k',
                                font: {
                                    family: 'Inter',
                                    size: 12
                                }
                            },
                            grid: {
                                display: true,
                                color: '#f3f4f6'
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    family: 'Inter',
                                    size: 12
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

        } catch (error) {
            console.error('Error updating bar chart:', error);
        }
    }

    updateVendorChart(data) {
        try {
            console.log('Updating vendor chart with data:', data);

            const canvas = document.getElementById('pieChart');
            if (!canvas) {
                console.warn('Pie chart canvas not found');
                return;
            }

            // Destroy existing chart if it exists
            if (this.charts.pieChart) {
                this.charts.pieChart.destroy();
            }

            if (!data || data.length === 0) {
                console.log('No data for vendor chart');
                return;
            }

            const ctx = canvas.getContext('2d');
            const colors = this.generateColors(data.length);

            // Apply opacity effect based on vendor filters (using dashboard principal logic)
            let backgroundColors = colors.slice();
            if (this.activeFilters.vendor_id.length > 0) {
                const filterOptions = window.forecastData?.filterOptions;
                if (filterOptions?.vendors) {
                    backgroundColors = data.map((item, index) => {
                        const vendor = filterOptions.vendors.find(v => v.name === item.name);
                        const isSelected = vendor && this.activeFilters.vendor_id.includes(vendor.id);
                        return isSelected ? colors[index] : colors[index] + '33'; // Add opacity to non-selected
                    });
                }
            }

            this.charts.pieChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(item => item.name),
                    datasets: [{
                        data: data.map(item => item.percentage),
                        backgroundColor: backgroundColors,
                        borderWidth: 0,
                        cutout: '40%'
                    }]
                },
                options: {
                    responsive: false,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    onClick: (event, elements) => {
                        if (elements.length > 0) {
                            const dataIndex = elements[0].index;
                            const vendorName = data[dataIndex].name;
                            this.handleVendorClick(vendorName);
                        }
                    }
                }
            });

            // Update vendor legend with click handlers
            this.updateVendorLegend(data, colors);

        } catch (error) {
            console.error('Error updating vendor chart:', error);
        }
    }

    updateVendorLegend(data, colors) {
        try {
            const legendContainer = document.getElementById('vendorLegend');
            if (!legendContainer) {
                console.warn('Vendor legend container not found');
                return;
            }

            legendContainer.innerHTML = '';

            data.forEach((item, index) => {
                const vendorItem = document.createElement('div');
                vendorItem.className = 'vendor-item';
                vendorItem.style.cursor = 'pointer';
                
                // Apply opacity effect to legend based on vendor filters (using dashboard principal logic)
                if (this.activeFilters.vendor_id.length > 0) {
                    const filterOptions = window.forecastData?.filterOptions;
                    if (filterOptions?.vendors) {
                        const vendor = filterOptions.vendors.find(v => v.name === item.name);
                        const isSelected = vendor && this.activeFilters.vendor_id.includes(vendor.id);
                        vendorItem.style.opacity = isSelected ? '1' : '0.3';
                    }
                } else {
                    vendorItem.style.opacity = '1';
                }
                
                vendorItem.innerHTML = `
                    <div class="vendor-info">
                        <div class="vendor-color" style="background-color: ${colors[index]}"></div>
                        <span class="vendor-name">${item.name}</span>
                    </div>
                    <div class="vendor-stats">
                        <span class="vendor-amount">$${new Intl.NumberFormat().format(item.value)}</span>
                        <div class="vendor-percentage">${item.percentage}%</div>
                    </div>
                `;
                
                // Add click handler to legend items
                vendorItem.addEventListener('click', () => {
                    this.handleVendorClick(item.name);
                });
                
                legendContainer.appendChild(vendorItem);
            });

        } catch (error) {
            console.error('Error updating vendor legend:', error);
        }
    }

    updateForecastTable(tableData) {
        try {
            console.log('Updating forecast table:', tableData);

            const tbody = document.getElementById('forecastTableBody');
            if (!tbody) {
                console.warn('Forecast table body not found');
                return;
            }

            if (!tableData || tableData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-gray-500">Sin datos disponibles</td></tr>';
                return;
            }

            tbody.innerHTML = tableData.map(row => {
                const deviationValue = parseFloat(row.deviation_kg.replace(',', '.'));
                let deviationClass = 'deviation-zero';
                if (deviationValue > 0) {
                    deviationClass = 'deviation-positive';
                } else if (deviationValue < 0) {
                    deviationClass = 'deviation-negative';
                }

                return `
                    <tr>
                        <td class="material-code">${row.material}</td>
                        <td class="text-right">${row.forecast_kg}</td>
                        <td class="text-right">${row.actual_kg}</td>
                        <td class="text-right ${deviationClass}">${row.deviation_kg}</td>
                    </tr>
                `;
            }).join('');

        } catch (error) {
            console.error('Error updating forecast table:', error);
        }
    }

    generateColors(count) {
        const colors = [
            '#565aff', '#7288ff', '#9aabff', '#aebbff',
            '#c9cfff', '#f46844', '#5ae7f4', '#5dd595'
        ];

        while (colors.length < count) {
            colors.push(...colors);
        }

        return colors.slice(0, count);
    }



    initializeCharts() {
        try {
            console.log('Initializing forecast charts...');

            if (window.forecastData && window.forecastData.charts) {
                this.updateCharts(window.forecastData.charts);
            }

            if (window.forecastData && window.forecastData.forecast_table) {
                this.updateForecastTable(window.forecastData.forecast_table);
            }

            console.log('All forecast charts initialized successfully');
        } catch (error) {
            console.error('Error initializing forecast charts:', error);
        }
    }

    showLoading() {
        // Create loading indicator if it doesn't exist
        let loading = document.getElementById('loading-indicator');
        if (!loading) {
            loading = document.createElement('div');
            loading.id = 'loading-indicator';
            loading.className = 'fixed inset-0 flex items-center justify-center bg-gray-600 bg-opacity-50';
            loading.innerHTML = `
                <div class="p-4 bg-white rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 -ml-1 text-blue-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Cargando datos...
                    </div>
                </div>
            `;
            document.body.appendChild(loading);
        }
        loading.classList.remove('hidden');
    }

    hideLoading() {
        const loading = document.getElementById('loading-indicator');
        if (loading) {
            loading.classList.add('hidden');
        }
    }

    showErrorModal(message) {
        // Simple error alert for now - can be enhanced with modals later
        alert(message);
    }
}

// Initialize forecast manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing ForecastManager');
    new ForecastManager();
});

// Also handle case where this script loads after DOM is ready
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    console.log('DOM already ready - Initializing ForecastManager');
    new ForecastManager();
}
