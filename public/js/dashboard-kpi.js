// Dashboard KPI - Funcionalidad con datos reales desde API
class DashboardKPIManager {
    constructor() {
        this.activeFilter = null;
        this.activeCompFilter = null;
        this.apiBaseUrl = '/dashboard-kpi/api';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        this.currentFilters = {};
        this.init();
    }

    init() {
        this.setupEventListeners();
        // Establecer filtros iniciales (fechas por defecto)
        this.currentFilters = this.collectFilters();
        
        // Cargar opciones de filtros primero
        this.loadFilterOptions().then(() => {
            // Actualizar filtros después de cargar opciones
            this.currentFilters = this.collectFilters();
            
            // Establecer fechas por defecto para vista comparativa si está activa
            // Esperar un poco para que Flatpickr se inicialice
            setTimeout(() => {
                this.setDefaultComparisonPeriods();
            }, 300);
            
            // Verificar qué vista está activa al iniciar
            const tendenciaTeusView = document.getElementById('tendencia-teus');
            const isTeusViewActive = tendenciaTeusView && tendenciaTeusView.classList.contains('active');
            
            // Luego cargar datos iniciales según la vista activa
            if (isTeusViewActive) {
                this.loadDefaultTeusTable();
            } else {
                this.loadDefaultTable();
            }
            this.loadKPISummary();
        });
    }

    setDefaultComparisonPeriods() {
        const comparativoView = document.getElementById('comparativo');
        if (!comparativoView || !comparativoView.classList.contains('active')) {
            return;
        }

        const periodAFrom = document.getElementById('comp-period-a-from');
        const periodATo = document.getElementById('comp-period-a-to');
        const periodBFrom = document.getElementById('comp-period-b-from');
        const periodBTo = document.getElementById('comp-period-b-to');

        if (!periodAFrom || !periodATo || !periodBFrom || !periodBTo) {
            return;
        }

        // Obtener fechas por defecto
        const lastMonth = this.getLastMonthRange();
        const currentMonth = this.getCurrentMonthRange();

        // Función para establecer fecha (compatible con Flatpickr)
        const setDateValue = (input, dateValue) => {
            if (!input) return;
            
            // Si tiene Flatpickr, usar su API
            if (input._flatpickr) {
                input._flatpickr.setDate(dateValue, false);
            } else {
                // Si no tiene Flatpickr aún, establecer el valor directamente
                input.value = dateValue;
                // Si Flatpickr se inicializa después, intentar establecer de nuevo
                setTimeout(() => {
                    if (input._flatpickr && !input._flatpickr.selectedDates.length) {
                        input._flatpickr.setDate(dateValue, false);
                    }
                }, 500);
            }
        };

        // Solo establecer si no tienen valores
        if (!periodAFrom.value && !periodAFrom._flatpickr?.selectedDates.length) {
            setDateValue(periodAFrom, lastMonth.start);
            setDateValue(periodATo, lastMonth.end);
        }

        if (!periodBFrom.value && !periodBFrom._flatpickr?.selectedDates.length) {
            setDateValue(periodBFrom, currentMonth.start);
            setDateValue(periodBTo, currentMonth.end);
        }
    }

    getLastMonthRange() {
        const now = new Date();
        const lastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        const lastMonthEnd = new Date(now.getFullYear(), now.getMonth(), 0);
        
        return {
            start: this.formatDateForInput(lastMonth),
            end: this.formatDateForInput(lastMonthEnd)
        };
    }

    getCurrentMonthRange() {
        const now = new Date();
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        
        return {
            start: this.formatDateForInput(firstDay),
            end: this.formatDateForInput(lastDay)
        };
    }

    formatDateForInput(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    async fetchData(endpoint, method = 'GET', body = null) {
        try {
            // Agregar filtros actuales como query params
            const url = new URL(`${this.apiBaseUrl}${endpoint}`, window.location.origin);
            Object.keys(this.currentFilters).forEach(key => {
                const value = this.currentFilters[key];
                if (value !== null && value !== undefined && value !== '') {
                    if (Array.isArray(value)) {
                        value.forEach(v => url.searchParams.append(`${key}[]`, v));
                    } else {
                        url.searchParams.append(key, value);
                    }
                }
            });

            const options = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
            };

            if (body && method !== 'GET') {
                options.body = JSON.stringify(body);
            }

            const response = await fetch(url.toString(), options);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Error fetching data:', error);
            return null;
        }
    }

    async loadFilterOptions() {
        try {
            const result = await this.fetchData('/filter-options');
            
            if (result && result.success && result.data) {
                const data = result.data;
                
                // Cargar clientes
                this.populateSelect('filter-trading-company', data.clients || []);
                
                // Cargar etapas
                this.populateSelect('filter-stage', data.stages || []);
                
                // Cargar proveedores de mercancía
                this.populateSelect('filter-vendor-id', data.vendors || []);
                
                // Cargar proveedores de servicio
                this.populateSelect('filter-service-provider', data.service_providers || []);
                
                // Cargar puertos de embarque
                this.populateSelect('filter-departure-port', data.departure_ports || []);
                
                // Cargar puertos de arribo
                this.populateSelect('filter-arrival-port', data.arrival_ports || []);
                
                // Cargar navieras
                this.populateSelect('filter-shipping-line', data.shipping_lines || []);
                
                // Cargar rutas logísticas
                this.populateSelect('filter-route-label', data.routes || []);
            }
        } catch (error) {
            console.error('Error loading filter options:', error);
        }
    }

    populateSelect(selectId, options) {
        const select = document.getElementById(selectId);
        if (!select) return;

        // Limpiar opciones existentes (excepto la primera "Todos")
        const firstOption = select.querySelector('option[value=""]') || select.querySelector('option:first-child');
        select.innerHTML = '';
        if (firstOption) {
            select.appendChild(firstOption);
        }

        // Agregar nuevas opciones
        options.forEach(option => {
            const opt = document.createElement('option');
            opt.value = option.id || option.name || option;
            opt.textContent = option.name || option;
            select.appendChild(opt);
        });
    }

    /**
     * Obtiene el valor de un input de fecha (compatible con Flatpickr altInput).
     * Con altInput, el valor real puede estar en input.value o en fp.selectedDates.
     */
    getDateInputValue(input) {
        if (!input) return null;
        if (input.value) return input.value;
        if (input._flatpickr?.selectedDates?.length) {
            return input._flatpickr.formatDate(input._flatpickr.selectedDates[0], 'Y-m-d');
        }
        return null;
    }

    collectFilters() {
        const filters = {};
        
        const dateFrom = document.getElementById('filter-date-from');
        const dateTo = document.getElementById('filter-date-to');
        const tradingCompany = document.getElementById('filter-trading-company');
        const stage = document.getElementById('filter-stage');
        const vendorId = document.getElementById('filter-vendor-id');
        const serviceProvider = document.getElementById('filter-service-provider');
        const departurePort = document.getElementById('filter-departure-port');
        const arrivalPort = document.getElementById('filter-arrival-port');
        const shippingLine = document.getElementById('filter-shipping-line');
        const routeLabel = document.getElementById('filter-route-label');
        const orderNumber = document.getElementById('filter-order-number');

        const dateFromVal = this.getDateInputValue(dateFrom);
        const dateToVal = this.getDateInputValue(dateTo);
        if (dateFromVal) filters.date_from = dateFromVal;
        if (dateToVal) filters.date_to = dateToVal;
        if (tradingCompany && tradingCompany.value) filters.trading_company = tradingCompany.value;
        if (stage && stage.value) filters.stage = stage.value;
        if (vendorId && vendorId.value) filters.vendor_id = vendorId.value;
        if (serviceProvider && serviceProvider.value) filters.service_provider = serviceProvider.value;
        if (departurePort && departurePort.value) filters.departure_port = departurePort.value;
        if (arrivalPort && arrivalPort.value) filters.arrival_port = arrivalPort.value;
        if (shippingLine && shippingLine.value) filters.shipping_line = shippingLine.value;
        if (routeLabel && routeLabel.value) filters.route_label = routeLabel.value;
        if (orderNumber && orderNumber.value) filters.order_number = orderNumber.value;

        return filters;
    }

