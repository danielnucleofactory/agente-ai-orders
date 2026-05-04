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

        // Apply filters button from top
        const applyFiltersBtnTop = document.getElementById('apply-filters-btn-top');
        if (applyFiltersBtnTop) {
            applyFiltersBtnTop.addEventListener('click', () => {
                this.applyFilters();
            });
        }

        // Apply filters button from panel (legacy support)
        const applyFiltersBtn = document.getElementById('apply-filters-btn');
        if (applyFiltersBtn) {
            applyFiltersBtn.addEventListener('click', () => {
                this.applyFilters();
            });
        }

        // Additional filter buttons
        const poRetrasoBtn = document.getElementById('btn-po-retraso-cl');
        if (poRetrasoBtn) {
            poRetrasoBtn.addEventListener('click', () => {
                this.toggleAdditionalFilter('po_retraso_cl', poRetrasoBtn);
            });
        }

        const poAdelantoBtn = document.getElementById('btn-po-adelanto-cl');
        if (poAdelantoBtn) {
            poAdelantoBtn.addEventListener('click', () => {
                this.toggleAdditionalFilter('po_adelanto_cl', poAdelantoBtn);
            });
        }

        const indicadorCapacidadBtn = document.getElementById('btn-indicador-capacidad');
        if (indicadorCapacidadBtn) {
            indicadorCapacidadBtn.addEventListener('click', () => {
                this.toggleAdditionalFilter('indicador_capacidad', indicadorCapacidadBtn);
            });
        }

        // Export button
        const exportBtn = document.getElementById('export-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.exportData();
            });
        }

        // Modal close events
        this.setupModalEvents();
    }

    toggleAdditionalFilter(filterName, button) {
        // Toggle active state
        button.classList.toggle('active');

        // Apply filters immediately
        this.applyFilters();
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

    /**
     * Obtiene el valor de un input de fecha (compatible con Flatpickr altInput).
     */
    getDateInputValue(input) {
        if (!input) return null;
        if (input.value) return input.value;
        if (input._flatpickr?.selectedDates?.length) {
            return input._flatpickr.formatDate(input._flatpickr.selectedDates[0], 'Y-m-d');
        }
        return null;
    }

    /**
     * Texto visible de una celda (coherente con lo que ve el usuario; sin HTML).
     */
    cellText(el) {
        if (!el) {
            return '';
        }
        const clone = el.cloneNode(true);
        clone.querySelectorAll('.expand-icon').forEach((n) => n.remove());
        return (clone.innerText || clone.textContent || '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    /**
     * Etiqueta de la fila padre (fila expandible) de una sub-tabla.
     */
    subTableParentLabel(subTable) {
        const subRow = subTable.closest('tr.sub-table-row');
        if (!subRow) {
            return '';
        }
        const prev = subRow.previousElementSibling;
        if (!prev) {
            return '';
        }
        const td = prev.querySelector('td, th');
        return td ? this.cellText(td) : '';
    }

    /**
     * Convierte una <table> del DOM en headings + filas (orden actual del DOM).
     *
     * @param {HTMLTableElement} table
     * @param {{ skipSubTableRows?: boolean }} options Si true, omite filas .sub-table-row (vista colapsada).
     */
    tableToSheetSpec(table, options = {}) {
        const skipSub = options.skipSubTableRows === true;
        const thead = table.querySelector('thead');
        const tbody = table.querySelector('tbody');
        let headings = [];
        if (thead) {
            const firstTr = thead.querySelector('tr');
            if (firstTr) {
                headings = [...firstTr.querySelectorAll('th, td')].map((th) => this.cellText(th));
            }
        }
        const rows = [];
        if (tbody) {
            tbody.querySelectorAll(':scope > tr').forEach((tr) => {
                if (skipSub && tr.classList.contains('sub-table-row')) {
                    return;
                }
                const cells = [...tr.querySelectorAll(':scope > th, :scope > td')];
                if (!cells.length) {
                    return;
                }
                rows.push(cells.map((c) => this.cellText(c)));
            });
        }
        return this.normalizeMatrix(headings, rows);
    }

    normalizeMatrix(headings, rows) {
        let h = [...headings];
        const r = rows.map((row) => [...row]);
        let maxCols = Math.max(h.length, 1, ...r.map((x) => x.length));
        if (!h.length && r.length) {
            h = Array.from({
                length: maxCols
            }, (_, i) => `Col ${i + 1}`);
            maxCols = h.length;
        }
        while (h.length < maxCols) {
            h.push(`Col ${h.length + 1}`);
        }
        if (h.length > maxCols) {
            h = h.slice(0, maxCols);
        }
        return {
            headings: h,
            rows: r.map((row) => {
                const copy = [...row];
                while (copy.length < maxCols) {
                    copy.push('');
                }
                return copy.slice(0, maxCols);
            }),
        };
    }

    deduplicateSheetTitles(sheets) {
        const used = new Set();
        return sheets.map((s, i) => {
            let base = (s.title || `Hoja${i + 1}`).replace(/[\[\]:*?/\\]/g, '-').trim() || `Hoja${i + 1}`;
            base = base.slice(0, 31);
            let out = base;
            let n = 2;
            while (used.has(out)) {
                const suf = `_${n}`;
                out = (base.slice(0, Math.max(1, 31 - suf.length)) + suf);
                n += 1;
            }
            used.add(out);
            return {
                ...s,
                title: out
            };
        });
    }

    /**
     * Construye las hojas del Excel según la vista activa (orden y contenido = DOM).
     * Tiempo real con sub-tablas: hoja principal sin filas .sub-table-row + una hoja por cada table.sub-table.
     */
    buildDomExportSheets() {
        const sheetsRaw = [];
        let filenameBase = 'dashboard_export';

        const activeView = document.querySelector('.view-content.active');
        const legacyTrend = document.getElementById('trendTable');

        if (activeView) {
            const viewId = activeView.id;
            filenameBase = `dashboard_${viewId.replace(/[^a-zA-Z0-9_-]/g, '_')}`;

            if (viewId === 'tendencia-po' || viewId === 'tendencia-teus') {
                const isTeus = viewId === 'tendencia-teus';
                const table = document.getElementById(isTeus ? 'kpiTrendTableTeus' : 'kpiTrendTable');
                const titleEl = document.getElementById(isTeus ? 'kpiTableTitleTeus' : 'kpiTableTitle');
                const mainTitle = (titleEl?.textContent || 'Principal').trim() || 'Principal';
                if (table) {
                    const main = this.tableToSheetSpec(table, {
                        skipSubTableRows: true
                    });
                    if (main.headings.length || main.rows.length) {
                        sheetsRaw.push({
                            title: mainTitle.slice(0, 100),
                            headings: main.headings,
                            rows: main.rows,
                        });
                    }
                    const subTables = table.querySelectorAll('tbody tr.sub-table-row table.sub-table');
                    let idx = 0;
                    subTables.forEach((sub) => {
                        idx += 1;
                        const spec = this.tableToSheetSpec(sub, {
                            skipSubTableRows: false
                        });
                        const hasData = spec.headings.length > 0 && spec.rows.length > 0;
                        if (!hasData) {
                            return;
                        }
                        const label = this.subTableParentLabel(sub) || `Detalle_${idx}`;
                        sheetsRaw.push({
                            title: `Det: ${label}`.slice(0, 100),
                            headings: spec.headings,
                            rows: spec.rows,
                        });
                    });
                }
            } else if (viewId === 'po-vs-teus') {
                const defs = [{
                        head: 'povsteus-stage-head',
                        title: 'PO_TEUs_Etapa'
                    },
                    {
                        head: 'povsteus-period-head',
                        title: 'PO_TEUs_Periodo'
                    },
                    {
                        head: 'povsteus-vendor-head',
                        title: 'PO_TEUs_Proveedor'
                    },
                    {
                        head: 'povsteus-line-head',
                        title: 'PO_TEUs_Naviera'
                    },
                ];
                defs.forEach((d) => {
                    const h = document.getElementById(d.head);
                    if (!h) {
                        return;
                    }
                    const t = h.closest('table');
                    if (!t) {
                        return;
                    }
                    const spec = this.tableToSheetSpec(t, {
                        skipSubTableRows: false
                    });
                    if (spec.headings.length || spec.rows.length) {
                        sheetsRaw.push({
                            title: d.title,
                            headings: spec.headings,
                            rows: spec.rows,
                        });
                    }
                });
            } else if (viewId === 'comparativo') {
                const t = document.getElementById('kpiCompTable');
                const title = (document.getElementById('kpiCompTableTitle')?.textContent || 'Comparativo').trim() || 'Comparativo';
                if (t) {
                    const spec = this.tableToSheetSpec(t, {
                        skipSubTableRows: false
                    });
                    if (spec.headings.length || spec.rows.length) {
                        sheetsRaw.push({
                            title: title.slice(0, 100),
                            headings: spec.headings,
                            rows: spec.rows,
                        });
                    }
                }
            } else if (viewId === 'proyeccion') {
                const t = document.getElementById('proy-table');
                const title = (document.getElementById('proy-table-description')?.textContent || 'Proyeccion').trim().slice(0, 100) || 'Proyeccion';
                if (t) {
                    const spec = this.tableToSheetSpec(t, {
                        skipSubTableRows: false
                    });
                    if (spec.headings.length || spec.rows.length) {
                        sheetsRaw.push({
                            title,
                            headings: spec.headings,
                            rows: spec.rows,
                        });
                    }
                }
            }
        } else if (legacyTrend) {
            filenameBase = 'dashboard_tendencia_mensual';
            const spec = this.tableToSheetSpec(legacyTrend, {
                skipSubTableRows: false
            });
            if (spec.headings.length || spec.rows.length) {
                sheetsRaw.push({
                    title: 'Tendencia_Etapas',
                    headings: spec.headings,
                    rows: spec.rows,
                });
            }
        }

        const sheets = this.deduplicateSheetTitles(sheetsRaw);
        return {
            sheets,
            filenameBase
        };
    }

    collectPanelFilters(searchParams) {
        // Contexto UI (dashboard-kpi): tab/subtab activa
        const activeTab = document.querySelector('.tab.active');
        if (activeTab?.dataset?.view) {
            searchParams.append('active_view', activeTab.dataset.view);
        }
        const activeSubtab = document.querySelector('.subtab.active');
        if (activeSubtab?.dataset?.subtab) {
            searchParams.append('active_subtab', activeSubtab.dataset.subtab);
        }

        // ========== FILTROS DEL DASHBOARD ORIGINAL ==========

        // Recopilar fechas (dashboard original)
        const startDate = document.getElementById('startDate');
        const startDateVal = this.getDateInputValue(startDate);
        if (startDateVal) {
            searchParams.append('date_from', startDateVal);
        }

        const endDate = document.getElementById('endDate');
        const endDateVal = this.getDateInputValue(endDate);
        if (endDateVal) {
            searchParams.append('date_to', endDateVal);
        }

        // ========== FILTROS DEL DASHBOARD-KPI ==========

        // Fecha inicio (dashboard-kpi)
        const filterDateFrom = document.getElementById('filter-date-from');
        const filterDateFromVal = this.getDateInputValue(filterDateFrom);
        if (filterDateFromVal && !startDateVal) {
            searchParams.append('date_from', filterDateFromVal);
        }

        // Fecha fin (dashboard-kpi)
        const filterDateTo = document.getElementById('filter-date-to');
        const filterDateToVal = this.getDateInputValue(filterDateTo);
        if (filterDateToVal && !endDateVal) {
            searchParams.append('date_to', filterDateToVal);
        }

        // Cliente / Trading Company (dashboard-kpi)
        const tradingCompany = document.getElementById('filter-trading-company');
        if (tradingCompany && tradingCompany.value) {
            searchParams.append('trading_company', tradingCompany.value);
        }

        // Etapa (dashboard-kpi)
        const filterStage = document.getElementById('filter-stage');
        if (filterStage && filterStage.value) {
            searchParams.append('stage', filterStage.value);
        }

        // Proveedor de Mercancía (dashboard-kpi)
        const filterVendorId = document.getElementById('filter-vendor-id');
        if (filterVendorId && filterVendorId.value) {
            searchParams.append('vendor_id', filterVendorId.value);
        }

        // Proveedor de Servicio (dashboard-kpi)
        const filterServiceProvider = document.getElementById('filter-service-provider');
        if (filterServiceProvider && filterServiceProvider.value) {
            searchParams.append('service_provider', filterServiceProvider.value);
        }

        // Puerto de Embarque (dashboard-kpi)
        const filterDeparturePort = document.getElementById('filter-departure-port');
        if (filterDeparturePort && filterDeparturePort.value) {
            searchParams.append('departure_port', filterDeparturePort.value);
        }

        // Puerto de Arribo (dashboard-kpi)
        const filterArrivalPort = document.getElementById('filter-arrival-port');
        if (filterArrivalPort && filterArrivalPort.value) {
            searchParams.append('arrival_port', filterArrivalPort.value);
        }

        // Naviera (dashboard-kpi)
        const filterShippingLine = document.getElementById('filter-shipping-line');
        if (filterShippingLine && filterShippingLine.value) {
            searchParams.append('shipping_line', filterShippingLine.value);
        }

        // Ruta Logística (dashboard-kpi)
        const filterRouteLabel = document.getElementById('filter-route-label');
        if (filterRouteLabel && filterRouteLabel.value) {
            searchParams.append('route_label', filterRouteLabel.value);
        }

        // Número de PO (dashboard-kpi)
        const filterOrderNumber = document.getElementById('filter-order-number');
        if (filterOrderNumber && filterOrderNumber.value) {
            searchParams.append('order_number', filterOrderNumber.value);
        }

        // ========== FILTROS MULTI-SELECT DEL DASHBOARD ORIGINAL ==========

        // Recopilar filtro de Tipo de Cliente
        const customerTypeSelect = document.getElementById('customer-type-filter');
        if (customerTypeSelect) {
            const selectedTypes = Array.from(customerTypeSelect.querySelectorAll('.multi-select-option.selected'))
                .map(opt => opt.dataset.value)
                .filter(Boolean);
            if (selectedTypes.length > 0) {
                selectedTypes.forEach(type => {
                    searchParams.append('customer_type[]', type);
                });
            }
        }

        // Recopilar filtro de Estado de Llegada
        const arrivalStatusSelect = document.getElementById('arrival-status-filter');
        if (arrivalStatusSelect) {
            const selectedStatuses = Array.from(arrivalStatusSelect.querySelectorAll('.multi-select-option.selected'))
                .map(opt => opt.dataset.value)
                .filter(Boolean);
            if (selectedStatuses.length > 0) {
                selectedStatuses.forEach(status => {
                    searchParams.append('arrival_status[]', status);
                });
            }
        }

        // Recopilar filtros de Vendor (buscar en top primero, luego en panel si existe)
        const vendorSelect = document.getElementById('vendor-filter-top') || document.querySelector('.filters-panel .multi-select[data-placeholder*="vendors"]');
        if (vendorSelect && !filterVendorId?.value) {
            const selectedVendors = Array.from(vendorSelect.querySelectorAll('.multi-select-option.selected'))
                .map(opt => opt.dataset.value)
                .filter(Boolean);
            if (selectedVendors.length > 0) {
                selectedVendors.forEach(vendorId => {
                    searchParams.append('vendor_id[]', vendorId);
                });
            }
        }

        // Recopilar filtro de Puerto de Embarque (multi-select)
        const departurePortSelect = document.getElementById('departure-port-filter');
        if (departurePortSelect && !filterDeparturePort?.value) {
            const selectedPorts = Array.from(departurePortSelect.querySelectorAll('.multi-select-option.selected'))
                .map(opt => opt.dataset.value)
                .filter(Boolean);
            if (selectedPorts.length > 0) {
                selectedPorts.forEach(port => {
                    searchParams.append('departure_port[]', port);
                });
            }
        }

        // Recopilar filtro de Puerto de Arribo (multi-select)
        const arrivalPortSelect = document.getElementById('arrival-port-filter');
        if (arrivalPortSelect && !filterArrivalPort?.value) {
            const selectedPorts = Array.from(arrivalPortSelect.querySelectorAll('.multi-select-option.selected'))
                .map(opt => opt.dataset.value)
                .filter(Boolean);
            if (selectedPorts.length > 0) {
                selectedPorts.forEach(port => {
                    searchParams.append('arrival_port[]', port);
                });
            }
        }

        // Recopilar filtro de Naviera (multi-select)
        const shippingLineSelect = document.getElementById('shipping-line-filter');
        if (shippingLineSelect && !filterShippingLine?.value) {
            const selectedLines = Array.from(shippingLineSelect.querySelectorAll('.multi-select-option.selected'))
                .map(opt => opt.dataset.value)
                .filter(Boolean);
            if (selectedLines.length > 0) {
                selectedLines.forEach(line => {
                    searchParams.append('shipping_line[]', line);
                });
            }
        }

        // Recopilar filtro de Proveedor de Servicios (multi-select)
        const serviceProviderSelect = document.getElementById('service-provider-filter');
        if (serviceProviderSelect && !filterServiceProvider?.value) {
            const selectedProviders = Array.from(serviceProviderSelect.querySelectorAll('.multi-select-option.selected'))
                .map(opt => opt.dataset.value)
                .filter(Boolean);
            if (selectedProviders.length > 0) {
                selectedProviders.forEach(provider => {
                    searchParams.append('service_provider[]', provider);
                });
            }
        }

        // ========== FILTROS ADICIONALES (BOTONES) ==========

        const poRetrasoBtn = document.getElementById('btn-po-retraso-cl');
        if (poRetrasoBtn && poRetrasoBtn.classList.contains('active')) {
            searchParams.append('po_retraso_cl', '1');
        }

        const poAdelantoBtn = document.getElementById('btn-po-adelanto-cl');
        if (poAdelantoBtn && poAdelantoBtn.classList.contains('active')) {
            searchParams.append('po_adelanto_cl', '1');
        }

        const indicadorCapacidadBtn = document.getElementById('btn-indicador-capacidad');
        if (indicadorCapacidadBtn && indicadorCapacidadBtn.classList.contains('active')) {
            searchParams.append('indicador_capacidad', '1');
        }

        // Legacy support: también buscar checkboxes si existen
        const poRetrasoCl = document.getElementById('filter-po-retraso-cl');
        if (poRetrasoCl && poRetrasoCl.checked && !poRetrasoBtn) {
            searchParams.append('po_retraso_cl', '1');
        }

        const poAdelantoCl = document.getElementById('filter-po-adelanto-cl');
        if (poAdelantoCl && poAdelantoCl.checked && !poAdelantoBtn) {
            searchParams.append('po_adelanto_cl', '1');
        }

        const indicadorCapacidad = document.getElementById('filter-indicador-capacidad');
        if (indicadorCapacidad && indicadorCapacidad.checked && !indicadorCapacidadBtn) {
            searchParams.append('indicador_capacidad', '1');
        }

        // ========== BOTONES DEL DASHBOARD-KPI (IDs nuevos) ==========
        const kpiPoRetraso = document.getElementById('btn-kpi-po-retraso-cl');
        if (kpiPoRetraso && kpiPoRetraso.classList.contains('active')) {
            searchParams.append('po_retraso_cl', '1');
        }
        const kpiPoAdelanto = document.getElementById('btn-kpi-po-adelanto-cl');
        if (kpiPoAdelanto && kpiPoAdelanto.classList.contains('active')) {
            searchParams.append('po_adelanto_cl', '1');
        }
        const kpiCapacidad = document.getElementById('btn-kpi-indicador-capacidad');
        if (kpiCapacidad && kpiCapacidad.classList.contains('active')) {
            searchParams.append('indicador_capacidad', '1');
        }
        const kpiTransbordo = document.getElementById('btn-kpi-pos-transbordo');
        if (kpiTransbordo && kpiTransbordo.classList.contains('active')) {
            searchParams.append('pos_transbordo', '1');
            searchParams.append('active_filter', 'pos_transbordo');
        }
        const kpiPosAta = document.getElementById('btn-kpi-pos-ata');
        if (kpiPosAta && kpiPosAta.classList.contains('active')) {
            searchParams.append('pos_ata', '1');
            searchParams.append('active_filter', 'pos_ata');
        }

        // Fallback: el KPI manager puede manejar "activo" sin clase CSS en el primer click
        if (window.dashboardKPIManager?.activeFilter) {
            searchParams.append('active_filter', window.dashboardKPIManager.activeFilter);
            if (window.dashboardKPIManager.activeFilter === 'pos_transbordo') {
                searchParams.append('pos_transbordo', '1');
            }
            if (window.dashboardKPIManager.activeFilter === 'pos_ata') {
                searchParams.append('pos_ata', '1');
            }
            if (window.dashboardKPIManager.activeFilter === 'po_retraso_cl') {
                searchParams.append('po_retraso_cl', '1');
            }
            if (window.dashboardKPIManager.activeFilter === 'po_adelanto_cl') {
                searchParams.append('po_adelanto_cl', '1');
            }
            if (window.dashboardKPIManager.activeFilter === 'indicador_capacidad') {
                searchParams.append('indicador_capacidad', '1');
            }
        }

        // Vista comparativo: períodos A/B (si existen inputs)
        const p1s = this.getDateInputValue(document.getElementById('comp-period-a-from'));
        const p1e = this.getDateInputValue(document.getElementById('comp-period-a-to'));
        const p2s = this.getDateInputValue(document.getElementById('comp-period-b-from'));
        const p2e = this.getDateInputValue(document.getElementById('comp-period-b-to'));
        if (p1s && p1e && p2s && p2e) {
            searchParams.append('period1_start', p1s);
            searchParams.append('period1_end', p1e);
            searchParams.append('period2_start', p2s);
            searchParams.append('period2_end', p2e);
            searchParams.append('comparison_period_1', `${p1s} → ${p1e}`);
            searchParams.append('comparison_period_2', `${p2s} → ${p2e}`);
        }

        // Vista proyección: semana desde (input type="week" entrega formato YYYY-Www)
        const proyWeek = document.getElementById('proy-week-start');
        if (proyWeek?.value) {
            searchParams.append('projection_week', proyWeek.value);
        }
    }

    async exportData() {
        const exportBtn = document.getElementById('export-btn');

        if (!exportBtn) {
            return;
        }

        const originalContent = exportBtn.innerHTML;

        try {
            // Mostrar loader en el botón
            exportBtn.disabled = true;
            exportBtn.innerHTML = `
                <svg style="width: 18px; height: 18px; animation: spin 1s linear infinite;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="31.4 31.4" stroke-dashoffset="0"></circle>
                </svg>
                Descargando...
            `;
            exportBtn.style.opacity = '0.7';
            exportBtn.style.cursor = 'wait';

            const built = this.buildDomExportSheets();
            if (!built.sheets.length) {
                throw new Error('No hay datos en la tabla visible para exportar.');
            }

            const response = await fetch('/dashboard/export', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                },
                body: JSON.stringify({
                    sheets: built.sheets,
                    filename_base: built.filenameBase,
                }),
            });

            if (!response.ok) {
                const ct = response.headers.get('Content-Type') || '';
                let detail = response.statusText || String(response.status);
                if (ct.includes('application/json')) {
                    try {
                        const j = await response.json();
                        if (j.message) {
                            detail = j.message;
                        } else if (j.errors) {
                            detail = JSON.stringify(j.errors);
                        }
                    } catch (_) {
                        /* ignore */
                    }
                }
                throw new Error('Error al exportar los datos: ' + detail);
            }

            // Create blob and download
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');

            // Get filename from response headers if available
            const contentDisposition = response.headers.get('Content-Disposition');
            let filename = 'tendencia_etapas.xlsx';
            if (contentDisposition) {
                const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(contentDisposition);
                if (matches && matches[1]) {
                    filename = matches[1].replace(/['"]/g, '');
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
            this.showErrorModal('Error al exportar los datos: ' + error.message);
        } finally {
            // Restaurar el botón
            exportBtn.disabled = false;
            exportBtn.innerHTML = originalContent;
            exportBtn.style.opacity = '1';
            exportBtn.style.cursor = 'pointer';
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
        // Initialize Customer Type multi-select
        const customerTypeSelect = document.getElementById('customer-type-filter');
        if (customerTypeSelect && filterOptions.customer_types) {
            this.populateMultiSelect(customerTypeSelect, filterOptions.customer_types, 'id', 'name');
        }

        // Initialize Arrival Status multi-select
        const arrivalStatusSelect = document.getElementById('arrival-status-filter');
        if (arrivalStatusSelect && filterOptions.arrival_statuses) {
            this.populateMultiSelect(arrivalStatusSelect, filterOptions.arrival_statuses, 'id', 'name');
        }

        // Initialize Vendor multi-select (buscar en top primero, luego en panel si existe)
        const vendorSelect = document.getElementById('vendor-filter-top') || document.querySelector('.filters-panel .multi-select[data-placeholder*="vendors"]');
        if (vendorSelect && filterOptions.vendors) {
            this.populateMultiSelect(vendorSelect, filterOptions.vendors, 'id', 'name');
        }

        // Initialize Departure Port multi-select
        const departurePortSelect = document.getElementById('departure-port-filter');
        if (departurePortSelect && filterOptions.departure_ports) {
            this.populateMultiSelect(departurePortSelect, filterOptions.departure_ports, 'id', 'name');
        }

        // Initialize Arrival Port multi-select
        const arrivalPortSelect = document.getElementById('arrival-port-filter');
        if (arrivalPortSelect && filterOptions.arrival_ports) {
            this.populateMultiSelect(arrivalPortSelect, filterOptions.arrival_ports, 'id', 'name');
        }

        // Initialize Shipping Line multi-select
        const shippingLineSelect = document.getElementById('shipping-line-filter');
        if (shippingLineSelect && filterOptions.shipping_lines) {
            this.populateMultiSelect(shippingLineSelect, filterOptions.shipping_lines, 'id', 'name');
        }

        // Initialize Service Provider multi-select
        const serviceProviderSelect = document.getElementById('service-provider-filter');
        if (serviceProviderSelect && filterOptions.service_providers) {
            this.populateMultiSelect(serviceProviderSelect, filterOptions.service_providers, 'id', 'name');
        }
    }

    sortFilterItems(items, valueKey, labelKey) {
        const arr = Array.isArray(items) ? items : Object.values(items);
        const label = (item) => {
            if (valueKey == null && labelKey == null) {
                return String(item ?? '');
            }
            return String(
                (labelKey ? item[labelKey] : null) ??
                (valueKey ? item[valueKey] : null) ??
                item?.name ??
                item?.short_text ??
                item ??
                ''
            );
        };
        return [...arr].sort((a, b) =>
            label(a).localeCompare(label(b), 'es', {
                sensitivity: 'base',
                numeric: true
            })
        );
    }

    populateMultiSelect(container, items, valueKey, labelKey) {
        const optionsBox = container.querySelector('.multi-select-options');
        if (!optionsBox) return;

        optionsBox.innerHTML = '';

        const itemsArray = this.sortFilterItems(items, valueKey, labelKey);

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

            const categoryOrder = [
                'Producción',
                'Booking',
                'Transito',
                'Puerto',
                'Recibiendo CDI',
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

// Initialize dashboard only once
(function() {
    function initDashboard() {
        if (window.dashboardManager) {
            console.log('DashboardManager already initialized, skipping...');
            return;
        }
        console.log('Initializing DashboardManager...');
        window.dashboardManager = new DashboardManager();
    }

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        // DOM already ready
        console.log('DOM already ready');
        initDashboard();
    } else {
        // Wait for DOM
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded');
            initDashboard();
        });
    }
})();