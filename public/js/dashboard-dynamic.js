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

        // Only initialize charts if we have data and haven't initialized yet
        if (!this.initialized && window.dashboardData) {
            this.initializeCharts();
            this.initialized = true;
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

            const form = document.getElementById('dashboard-filters');
            const formData = new FormData(form);
            const searchParams = new URLSearchParams(formData);

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

    async exportData() {
        try {
            console.log('Exporting data...');
            this.showLoading();

            const form = document.getElementById('dashboard-filters');
            const formData = new FormData(form);
            const searchParams = new URLSearchParams(formData);

            const response = await fetch(`/dashboard/export?${searchParams.toString()}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
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

        // Update charts
        this.updateCharts(data.charts);

        // Update detail table
        this.updateDetailTable(data.detail_table);
    }

    updateMetrics(metrics) {
        console.log('Updating metrics:', metrics);

        const elements = {
            'total-pos': metrics.total_pos,
            'on-time-percentage': metrics.on_time_percentage + '%',
            'delayed-percentage': metrics.delayed_percentage + '%',
            'material-count': metrics.material_count
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
        console.log('Updating charts with data:', chartsData);

        // Update hub chart
        if (chartsData.hub_distribution) {
            this.updatePieChart('hubChart', 'hubLegend', chartsData.hub_distribution);
        }

        // Update delivery status chart
        if (chartsData.delivery_status) {
            this.updatePieChart('deliveryChart', 'deliveryLegend', chartsData.delivery_status);
        }

        // Update transport type chart
        if (chartsData.transport_type) {
            this.updatePieChart('transportChart', 'transportLegend', chartsData.transport_type);
        }

        // Update delay reasons chart
        if (chartsData.delay_reasons) {
            this.updatePieChart('delayChart', 'delayLegend', chartsData.delay_reasons);
        }

        // Update stage chart
        if (chartsData.pos_by_stage) {
            this.updatePieChart('stageChart', 'stageLegend', chartsData.pos_by_stage);
        }
    }

    updatePieChart(canvasId, legendId, data) {
        try {
            console.log(`Creating/updating chart: ${canvasId}`, data);

            const canvas = document.getElementById(canvasId);
            if (!canvas) {
                console.warn(`Canvas with id '${canvasId}' not found`);
                return;
            }

            // Destroy existing chart if it exists
            if (this.charts[canvasId]) {
                console.log(`Destroying existing chart: ${canvasId}`);
                this.charts[canvasId].destroy();
                delete this.charts[canvasId];
            }

            // Skip if no data
            if (!data || data.length === 0) {
                console.log(`No data for chart: ${canvasId}`);
                this.updateChartLegend(legendId, [], []);
                return;
            }

            // Create new chart
            const ctx = canvas.getContext('2d');
            const colors = this.generateColors(data.length);

            console.log(`Creating new chart: ${canvasId}`);

            // Use percentage values for the chart visualization if available, otherwise use value
            const chartData = data.map(item => item.percentage || item.value);

            const chart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(item => item.name),
                    datasets: [{
                        data: chartData,
                        backgroundColor: colors,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const item = data[context.dataIndex];
                                    if (item.percentage) {
                                        return `${item.name}: ${item.percentage}% (${item.value})`;
                                    }
                                    return `${item.name}: ${item.value}`;
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });

            // Store chart reference
            this.charts[canvasId] = chart;
            console.log(`Chart created successfully: ${canvasId}`);

            // Update legend
            this.updateChartLegend(legendId, data, colors);
        } catch (error) {
            console.error(`Error creating chart ${canvasId}:`, error);
        }
    }

    updateChartLegend(legendId, data, colors) {
        try {
            const legend = document.getElementById(legendId);
            if (!legend) {
                console.warn(`Legend with id '${legendId}' not found`);
                return;
            }

            if (!data || data.length === 0) {
                legend.innerHTML = '<div class="text-sm text-gray-500">Sin datos</div>';
                return;
            }

            legend.innerHTML = data.map((item, index) => {
                // Use percentage if available, otherwise use value
                const displayValue = item.percentage ? `${item.percentage}%` : item.value;

                return `
                    <div class="legend-item">
                        <div class="legend-label">
                            <div class="legend-color" style="background-color: ${colors[index]}"></div>
                            <span class="legend-text">${item.name}</span>
                        </div>
                        <span class="legend-value">${displayValue}</span>
                    </div>
                `;
            }).join('');
        } catch (error) {
            console.error(`Error updating legend ${legendId}:`, error);
        }
    }

    updateDetailTable(tableData) {
        try {
            console.log('Updating detail table:', tableData);

            const tbody = document.getElementById('detailTableBody');
            if (!tbody) {
                console.warn('Detail table body not found');
                return;
            }

            if (!tableData || tableData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-gray-500">Sin datos disponibles</td></tr>';
                return;
            }

            tbody.innerHTML = tableData.map(row => `
                <tr>
                    <td>${row.po_number}</td>
                    <td>${row.fecha_salida}</td>
                    <td>${row.fecha_estimada}</td>
                    <td class="text-right">${row.cantidad_kg}</td>
                </tr>
            `).join('');
        } catch (error) {
            console.error('Error updating detail table:', error);
        }
    }

    generateColors(count) {
        const colors = [
            '#565aff', '#9aabff', '#ff3459', '#f46844',
            '#5dd595', '#5ae7f4', '#c9cfff', '#ffb366'
        ];

        while (colors.length < count) {
            colors.push(...colors);
        }

        return colors.slice(0, count);
    }

    initializeCharts() {
        try {
            console.log('Initializing charts...');

            if (window.dashboardData && window.dashboardData.charts) {
                this.updateCharts(window.dashboardData.charts);
                console.log('All charts initialized successfully');
            } else {
                console.warn('No dashboard data available for chart initialization');
            }
        } catch (error) {
            console.error('Error initializing charts:', error);
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
    new DashboardManager();
});

// Also handle case where this script loads after DOM is ready
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    console.log('DOM already ready - Initializing Dashboard');
    new DashboardManager();
}