    async applyFilters() {
        this.currentFilters = this.collectFilters();

        // Detectar vista activa
        const activeView = document.querySelector('.view-content.active')?.id
            || document.querySelector('.subtabs-container.active .subtab.active')?.dataset?.subtab
            || 'tendencia-po';

        const reloads = [];

        // Siempre recargar KPI cards
        reloads.push(this.loadKPISummary());

        // Recargar según la vista visible
        if (activeView === 'tendencia-po' || activeView === 'tendencia-teus' || activeView === 'tendencia') {
            reloads.push(this.loadDefaultTable());
            reloads.push(this.loadDefaultTeusTable());

            if (this.activeFilter) {
                const activeButton = document.querySelector(`#${this.activeFilter}`);
                if (activeButton) {
                    const type = activeButton.closest('.filters-panel') ? 'po' : 'teus';
                    reloads.push(this.updateTable(this.activeFilter, type));
                }
            }
        } else if (activeView === 'po-vs-teus') {
            reloads.push(this.loadPoVsTeus());
        } else if (activeView === 'proyeccion') {
            reloads.push(this.loadProyeccion());
        } else if (activeView === 'comparativo') {
            if (this.activeCompFilter) {
                reloads.push(this.updateCompTable(this.activeCompFilter));
            }
        }

        await Promise.all(reloads);
    }

    async loadKPISummary() {
        try {
            const [stageRes, delayRes, advanceRes, ataRes] = await Promise.all([
                this.fetchData('/pos-by-stage'),
                this.fetchData('/pos-delay-cl'),
                this.fetchData('/pos-advance-cl'),
                this.fetchData('/pos-with-ata'),
            ]);

            const totalPOs     = stageRes?.data?.total_pos ?? 0;
            const delayCount   = delayRes?.data?.summary?.total_pos ?? 0;
            const advanceCount = advanceRes?.data?.summary?.total_pos ?? 0;
            const ataCount     = ataRes?.data?.summary?.total_pos ?? 0;

            const delayPct   = totalPOs > 0 ? ((delayCount   / totalPOs) * 100).toFixed(1) : 0;
            const advancePct = totalPOs > 0 ? ((advanceCount / totalPOs) * 100).toFixed(1) : 0;

            document.getElementById('kpi-total-pos').textContent          = totalPOs.toLocaleString();
            document.getElementById('kpi-delay-count').textContent        = delayCount.toLocaleString();
            document.getElementById('kpi-delay-percentage').textContent   = `${delayPct}% del total`;
            document.getElementById('kpi-advance-count').textContent      = advanceCount.toLocaleString();
            document.getElementById('kpi-advance-percentage').textContent = `${advancePct}% del total`;
            document.getElementById('kpi-ata-count').textContent          = ataCount.toLocaleString();
        } catch (error) {
            console.error('Error loading KPI summary:', error);
        }
    }

    async loadDefaultTable() {
        // Cargar tabla "PO por Etapa" por defecto en la vista de PO
        const tableHead = document.getElementById('kpiTrendTableHead');
        const tableBody = document.getElementById('kpiTrendTableBody');
        const tableTitle = document.getElementById('kpiTableTitle');

        if (!tableHead || !tableBody || !tableTitle) return;

        // Mostrar loading
        tableTitle.textContent = 'PO por Etapa';
        tableBody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 20px;">Cargando datos...</td></tr>';

        try {
            const result = await this.fetchData('/pos-by-stage');
            
            if (result && result.success && result.data) {
                this.renderPOsByStageTable(result.data, tableHead, tableBody);
            } else {
                this.renderPOsByStageTableFallback(tableHead, tableBody);
            }
        } catch (error) {
            console.error('Error loading default table:', error);
            this.renderPOsByStageTableFallback(tableHead, tableBody);
        }
    }

    renderPOsByStageTable(data, tableHead, tableBody) {
        tableHead.innerHTML = `
            <tr>
                <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Etapa</th>
                <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;"># PO</th>
                <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">% de Volumen</th>
            </tr>
        `;

        const stageData = data.data || [];
        const totalPOs = data.total_pos || 0;

        const badgeColors = {
            'Producción': 'badge-info',
            'Booking': 'badge-info',
            'Tránsito': 'badge-info',
            'Transbordo': 'badge-info',
            'Arribo': 'badge-success',
        };

        let rows = '';
        stageData.forEach(stage => {
            const badgeClass = badgeColors[stage.stage] || 'badge-info';
            rows += `
                <tr>
                    <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="badge ${badgeClass}">${stage.stage}</span></td>
                    <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">${stage.po_count.toLocaleString()}</td>
                    <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">${stage.percentage.toFixed(1)}%</td>
                </tr>
            `;
        });

        // Agregar fila de total
        rows += `
            <tr style="background-color: #f8faf9; font-weight: 700;">
                <td style="padding: 12px; border: 1px solid #e5e7eb; color: #374151;">Total</td>
                <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 700; color: #1AAD8A;">${totalPOs.toLocaleString()}</td>
                <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 700; color: #0984e3;">100.0%</td>
            </tr>
        `;

        tableBody.innerHTML = rows;
    }

    renderPOsByStageTableFallback(tableHead, tableBody) {
        tableHead.innerHTML = `
            <tr>
                <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Etapa</th>
                <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;"># PO</th>
                <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">% de Volumen</th>
            </tr>
        `;
        
        tableBody.innerHTML = `
            <tr>
                <td colspan="3" style="text-align: center; padding: 20px; color: #6b7280;">
                    No hay datos disponibles. Verifique la conexión con el servidor.
                </td>
            </tr>
        `;
    }

    setupEventListeners() {
        // Botón aplicar filtros
        const applyBtn = document.getElementById('btn-apply-filters');
        if (applyBtn) {
            applyBtn.addEventListener('click', () => {
                this.applyFilters();
            });
        }

        // Permitir aplicar filtros con Enter en los inputs
        document.querySelectorAll('.filter-input').forEach(input => {
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.applyFilters();
                }
            });
        });

        // Filtros adicionales para PO
        const poFilters = [
            'btn-kpi-po-retraso-cl',
            'btn-kpi-po-adelanto-cl',
            'btn-kpi-indicador-capacidad',
            'btn-kpi-pos-transbordo',
            'btn-kpi-pos-ata'
        ];

        poFilters.forEach(filterId => {
            const button = document.getElementById(filterId);
            if (button) {
                button.addEventListener('click', () => {
                    this.handleFilterClick(filterId, button, 'po');
                });
            }
        });

        // Filtros adicionales para TEUs
        const teusFilters = [
            'btn-kpi-teus-retraso-cl',
            'btn-kpi-teus-adelanto-cl',
            'btn-kpi-teus-capacidad',
            'btn-kpi-teus-transbordo',
            'btn-kpi-teus-ata'
        ];

        teusFilters.forEach(filterId => {
            const button = document.getElementById(filterId);
            if (button) {
                button.addEventListener('click', () => {
                    this.handleFilterClick(filterId, button, 'teus');
                });
            }
        });

        // Listener para cambio de subtabs (PO/TEUs)
        document.querySelectorAll('.subtab').forEach(subtab => {
            subtab.addEventListener('click', () => {
                const subtabId = subtab.dataset.subtab;
                if (subtabId === 'tendencia-teus') {
                    // Cuando se cambia a TEUs, cargar tabla por defecto si no hay filtro activo
                    if (!this.activeFilter) {
                        this.loadDefaultTeusTable();
                    }
                } else if (subtabId === 'tendencia-po') {
                    // Cuando se cambia a PO, cargar tabla por defecto si no hay filtro activo
                    if (!this.activeFilter) {
                        this.loadDefaultTable();
                    }
                }
            });
        });

        // Filtros para Comparativo
        const compFilters = [
            'btn-comp-retraso-cl',
            'btn-comp-adelanto-cl',
            'btn-comp-capacidad',
            'btn-comp-atd',
            'btn-comp-ata'
        ];

        compFilters.forEach(filterId => {
            const button = document.getElementById(filterId);
            if (button) {
                button.addEventListener('click', () => {
                    this.handleCompFilterClick(filterId, button);
                });
            }
        });
    }

    handleFilterClick(filterId, button, type = 'po') {
        // Si el mismo botón está activo, desactivarlo y cargar tabla por defecto
        if (button.classList.contains('active') && this.activeFilter === filterId) {
            button.classList.remove('active');
            this.activeFilter = null;
            if (type === 'po') {
                this.loadDefaultTable();
            } else {
                this.loadDefaultTeusTable();
            }
            return;
        }

        // Desactivar todos los botones del mismo tipo (PO o TEUs)
        const container = button.closest('.filters-panel');
        if (container) {
            container.querySelectorAll('.filter-button').forEach(btn => {
                btn.classList.remove('active');
            });
        }

        // Activar el botón clickeado
        button.classList.add('active');
        this.activeFilter = filterId;

        // Actualizar tabla según el filtro
        this.updateTable(filterId, type);
    }

    async updateTable(filterId, type = 'po') {
        const suffix = type === 'teus' ? 'Teus' : '';
        const tableHead = document.getElementById(`kpiTrendTableHead${suffix}`);
        const tableBody = document.getElementById(`kpiTrendTableBody${suffix}`);
        const tableTitle = document.getElementById(`kpiTableTitle${suffix}`);

        if (!tableHead || !tableBody || !tableTitle) return;

        // Mapeo de filterId a endpoint de API
        const filterEndpoints = {
            'btn-kpi-po-retraso-cl': '/pos-delay-cl',
            'btn-kpi-po-adelanto-cl': '/pos-advance-cl',
            'btn-kpi-indicador-capacidad': '/capacity',
            'btn-kpi-pos-transbordo': '/transshipment',
            'btn-kpi-pos-ata': '/pos-with-ata',
            'btn-kpi-teus-retraso-cl': '/pos-delay-cl',
            'btn-kpi-teus-adelanto-cl': '/pos-advance-cl',
            'btn-kpi-teus-capacidad': '/capacity',
            'btn-kpi-teus-transbordo': '/transshipment',
            'btn-kpi-teus-ata': '/pos-with-ata',
        };

        const filterTitles = {
            'btn-kpi-po-retraso-cl': 'POs con Retraso según Carga Lista (CL)',
            'btn-kpi-po-adelanto-cl': 'POs con Adelanto según Carga Lista (CL)',
            'btn-kpi-indicador-capacidad': 'Capacidad (Allocation)',
            'btn-kpi-pos-transbordo': 'POs en Puerto de Transbordo',
            'btn-kpi-pos-ata': 'POs con ATA (Puerto de Destino)',
            'btn-kpi-teus-retraso-cl': 'TEUs con Retraso según Carga Lista (CL)',
            'btn-kpi-teus-adelanto-cl': 'TEUs con Adelanto según Carga Lista (CL)',
            'btn-kpi-teus-capacidad': 'Capacidad (Allocation) - TEUs',
            'btn-kpi-teus-transbordo': 'TEUs en Puerto de Transbordo',
            'btn-kpi-teus-ata': 'TEUs con ATA (Puerto de Destino)',
        };

        tableTitle.textContent = filterTitles[filterId] || 'Datos';
        tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">Cargando datos...</td></tr>';

        const endpoint = filterEndpoints[filterId];
        if (!endpoint) {
            tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #e17055;">Filtro no configurado</td></tr>';
            return;
        }

        try {
            const result = await this.fetchData(endpoint);
            
            if (result && result.success && result.data) {
                this.renderFilterTable(filterId, result.data, tableHead, tableBody, type);
            } else {
                tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #6b7280;">No hay datos disponibles</td></tr>';
            }
        } catch (error) {
            console.error('Error loading filter data:', error);
            tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #e17055;">Error cargando datos</td></tr>';
        }
    }

    renderFilterTable(filterId, data, tableHead, tableBody, type) {
        const isTeus = type === 'teus';
        const valueLabel = isTeus ? 'TEUs' : 'PO';

        // Determinar qué tipo de tabla renderizar según el filterId
        if (filterId.includes('retraso-cl') || filterId.includes('adelanto-cl')) {
            this.renderDelayAdvanceTable(filterId, data, tableHead, tableBody, isTeus);
        } else if (filterId.includes('capacidad')) {
            this.renderCapacityTable(data, tableHead, tableBody, isTeus);
        } else if (filterId.includes('transbordo')) {
            this.renderTransshipmentTable(data, tableHead, tableBody, isTeus);
        } else if (filterId.includes('ata')) {
            this.renderATATable(data, tableHead, tableBody, isTeus);
        }
    }

    renderDelayAdvanceTable(filterId, data, tableHead, tableBody, isTeus) {
        const isDelay = filterId.includes('retraso');
        const dayLabel = isDelay ? 'Atraso' : 'Adelanto';
        const valueLabel = isTeus ? 'TEUs' : 'PO';
        const valueField = isTeus ? 'teus' : 'po_count';
        const dayField = isDelay ? 'avg_delay_days' : 'avg_advance_days';

        tableHead.innerHTML = `
            <tr>
                <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Proveedor de Mercancía</th>
                <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Cantidad de ${valueLabel}</th>
                <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Días Prom. ${dayLabel}</th>
            </tr>
        `;

        const vendors = data.by_vendor || [];
        const summary = data.summary || {};

        let rows = '';
        vendors.forEach((vendor, index) => {
            const hasDetails = data.details && data.details.filter(d => d.vendor === vendor.vendor).length > 0;
            const vendorDetails = data.details ? data.details.filter(d => d.vendor === vendor.vendor) : [];
            
            rows += `
                <tr class="${hasDetails ? 'expandable-row' : ''}" ${hasDetails ? 'onclick="toggleSubTable(this)"' : ''}>
                    <td style="padding: 12px; border: 1px solid #e5e7eb;">${hasDetails ? '<span class="expand-icon">▶</span>' : ''}${vendor.vendor}</td>
                    <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">${(isTeus ? vendor.teus : vendor.po_count).toLocaleString()}</td>
                    <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: ${isDelay ? '#e17055' : '#27ae60'};">${vendor[dayField]?.toFixed(1) || 0} días</td>
                </tr>
            `;

            // Agregar subtabla si hay detalles
            if (hasDetails && vendorDetails.length > 0) {
                rows += `
                    <tr class="sub-table-row">
                        <td colspan="3" class="sub-table-cell" style="padding: 0;">
                            <table class="sub-table" style="width: 100%; margin: 12px 0; border: 1px solid #e8edec; border-radius: 6px; overflow: hidden;">
                                <thead style="background: #f0f3f2;">
                                    <tr>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Número de PO</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Días de ${dayLabel}</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Etapa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${vendorDetails.slice(0, 5).map(detail => `
                                        <tr>
                                            <td style="padding: 10px 12px; font-size: 13px;">${detail.order_number}</td>
                                            <td class="days" style="padding: 10px 12px; font-size: 13px; color: ${isDelay ? '#e17055' : '#27ae60'};">${detail[isDelay ? 'delay_days' : 'advance_days']} días</td>
                                            <td style="padding: 10px 12px; font-size: 13px;">${detail.stage}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </td>
                    </tr>
                `;
            }
        });

        // Fila total
        const totalValue = isTeus ? summary.total_teus : summary.total_pos;
        const avgDays = isDelay ? summary.avg_delay_days : summary.avg_advance_days;
        
        rows += `
            <tr style="background-color: #f8faf9; font-weight: 700;">
                <td style="padding: 12px; border: 1px solid #e5e7eb; color: #374151;">Total</td>
                <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 700; color: #1AAD8A;">${(totalValue || 0).toLocaleString()}</td>
                <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: ${isDelay ? '#e17055' : '#27ae60'};">${(avgDays || 0).toFixed(1)} días</td>
            </tr>
        `;

        tableBody.innerHTML = rows;
    }

    renderCapacityTable(data, tableHead, tableBody, isTeus) {
        const valueLabel = isTeus ? 'TEUs' : 'PO';

        tableHead.innerHTML = `
            <tr>
                <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Proveedor de Mercancía</th>
                <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Cantidad de ${valueLabel}</th>
                <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">% Participación</th>
                <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Días Prom. Tránsito</th>
            </tr>
        `;

        const vendors = data.by_vendor || [];
        const summary = data.summary || {};

        let rows = '';
        vendors.forEach(vendor => {
            const value = isTeus ? vendor.teus : vendor.po_count;
            rows += `
                <tr>
                    <td style="padding: 12px; border: 1px solid #e5e7eb;">${vendor.vendor}</td>
                    <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">${value.toLocaleString()}</td>
                    <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">${vendor.percentage?.toFixed(1) || 0}%</td>
                    <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">${vendor.avg_transit_days?.toFixed(1) || 0} días</td>
                </tr>
            `;
        });

        const totalValue = isTeus ? summary.total_teus : summary.total_pos;
        rows += `
            <tr style="background-color: #f8faf9; font-weight: 700;">
                <td style="padding: 12px; border: 1px solid #e5e7eb; color: #374151;">Total</td>
                <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 700; color: #1AAD8A;">${(totalValue || 0).toLocaleString()}</td>
                <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 700; color: #0984e3;">100.0%</td>
                <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">${summary.avg_transit_days?.toFixed(1) || 0} días</td>
            </tr>
        `;

        tableBody.innerHTML = rows;
    }

    renderTransshipmentTable(data, tableHead, tableBody, isTeus) {
        const valueLabel = isTeus ? 'TEUs' : 'POs';
        const valueField = isTeus ? 'teus' : 'po_count';

        tableHead.innerHTML = `
            <tr>
                <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Puerto de Transbordo</th>
                <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Cantidad de ${valueLabel}</th>
            </tr>
        `;

        const ports = data.by_port || [];
        const details = data.details || [];

        if (ports.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="2" style="text-align: center; padding: 20px; color: #6b7280;">
                        No hay POs en puerto de transbordo actualmente
                    </td>
                </tr>
            `;
            return;
        }

        let rows = '';
        ports.forEach(port => {
            const value = port[valueField];
            const portDetails = details.filter(d => d.transshipment_port === port.port);
            const hasDetails = portDetails.length > 0;

            rows += `
                <tr class="${hasDetails ? 'expandable-row' : ''}" ${hasDetails ? 'onclick="toggleSubTable(this)"' : ''}>
                    <td style="padding: 12px; border: 1px solid #e5e7eb;">
                        ${hasDetails ? '<span class="expand-icon">▶</span>' : ''}${port.port}
                    </td>
                    <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">
                        ${value.toLocaleString()}
                    </td>
                </tr>
            `;

            if (hasDetails) {
                const subColspan = isTeus ? 2 : 2;
                rows += `
                    <tr class="sub-table-row">
                        <td colspan="2" class="sub-table-cell" style="padding: 0;">
                            <table class="sub-table" style="width: 100%; margin: 12px 0; border: 1px solid #e8edec; border-radius: 6px; overflow: hidden;">
                                <thead style="background: #f0f3f2;">
                                    <tr>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Número de PO</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Proveedor</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Naviera</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Puerto Destino</th>
                                        ${isTeus ? '<th style="padding: 10px 12px; color: #2d3436; font-size: 12px; text-align: right;">TEUs</th>' : ''}
                                    </tr>
                                </thead>
                                <tbody>
                                    ${portDetails.map(d => `
                                        <tr style="background: white;">
                                            <td style="padding: 10px 12px; font-size: 12px; color: #636e72;">${d.order_number}</td>
                                            <td style="padding: 10px 12px; font-size: 12px; color: #636e72;">${d.vendor}</td>
                                            <td style="padding: 10px 12px; font-size: 12px; color: #636e72;">${d.shipping_line}</td>
                                            <td style="padding: 10px 12px; font-size: 12px; color: #636e72;">${d.destination_port}</td>
                                            ${isTeus ? `<td style="padding: 10px 12px; font-size: 12px; color: #636e72; text-align: right;">${d.teus}</td>` : ''}
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </td>
                    </tr>
                `;
            }
        });

        tableBody.innerHTML = rows;
    }

    renderATATable(data, tableHead, tableBody, isTeus) {
        const valueLabel = isTeus ? 'TEUs' : 'PO';

        tableHead.innerHTML = `
            <tr>
                <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Cliente</th>
                <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Ruta Logística</th>
                <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Cantidad de ${valueLabel}</th>
            </tr>
        `;

        const clients = data.by_client || [];
        const routes = data.by_route || [];
        const summary = data.summary || {};

        // Combinar clientes y rutas
        let rows = '';
        
        clients.forEach(client => {
            const value = isTeus ? client.teus : client.po_count;
            // Encontrar rutas asociadas
            const clientRoutes = routes.slice(0, 2); // Simplificado
            
            rows += `
                <tr class="expandable-row" onclick="toggleSubTable(this)">
                    <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="expand-icon">▶</span>${client.client}</td>
                    <td style="padding: 12px; border: 1px solid #e5e7eb;">-</td>
                    <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">${value.toLocaleString()}</td>
                </tr>
            `;

            // Subtabla con detalles
            if (data.details && data.details.length > 0) {
                const clientDetails = data.details.slice(0, 5);
                rows += `
                    <tr class="sub-table-row">
                        <td colspan="3" class="sub-table-cell" style="padding: 0;">
                            <table class="sub-table" style="width: 100%; margin: 12px 0; border: 1px solid #e8edec; border-radius: 6px; overflow: hidden;">
                                <thead style="background: #f0f3f2;">
                                    <tr>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Número de PO</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Naviera</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Puerto de Descarga</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Fecha ATA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${clientDetails.map(detail => `
                                        <tr>
                                            <td style="padding: 10px 12px; font-size: 13px;">${detail.order_number}</td>
                                            <td style="padding: 10px 12px; font-size: 13px;">${detail.shipping_line}</td>
                                            <td style="padding: 10px 12px; font-size: 13px;">${detail.arrival_port}</td>
                                            <td style="padding: 10px 12px; font-size: 13px;">${detail.date_ata}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </td>
                    </tr>
                `;
            }
        });

        const totalValue = isTeus ? summary.total_teus : summary.total_pos;
        rows += `
            <tr style="background-color: #f8faf9; font-weight: 700;">
                <td style="padding: 12px; border: 1px solid #e5e7eb; color: #374151;">Total</td>
                <td style="padding: 12px; border: 1px solid #e5e7eb; color: #374151;">-</td>
                <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 700; color: #1AAD8A;">${(totalValue || 0).toLocaleString()}</td>
            </tr>
        `;

        tableBody.innerHTML = rows || '<tr><td colspan="3" style="text-align: center; padding: 20px; color: #6b7280;">No hay datos disponibles</td></tr>';
    }

    async loadDefaultTeusTable() {
        const tableHead = document.getElementById('kpiTrendTableHeadTeus');
        const tableBody = document.getElementById('kpiTrendTableBodyTeus');
        const tableTitle = document.getElementById('kpiTableTitleTeus');

        if (!tableHead || !tableBody || !tableTitle) return;

        tableTitle.textContent = 'TEUs por Etapa';
        tableBody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 20px;">Cargando datos...</td></tr>';

        try {
            const result = await this.fetchData('/pos-by-stage');
            
            if (result && result.success && result.data) {
                this.renderTEUsByStageTable(result.data, tableHead, tableBody);
            } else {
                tableBody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 20px; color: #6b7280;">No hay datos disponibles</td></tr>';
            }
        } catch (error) {
            console.error('Error loading TEUs table:', error);
            tableBody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 20px; color: #e17055;">Error cargando datos</td></tr>';
        }
    }

    renderTEUsByStageTable(data, tableHead, tableBody) {
        tableHead.innerHTML = `
            <tr>
                <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Etapa</th>
                <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;"># TEUs</th>
                <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">% de Volumen</th>
            </tr>
        `;

        const stageData = data.data || [];
        const totalTEUs = data.total_teus || 0;

        const badgeColors = {
            'Producción': 'badge-info',
            'Booking': 'badge-info',
            'Tránsito': 'badge-info',
            'Transbordo': 'badge-info',
            'Arribo': 'badge-success',
        };

        let rows = '';
        stageData.forEach(stage => {
            const badgeClass = badgeColors[stage.stage] || 'badge-info';
            rows += `
                <tr>
                    <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="badge ${badgeClass}">${stage.stage}</span></td>
                    <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">${stage.teus.toLocaleString()}</td>
                    <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">${stage.teus_percentage.toFixed(1)}%</td>
                </tr>
            `;
        });

        rows += `
            <tr style="background-color: #f8faf9; font-weight: 700;">
                <td style="padding: 12px; border: 1px solid #e5e7eb; color: #374151;">Total</td>
                <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 700; color: #1AAD8A;">${totalTEUs.toLocaleString()}</td>
                <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 700; color: #0984e3;">100.0%</td>
            </tr>
        `;

        tableBody.innerHTML = rows;
    }

    showEmptyState(type = 'po') {
        const suffix = type === 'teus' ? 'Teus' : '';
        const tableHead = document.getElementById(`kpiTrendTableHead${suffix}`);
        const tableBody = document.getElementById(`kpiTrendTableBody${suffix}`);
        const tableTitle = document.getElementById(`kpiTableTitle${suffix}`);

        if (tableTitle) tableTitle.textContent = 'Seleccione un filtro para ver los datos';
        if (tableHead) {
            tableHead.innerHTML = `
                <tr>
                    <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Seleccione un filtro</th>
                </tr>
            `;
        }
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td style="padding: 12px; text-align: center; color: #6b7280; border: 1px solid #e5e7eb;">Use los filtros del panel lateral para ver los datos</td>
                </tr>
            `;
        }
    }

    // =====================================================
    // MÉTODOS PARA VISTA COMPARATIVO
    // =====================================================

    handleCompFilterClick(filterId, button) {
        // Si el mismo botón está activo, desactivarlo
        if (button.classList.contains('active') && this.activeCompFilter === filterId) {
            button.classList.remove('active');
            this.activeCompFilter = null;
            this.showCompEmptyState();
            return;
        }

        // Desactivar todos los botones del panel comparativo
        const container = document.getElementById('comparativo-filters-panel');
        if (container) {
            container.querySelectorAll('.filter-button').forEach(btn => {
                btn.classList.remove('active');
            });
        }

        // Activar el botón clickeado
        button.classList.add('active');
        this.activeCompFilter = filterId;

        // Actualizar tabla según el filtro
        this.updateCompTable(filterId);
    }

    async updateCompTable(filterId) {
        const tableHead = document.getElementById('kpiCompTableHead');
        const tableBody = document.getElementById('kpiCompTableBody');
        const tableTitle = document.getElementById('kpiCompTableTitle');

        if (!tableHead || !tableBody || !tableTitle) return;

        const filterTitles = {
            'btn-comp-retraso-cl': 'PO con Atraso CL - Comparación entre Períodos',
            'btn-comp-adelanto-cl': 'PO con Adelanto CL - Comparación entre Períodos',
            'btn-comp-capacidad': 'Capacidad (Allocation) - Comparación entre Períodos',
            'btn-comp-atd': 'PO con ATD - Comparación entre Períodos',
            'btn-comp-ata': 'PO con ATA - Comparación entre Períodos',
        };

        const filterEndpoints = {
            'btn-comp-retraso-cl': '/compare-delay-cl',
            'btn-comp-adelanto-cl': '/compare-advance-cl',
            'btn-comp-capacidad': '/compare-atd',
            'btn-comp-atd': '/compare-atd',
            'btn-comp-ata': '/compare-ata',
        };

        tableTitle.textContent = filterTitles[filterId] || 'Comparación entre Períodos';

        // Obtener períodos de comparación (compatible con Flatpickr altInput)
        const periodAFrom = this.getDateInputValue(document.getElementById('comp-period-a-from'));
        const periodATo   = this.getDateInputValue(document.getElementById('comp-period-a-to'));
        const periodBFrom = this.getDateInputValue(document.getElementById('comp-period-b-from'));
        const periodBTo   = this.getDateInputValue(document.getElementById('comp-period-b-to'));

        // Validar que todos los períodos estén completos
        if (!periodAFrom || !periodATo || !periodBFrom || !periodBTo) {
            tableHead.innerHTML = `
                <tr>
                    <th style="padding: 12px; text-align: center; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;" colspan="4">
                        Períodos incompletos
                    </th>
                </tr>
            `;
            tableBody.innerHTML = `
                <tr>
                    <td colspan="4" style="text-align: center; padding: 30px; color: #e17055;">
                        <div style="margin-bottom: 8px;">⚠️ Por favor, complete todos los períodos de comparación</div>
                        <div style="font-size: 13px; color: #6b7280;">Seleccione las fechas de inicio y fin para el Período A y el Período B</div>
                    </td>
                </tr>
            `;
            return;
        }

        // Validar que las fechas sean coherentes
        if (new Date(periodAFrom) > new Date(periodATo)) {
            tableBody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: #e17055;">La fecha de inicio del Período A debe ser anterior a la fecha de fin</td></tr>';
            return;
        }
        if (new Date(periodBFrom) > new Date(periodBTo)) {
            tableBody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: #e17055;">La fecha de inicio del Período B debe ser anterior a la fecha de fin</td></tr>';
            return;
        }

        tableBody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px;">Cargando datos...</td></tr>';

        const endpoint = filterEndpoints[filterId];
        if (!endpoint) {
            tableBody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: #e17055;">Filtro no configurado</td></tr>';
            return;
        }

        try {
            // Crear URL con los períodos de comparación
            const result = await this.fetchComparisonData(endpoint, periodAFrom, periodATo, periodBFrom, periodBTo);
            
            if (result && result.success && result.data) {
                this.renderComparisonTable(filterId, result.data, tableHead, tableBody);
            } else {
                // Mostrar datos de ejemplo si el endpoint aún no está implementado
                this.renderComparisonTableFallback(filterId, tableHead, tableBody, periodAFrom, periodATo, periodBFrom, periodBTo);
            }
        } catch (error) {
            console.error('Error loading comparison data:', error);
            this.renderComparisonTableFallback(filterId, tableHead, tableBody, periodAFrom, periodATo, periodBFrom, periodBTo);
        }
    }

    async fetchComparisonData(endpoint, periodAFrom, periodATo, periodBFrom, periodBTo) {
        try {
            const url = new URL(`${this.apiBaseUrl}${endpoint}`, window.location.origin);

            // Filtros globales como query params
            Object.keys(this.currentFilters).forEach(key => {
                const value = this.currentFilters[key];
                if (value !== null && value !== undefined && value !== '') {
                    if (Array.isArray(value)) {
                        value.forEach(v => url.searchParams.append(`${key}[]`, v));
                    } else {
                        url.searchParams.append(key, value);
                    }
                }
            });

            const response = await fetch(url.toString(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify({
                    period1_start: periodAFrom,
                    period1_end:   periodATo,
                    period2_start: periodBFrom,
                    period2_end:   periodBTo,
                }),
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Error fetching comparison data:', error);
            return null;
        }
    }

    renderComparisonTable(filterId, data, tableHead, tableBody) {
        // Backend returns { period1: {start, end, total}, period2: {start, end, total}, by_vendor: [...] }
        const formatLabel = (p) => {
            if (!p || !p.start) return 'Período';
            const from = new Date(p.start + 'T00:00:00');
            const to   = new Date(p.end   + 'T00:00:00');
            const months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
            if (from.getMonth() === to.getMonth() && from.getFullYear() === to.getFullYear()) {
                return `${months[from.getMonth()]} ${from.getFullYear()}`;
            }
            return `${from.getDate()}/${from.getMonth()+1} - ${to.getDate()}/${to.getMonth()+1}`;
        };

        const periodA = formatLabel(data.period1);
        const periodB = formatLabel(data.period2);
        const totalA  = data.period1?.total ?? 0;
        const totalB  = data.period2?.total ?? 0;

        tableHead.innerHTML = `
            <tr>
                <th style="padding:12px;text-align:left;border:1px solid #e5e7eb;font-weight:600;color:#374151;">Proveedor de Mercancía</th>
                <th style="padding:12px;text-align:right;border:1px solid #e5e7eb;font-weight:600;color:#1AAD8A;">PO ${periodA}<br><small style="font-weight:400;color:#6b7280;">Período A</small></th>
                <th style="padding:12px;text-align:right;border:1px solid #e5e7eb;font-weight:600;color:#0984e3;">PO ${periodB}<br><small style="font-weight:400;color:#6b7280;">Período B</small></th>
                <th style="padding:12px;text-align:right;border:1px solid #e5e7eb;font-weight:600;color:#374151;">% Variación</th>
            </tr>
        `;

        const vendors = data.by_vendor || [];
        const isDelay = filterId.includes('retraso');

        let rows = '';
        vendors.forEach(v => {
            const p1  = v.period1_count ?? 0;
            const p2  = v.period2_count ?? 0;
            const pct = v.variation_percentage ?? 0;
            const sign  = pct > 0 ? '+' : '';
            const color = (isDelay ? pct < 0 : pct > 0) ? '#27ae60' : '#e17055';

            rows += `<tr>
                <td style="padding:12px;border:1px solid #e5e7eb;">${v.vendor}</td>
                <td style="padding:12px;border:1px solid #e5e7eb;text-align:right;" class="number">${p1.toLocaleString()}</td>
                <td style="padding:12px;border:1px solid #e5e7eb;text-align:right;" class="number">${p2.toLocaleString()}</td>
                <td style="padding:12px;border:1px solid #e5e7eb;text-align:right;color:${color};">${sign}${pct.toFixed(1)}%</td>
            </tr>`;
        });

        const totalPct   = totalA > 0 ? (((totalB - totalA) / totalA) * 100).toFixed(1) : '0.0';
        const totalSign  = parseFloat(totalPct) > 0 ? '+' : '';
        const totalColor = (isDelay ? parseFloat(totalPct) < 0 : parseFloat(totalPct) > 0) ? '#27ae60' : '#e17055';

        rows += `<tr style="background-color:#f8faf9;font-weight:700;">
            <td style="padding:12px;border:1px solid #e5e7eb;color:#374151;">Total</td>
            <td style="padding:12px;border:1px solid #e5e7eb;text-align:right;font-weight:700;color:#1AAD8A;" class="number">${totalA.toLocaleString()}</td>
            <td style="padding:12px;border:1px solid #e5e7eb;text-align:right;font-weight:700;color:#1AAD8A;" class="number">${totalB.toLocaleString()}</td>
            <td style="padding:12px;border:1px solid #e5e7eb;text-align:right;font-weight:700;color:${totalColor};">${totalSign}${totalPct}%</td>
        </tr>`;

        tableBody.innerHTML = rows;
    }

    renderComparisonTableFallback(filterId, tableHead, tableBody, periodAFrom, periodATo, periodBFrom, periodBTo) {
        // Formatear fechas para mostrar
        const formatPeriod = (from, to) => {
            if (!from || !to) return 'Período';
            const fromDate = new Date(from);
            const toDate = new Date(to);
            const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            
            // Si es el mismo mes y año
            if (fromDate.getMonth() === toDate.getMonth() && fromDate.getFullYear() === toDate.getFullYear()) {
                return `${months[fromDate.getMonth()]} ${fromDate.getFullYear()}`;
            }
            // Si es diferente
            return `${fromDate.getDate()}/${fromDate.getMonth()+1} - ${toDate.getDate()}/${toDate.getMonth()+1}`;
        };

        const periodALabel = formatPeriod(periodAFrom, periodATo);
        const periodBLabel = formatPeriod(periodBFrom, periodBTo);

        // Datos de ejemplo para cada tipo de comparación
        const exampleData = {
            'btn-comp-retraso-cl': {
                vendors: [
                    { name: 'Asia Manufacturing', a: 72, b: 64, var: -11.1 },
                    { name: 'Global Textiles', a: 54, b: 48, var: -11.1 },
                    { name: 'Electronics Corp', a: 48, b: 44, var: -8.3 },
                    { name: 'Premium Goods', a: 12, b: 15, var: 25.0 },
                    { name: 'Fast Logistics', a: 8, b: 6, var: -25.0 },
                ],
                totalA: 194,
                totalB: 177,
                totalVar: -8.8
            },
            'btn-comp-adelanto-cl': {
                vendors: [
                    { name: 'Premium Goods', a: 32, b: 38, var: 18.8 },
                    { name: 'Fast Logistics', a: 28, b: 31, var: 10.7 },
                    { name: 'Quality First', a: 18, b: 20, var: 11.1 },
                    { name: 'Asia Manufacturing', a: 15, b: 12, var: -20.0 },
                    { name: 'Global Textiles', a: 11, b: 9, var: -18.2 },
                ],
                totalA: 104,
                totalB: 110,
                totalVar: 5.8
            },
            'btn-comp-capacidad': {
                vendors: [
                    { name: 'Asia Manufacturing', a: 412, b: 481, var: 16.7 },
                    { name: 'Global Textiles', a: 348, b: 394, var: 13.2 },
                    { name: 'Electronics Corp', a: 225, b: 248, var: 10.2 },
                    { name: 'Premium Goods', a: 78, b: 85, var: 9.0 },
                    { name: 'Fast Logistics', a: 45, b: 40, var: -11.1 },
                ],
                totalA: 1108,
                totalB: 1248,
                totalVar: 12.6
            },
            'btn-comp-atd': {
                vendors: [
                    { name: 'Asia Manufacturing', a: 412, b: 481, var: 16.7 },
                    { name: 'Global Textiles', a: 348, b: 394, var: 13.2 },
                    { name: 'Electronics Corp', a: 225, b: 248, var: 10.2 },
                    { name: 'Premium Goods', a: 78, b: 85, var: 9.0 },
                    { name: 'Fast Logistics', a: 45, b: 40, var: -11.1 },
                ],
                totalA: 1108,
                totalB: 1248,
                totalVar: 12.6
            },
            'btn-comp-ata': {
                vendors: [
                    { name: 'Asia Manufacturing', a: 382, b: 421, var: 10.2 },
                    { name: 'Global Textiles', a: 315, b: 338, var: 7.3 },
                    { name: 'Electronics Corp', a: 198, b: 212, var: 7.1 },
                    { name: 'Premium Goods', a: 68, b: 73, var: 7.4 },
                    { name: 'Fast Logistics', a: 42, b: 38, var: -9.5 },
                ],
                totalA: 1005,
                totalB: 1082,
                totalVar: 7.7
            }
        };

        const data = exampleData[filterId] || exampleData['btn-comp-ata'];

        tableHead.innerHTML = `
            <tr>
                <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Proveedor de Mercancía</th>
                <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #1AAD8A;">PO ${periodALabel}<br><small style="font-weight: 400; color: #6b7280;">Período A</small></th>
                <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #0984e3;">PO ${periodBLabel}<br><small style="font-weight: 400; color: #6b7280;">Período B</small></th>
                <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">% Variación</th>
            </tr>
        `;

        let rows = '';
        data.vendors.forEach(vendor => {
            const variationSign = vendor.var > 0 ? '+' : '';
            const isPositive = filterId.includes('retraso') ? vendor.var < 0 : vendor.var > 0;
            const colorClass = isPositive ? '#27ae60' : '#e17055';
            
            rows += `
                <tr>
                    <td style="padding: 12px; border: 1px solid #e5e7eb;">${vendor.name}</td>
                    <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">${vendor.a.toLocaleString()}</td>
                    <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">${vendor.b.toLocaleString()}</td>
                    <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: ${colorClass};">${variationSign}${vendor.var.toFixed(1)}%</td>
                </tr>
            `;
        });

        const totalVarSign = data.totalVar > 0 ? '+' : '';
        const totalIsPositive = filterId.includes('retraso') ? data.totalVar < 0 : data.totalVar > 0;
        const totalColor = totalIsPositive ? '#27ae60' : '#e17055';

        rows += `
            <tr style="background-color: #f8faf9; font-weight: 700;">
                <td style="padding: 12px; border: 1px solid #e5e7eb; color: #374151;">Total</td>
                <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 700; color: #1AAD8A;">${data.totalA.toLocaleString()}</td>
                <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 700; color: #1AAD8A;">${data.totalB.toLocaleString()}</td>
                <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 700; color: ${totalColor};">${totalVarSign}${data.totalVar.toFixed(1)}%</td>
            </tr>
        `;

        tableBody.innerHTML = rows;
    }

    showCompEmptyState() {
        const tableHead = document.getElementById('kpiCompTableHead');
        const tableBody = document.getElementById('kpiCompTableBody');
        const tableTitle = document.getElementById('kpiCompTableTitle');

        if (tableTitle) tableTitle.textContent = 'Seleccione un indicador para ver la comparación';
        if (tableHead) {
            tableHead.innerHTML = `
                <tr>
                    <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Seleccione un indicador</th>
                </tr>
            `;
        }
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td style="padding: 12px; text-align: center; color: #6b7280; border: 1px solid #e5e7eb;">Use los filtros del panel lateral para ver los datos comparativos</td>
                </tr>
            `;
        }
    }

    async loadProyeccion() {
        const tableHead = document.getElementById('proy-table-head');
        const tableBody = document.getElementById('proy-table-body');

        if (!tableHead || !tableBody) return;

        tableBody.innerHTML = '<tr><td style="text-align:center; padding: 20px; color: #6b7280;">Cargando datos...</td></tr>';

        try {
            const result = await this.fetchData('/future-arrivals');

            if (!result || !result.success || !result.data) {
                tableHead.innerHTML = '<tr><th style="padding: 12px; border: 1px solid #e5e7eb;">Etapa</th></tr>';
                tableBody.innerHTML = '<tr><td style="text-align:center; padding: 20px; color: #6b7280;">No hay datos de proyección disponibles.</td></tr>';
                return;
            }

            this.renderProyeccion(result.data, tableHead, tableBody);
        } catch (error) {
            console.error('Error loading proyeccion:', error);
            tableHead.innerHTML = '<tr><th style="padding: 12px; border: 1px solid #e5e7eb;">Etapa</th></tr>';
            tableBody.innerHTML = '<tr><td style="text-align:center; padding: 20px; color: #e17055;">Error al cargar los datos de proyección.</td></tr>';
        }
    }

    renderProyeccion(data, tableHead, tableBody) {
        const weeks = data.weeks || [];
        const stages = data.data || [];

        if (weeks.length === 0 || stages.length === 0) {
            tableHead.innerHTML = '<tr><th style="padding: 12px; border: 1px solid #e5e7eb;">Etapa</th></tr>';
            tableBody.innerHTML = '<tr><td style="text-align:center; padding: 20px; color: #6b7280;">No hay datos de proyección disponibles para el período seleccionado.</td></tr>';
            ['proy-total-pos', 'proy-produccion-pos', 'proy-booking-pos', 'proy-transito-pos'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.textContent = '0';
            });
            return;
        }

        // Actualizar KPI cards
        const stageColors = { 'Producción': '#565AFF', 'Booking': '#28C7A1', 'Tránsito': '#1AAD8A' };
        const stageSubtitles = { 'Producción': 'CL Teórica estimada', 'Booking': 'ETD proyectado', 'Tránsito': 'ETA confirmado' };
        const cardIds = { 'Producción': 'proy-produccion-pos', 'Booking': 'proy-booking-pos', 'Tránsito': 'proy-transito-pos' };

        let grandTotal = 0;
        stages.forEach(stage => {
            const total = stage.weeks.reduce((sum, w) => sum + (w.po_count || 0), 0);
            grandTotal += total;
            const el = document.getElementById(cardIds[stage.stage]);
            if (el) el.textContent = total.toLocaleString();
        });
        const totalEl = document.getElementById('proy-total-pos');
        if (totalEl) totalEl.textContent = grandTotal.toLocaleString();

        // Cabecera de la tabla
        let headRow = '<tr><th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151; min-width: 120px;">Etapa</th>';
        weeks.forEach(week => {
            headRow += `<th class="align-right" style="padding: 10px 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151; white-space: nowrap;">${week.replace('-', '<br>')}</th>`;
        });
        headRow += '<th class="align-right" style="padding: 10px 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Total</th></tr>';
        tableHead.innerHTML = headRow;

        // Filas por etapa
        const weeklyTotals = new Array(weeks.length).fill(0);
        let rows = '';

        const stageLabels = { 'Producción': 'CL Teórica', 'Booking': 'ETD', 'Tránsito': 'ETA' };
        stages.forEach(stage => {
            const color = stageColors[stage.stage] || '#374151';
            const sublabel = stageLabels[stage.stage] || '';
            let rowTotal = 0;
            let cells = '';

            stage.weeks.forEach((w, i) => {
                const count = w.po_count || 0;
                rowTotal += count;
                weeklyTotals[i] += count;
                cells += `<td class="align-right number" style="padding: 10px 12px; border: 1px solid #e5e7eb; text-align: right; color: #374151;">${count.toLocaleString()}</td>`;
            });

            rows += `
                <tr>
                    <td style="padding: 10px 12px; border: 1px solid #e5e7eb;">
                        <span style="display:inline-block; padding: 2px 8px; border-radius: 4px; background: ${color}20; color: ${color}; font-weight: 600; font-size: 12px;">${stage.stage}</span>
                        <br><small style="color: #6b7280; font-size: 11px;">${sublabel}</small>
                    </td>
                    ${cells}
                    <td class="align-right number" style="padding: 10px 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 700; color: ${color};">${rowTotal.toLocaleString()}</td>
                </tr>`;
        });

        // Fila total
        let totalCells = weeklyTotals.map(t => `<td class="align-right number" style="padding: 10px 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 700; color: #1AAD8A;">${t.toLocaleString()}</td>`).join('');
        rows += `
            <tr style="background-color: #f8faf9;">
                <td style="padding: 10px 12px; border: 1px solid #e5e7eb; font-weight: 700; color: #374151;">Total por Semana</td>
                ${totalCells}
                <td class="align-right number" style="padding: 10px 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 700; color: #1AAD8A;">${grandTotal.toLocaleString()}</td>
            </tr>`;

        tableBody.innerHTML = rows;
    }
    async loadPoVsTeus() {
        const ids = ['stage', 'period', 'vendor', 'line'];
        ids.forEach(id => {
            const body = document.getElementById(`povsteus-${id}-body`);
            if (body) body.innerHTML = '<tr><td style="text-align:center;padding:20px;color:#6b7280;">Cargando datos...</td></tr>';
        });

        try {
            const [stageRes, periodRes, vendorRes, lineRes] = await Promise.all([
                this.fetchData('/po-vs-teus/stage'),
                this.fetchData('/po-vs-teus/period'),
                this.fetchData('/po-vs-teus/vendor'),
                this.fetchData('/po-vs-teus/shipping-line'),
            ]);

            // KPI cards desde etapas (total_pos / total_teus)
            const totalPos  = stageRes?.data?.total_pos  ?? 0;
            const totalTeus = stageRes?.data?.total_teus ?? 0;
            this._setText('povsteus-total-pos',  totalPos.toLocaleString());
            this._setText('povsteus-total-teus', totalTeus.toLocaleString());

            // Variaciones desde períodos
            const weekVar  = periodRes?.data?.week_variation;
            const monthVar = periodRes?.data?.month_variation;
            this._setText('povsteus-week-variation',  weekVar  != null ? (weekVar  >= 0 ? '+' : '') + weekVar  + '%' : '-');
            this._setText('povsteus-month-variation', monthVar != null ? (monthVar >= 0 ? '+' : '') + monthVar + '%' : '-');

            this.renderPoVsTeusByStage(stageRes?.data,   'povsteus-stage-head',  'povsteus-stage-body');
            this.renderPoVsTeusByPeriod(periodRes?.data, 'povsteus-period-head', 'povsteus-period-body');
            this.renderPoVsTeusByGroup(vendorRes?.data,  'vendor', 'Proveedor de Mercancía', 'povsteus-vendor-head', 'povsteus-vendor-body');
            this.renderPoVsTeusByGroup(lineRes?.data,    'shipping_line', 'Naviera', 'povsteus-line-head', 'povsteus-line-body');

        } catch (error) {
            console.error('Error loading PO vs TEUs:', error);
            ids.forEach(id => {
                const head = document.getElementById(`povsteus-${id}-head`);
                const body = document.getElementById(`povsteus-${id}-body`);
                if (head) head.innerHTML = '<tr><th style="padding:12px;border:1px solid #e5e7eb;">Error</th></tr>';
                if (body) body.innerHTML = '<tr><td style="text-align:center;padding:20px;color:#e17055;">Error al cargar los datos.</td></tr>';
            });
        }
    }

    _setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    renderPoVsTeusByStage(data, headId, bodyId) {
        const head = document.getElementById(headId);
        const body = document.getElementById(bodyId);
        if (!head || !body) return;

        const rows = data?.data ?? [];
        if (rows.length === 0) {
            head.innerHTML = '<tr><th style="padding:12px;border:1px solid #e5e7eb;">Etapa</th></tr>';
            body.innerHTML = '<tr><td style="text-align:center;padding:20px;color:#6b7280;">Sin datos disponibles.</td></tr>';
            return;
        }

        head.innerHTML = `<tr>
            <th style="padding:12px;border:1px solid #e5e7eb;text-align:left;">Etapa</th>
            <th style="padding:12px;border:1px solid #e5e7eb;text-align:right;">Cantidad de PO</th>
            <th style="padding:12px;border:1px solid #e5e7eb;text-align:right;">Cantidad de TEUs</th>
            <th style="padding:12px;border:1px solid #e5e7eb;text-align:right;">% POs</th>
        </tr>`;

        const rowsHtml = rows.map(r => `<tr>
            <td style="padding:10px 12px;border:1px solid #e5e7eb;">${r.stage}</td>
            <td style="padding:10px 12px;border:1px solid #e5e7eb;text-align:right;" class="number">${r.po_count.toLocaleString()}</td>
            <td style="padding:10px 12px;border:1px solid #e5e7eb;text-align:right;" class="number">${(r.teus ?? 0).toLocaleString()}</td>
            <td style="padding:10px 12px;border:1px solid #e5e7eb;text-align:right;" class="percentage">${r.percentage}%</td>
        </tr>`).join('');

        const total = data.total_pos ?? 0;
        const totalTeus = data.total_teus ?? 0;
        body.innerHTML = rowsHtml + `<tr style="background-color:#f8faf9;font-weight:700;">
            <td style="padding:10px 12px;border:1px solid #e5e7eb;color:#374151;">Total</td>
            <td style="padding:10px 12px;border:1px solid #e5e7eb;text-align:right;color:#1AAD8A;">${total.toLocaleString()}</td>
            <td style="padding:10px 12px;border:1px solid #e5e7eb;text-align:right;color:#1AAD8A;">${(+totalTeus).toLocaleString()}</td>
            <td style="padding:10px 12px;border:1px solid #e5e7eb;text-align:right;color:#0984e3;">100%</td>
        </tr>`;
    }

    renderPoVsTeusByPeriod(data, headId, bodyId) {
        const head = document.getElementById(headId);
        const body = document.getElementById(bodyId);
        if (!head || !body) return;

        const periods = data?.periods ?? [];
        if (periods.length === 0) {
            head.innerHTML = '<tr><th style="padding:12px;border:1px solid #e5e7eb;">Período</th></tr>';
            body.innerHTML = '<tr><td style="text-align:center;padding:20px;color:#6b7280;">Sin datos disponibles.</td></tr>';
            return;
        }

        head.innerHTML = `<tr>
            <th style="padding:12px;border:1px solid #e5e7eb;text-align:left;">Período</th>
            <th style="padding:12px;border:1px solid #e5e7eb;text-align:right;">Cantidad de PO</th>
            <th style="padding:12px;border:1px solid #e5e7eb;text-align:right;">Cantidad de TEUs</th>
        </tr>`;

        body.innerHTML = periods.map(p => `<tr>
            <td style="padding:10px 12px;border:1px solid #e5e7eb;">${p.period}</td>
            <td style="padding:10px 12px;border:1px solid #e5e7eb;text-align:right;" class="number">${(p.po_count ?? 0).toLocaleString()}</td>
            <td style="padding:10px 12px;border:1px solid #e5e7eb;text-align:right;" class="number">${(p.teus ?? 0).toLocaleString()}</td>
        </tr>`).join('');
    }

    renderPoVsTeusByGroup(data, key, label, headId, bodyId) {
        const head = document.getElementById(headId);
        const body = document.getElementById(bodyId);
        if (!head || !body) return;

        const rows = data?.data ?? [];
        if (rows.length === 0) {
            head.innerHTML = `<tr><th style="padding:12px;border:1px solid #e5e7eb;">${label}</th></tr>`;
            body.innerHTML = '<tr><td style="text-align:center;padding:20px;color:#6b7280;">Sin datos disponibles.</td></tr>';
            return;
        }

        head.innerHTML = `<tr>
            <th style="padding:12px;border:1px solid #e5e7eb;text-align:left;">${label}</th>
            <th style="padding:12px;border:1px solid #e5e7eb;text-align:right;">Cantidad de PO</th>
            <th style="padding:12px;border:1px solid #e5e7eb;text-align:right;">Cantidad de TEUs</th>
            <th style="padding:12px;border:1px solid #e5e7eb;text-align:right;">% Participación</th>
        </tr>`;

        const total = data.total_pos ?? 0;
        const totalTeus = data.total_teus ?? 0;

        const rowsHtml = rows.map(r => {
            const pct = total > 0 ? ((r.po_count / total) * 100).toFixed(1) : '0.0';
            return `<tr>
                <td style="padding:10px 12px;border:1px solid #e5e7eb;">${r[key] ?? '-'}</td>
                <td style="padding:10px 12px;border:1px solid #e5e7eb;text-align:right;" class="number">${r.po_count.toLocaleString()}</td>
                <td style="padding:10px 12px;border:1px solid #e5e7eb;text-align:right;" class="number">${(r.teus ?? 0).toLocaleString()}</td>
                <td style="padding:10px 12px;border:1px solid #e5e7eb;text-align:right;" class="percentage">${pct}%</td>
            </tr>`;
        }).join('');

        body.innerHTML = rowsHtml + `<tr style="background-color:#f8faf9;font-weight:700;">
            <td style="padding:10px 12px;border:1px solid #e5e7eb;color:#374151;">Total</td>
            <td style="padding:10px 12px;border:1px solid #e5e7eb;text-align:right;color:#1AAD8A;">${total.toLocaleString()}</td>
            <td style="padding:10px 12px;border:1px solid #e5e7eb;text-align:right;color:#1AAD8A;">${(+totalTeus).toLocaleString()}</td>
            <td style="padding:10px 12px;border:1px solid #e5e7eb;text-align:right;color:#0984e3;">100%</td>
        </tr>`;
    }
}

// Expandable rows function
function toggleSubTable(row) {
    const subTableRow = row.nextElementSibling;
    const expandIcon = row.querySelector('.expand-icon');
    
    if (!subTableRow || !subTableRow.classList.contains('sub-table-row')) return;
    
    if (subTableRow.classList.contains('visible')) {
        subTableRow.classList.remove('visible');
        row.classList.remove('expanded');
    } else {
        subTableRow.classList.add('visible');
        row.classList.add('expanded');
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.dashboardKPIManager = new DashboardKPIManager();
});
