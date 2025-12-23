// Dashboard KPI - Funcionalidad de filtros adicionales
class DashboardKPIManager {
    constructor() {
        this.activeFilter = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
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
    }

    handleFilterClick(filterId, button, type = 'po') {
        // Si el mismo botón está activo, desactivarlo
        if (button.classList.contains('active') && this.activeFilter === filterId) {
            button.classList.remove('active');
            this.activeFilter = null;
            this.showEmptyState(type);
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

    updateTable(filterId, type = 'po') {
        const suffix = type === 'teus' ? 'Teus' : '';
        const tableHead = document.getElementById(`kpiTrendTableHead${suffix}`);
        const tableBody = document.getElementById(`kpiTrendTableBody${suffix}`);
        const tableTitle = document.getElementById(`kpiTableTitle${suffix}`);

        if (!tableHead || !tableBody || !tableTitle) return;

        // Datos dummy para cada filtro
        const filterData = this.getFilterData(filterId, type);

        // Actualizar título
        tableTitle.textContent = filterData.title;

        // Actualizar encabezados
        tableHead.innerHTML = filterData.headers;

        // Actualizar cuerpo
        tableBody.innerHTML = filterData.rows;
    }

    getFilterData(filterId, type = 'po') {
        if (type === 'teus') {
            return this.getTeusFilterData(filterId);
        }
        
        const data = {
            'btn-kpi-po-retraso-cl': {
                title: 'POs con Retraso según Carga Lista (CL)',
                headers: `
                    <tr>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Proveedor de Mercancía</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Cantidad de PO</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Días Prom. Atraso</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Máximo Atraso</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">% del Total PO</th>
                    </tr>
                `,
                rows: `
                    <tr class="expandable-row" onclick="toggleSubTable(this)">
                        <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="expand-icon">▶</span>Proveedor Asia Manufacturing</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">64</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">8.2 días</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">21 días</td>
                        <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">41.0%</td>
                    </tr>
                    <tr class="sub-table-row">
                        <td colspan="5" class="sub-table-cell" style="padding: 0;">
                            <table class="sub-table" style="width: 100%; margin: 12px 0; border: 1px solid #e8edec; border-radius: 6px; overflow: hidden;">
                                <thead style="background: #f0f3f2;">
                                    <tr>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Número de PO</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Días de Atraso</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Etapa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="padding: 10px 12px; font-size: 13px;">PO-2025-001234</td>
                                        <td class="days" style="padding: 10px 12px; font-size: 13px; color: #e17055;">12 días</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">Producción</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 12px; font-size: 13px;">PO-2025-001567</td>
                                        <td class="days" style="padding: 10px 12px; font-size: 13px; color: #e17055;">6 días</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">Producción</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr class="expandable-row" onclick="toggleSubTable(this)">
                        <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="expand-icon">▶</span>Proveedor Global Textiles</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">48</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">5.7 días</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">15 días</td>
                        <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">30.8%</td>
                    </tr>
                    <tr class="expandable-row" onclick="toggleSubTable(this)">
                        <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="expand-icon">▶</span>Proveedor Electronics Corp</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">44</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">4.3 días</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">11 días</td>
                        <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">28.2%</td>
                    </tr>
                `
            },
            'btn-kpi-po-adelanto-cl': {
                title: 'POs con Adelanto según Carga Lista (CL)',
                headers: `
                    <tr>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Proveedor de Mercancía</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Cantidad de PO</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Días Prom. Adelanto</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Máximo Adelanto</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">% del Total PO</th>
                    </tr>
                `,
                rows: `
                    <tr class="expandable-row" onclick="toggleSubTable(this)">
                        <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="expand-icon">▶</span>Proveedor Premium Goods</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">38</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">3.5 días</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">9 días</td>
                        <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">42.7%</td>
                    </tr>
                    <tr class="sub-table-row">
                        <td colspan="5" class="sub-table-cell" style="padding: 0;">
                            <table class="sub-table" style="width: 100%; margin: 12px 0; border: 1px solid #e8edec; border-radius: 6px; overflow: hidden;">
                                <thead style="background: #f0f3f2;">
                                    <tr>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Número de PO</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Días de Adelanto</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Etapa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="padding: 10px 12px; font-size: 13px;">PO-2025-004521</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">5 días</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">Producción</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 12px; font-size: 13px;">PO-2025-004689</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">2 días</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">Booking</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr class="expandable-row" onclick="toggleSubTable(this)">
                        <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="expand-icon">▶</span>Proveedor Fast Logistics</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">31</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">2.8 días</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">7 días</td>
                        <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">34.8%</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Proveedor Quality First</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">20</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">2.1 días</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">5 días</td>
                        <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">22.5%</td>
                    </tr>
                `
            },
            'btn-kpi-indicador-capacidad': {
                title: 'Capacidad (Allocation)',
                headers: `
                    <tr>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Proveedor de Mercancía</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Proveedor de Servicio</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Cantidad de PO</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">% Participación</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Días Prom. Tránsito</th>
                    </tr>
                `,
                rows: `
                    <tr class="expandable-row" onclick="toggleSubTable(this)">
                        <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="expand-icon">▶</span>Asia Manufacturing</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Linktech Logistics</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">156</td>
                        <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">38.5%</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">28.3 días</td>
                    </tr>
                    <tr class="sub-table-row">
                        <td colspan="5" class="sub-table-cell" style="padding: 0;">
                            <table class="sub-table" style="width: 100%; margin: 12px 0; border: 1px solid #e8edec; border-radius: 6px; overflow: hidden;">
                                <thead style="background: #f0f3f2;">
                                    <tr>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Número de PO</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Naviera</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Días de Tránsito</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="padding: 10px 12px; font-size: 13px;">PO-2025-006781</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">Maersk Line</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">26 días</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 12px; font-size: 13px;">PO-2025-006892</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">MSC</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">31 días</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr class="expandable-row" onclick="toggleSubTable(this)">
                        <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="expand-icon">▶</span>Global Textiles</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Express Freight</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">128</td>
                        <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">31.6%</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">25.7 días</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Electronics Corp</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Global Shipping Co</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">121</td>
                        <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">29.9%</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">30.2 días</td>
                    </tr>
                `
            },
            'btn-kpi-pos-transbordo': {
                title: 'POs en Puerto de Transbordo',
                headers: `
                    <tr>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Número de PO</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Proveedor Mercancía</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Proveedor Servicio</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Naviera</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Puerto Transbordo</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">N° Transbordo</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Días Transcurridos</th>
                    </tr>
                `,
                rows: `
                    <tr>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">PO-2025-008456</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Asia Manufacturing</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Linktech Logistics</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Maersk Line</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Busan, Corea del Sur</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">2</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">3 días</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">PO-2025-008567</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Global Textiles</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Express Freight</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">MSC</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Manzanillo, Panamá</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">2</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">2 días</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">PO-2025-008678</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Electronics Corp</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Global Shipping Co</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">CMA CGM</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Cartagena, Colombia</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">3</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">5 días</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">PO-2025-008789</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Premium Goods</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Linktech Logistics</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Hapag-Lloyd</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Busan, Corea del Sur</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">2</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">1 día</td>
                    </tr>
                `
            },
            'btn-kpi-pos-ata': {
                title: 'POs con ATA (Puerto de Destino)',
                headers: `
                    <tr>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Proveedor de Mercancía</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Proveedor de Servicio</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Cantidad de PO</th>
                    </tr>
                `,
                rows: `
                    <tr class="expandable-row" onclick="toggleSubTable(this)">
                        <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="expand-icon">▶</span>Asia Manufacturing</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Linktech Logistics</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">198</td>
                    </tr>
                    <tr class="sub-table-row">
                        <td colspan="3" class="sub-table-cell" style="padding: 0;">
                            <table class="sub-table" style="width: 100%; margin: 12px 0; border: 1px solid #e8edec; border-radius: 6px; overflow: hidden;">
                                <thead style="background: #f0f3f2;">
                                    <tr>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Número de PO</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Naviera</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Puerto de Descarga</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Fecha ATA Real</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="padding: 10px 12px; font-size: 13px;">PO-2025-009123</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">Maersk Line</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">Limón-Moín, CR</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">2025-12-08</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 12px; font-size: 13px;">PO-2025-009234</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">MSC</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">Limón-Moín, CR</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">2025-12-10</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr class="expandable-row" onclick="toggleSubTable(this)">
                        <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="expand-icon">▶</span>Global Textiles</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Express Freight</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">165</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Electronics Corp</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Global Shipping Co</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">179</td>
                    </tr>
                `
            }
        };

        return data[filterId] || { title: '', headers: '', rows: '' };
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

    getTeusFilterData(filterId) {
        const data = {
            'btn-kpi-teus-retraso-cl': {
                title: 'TEUs con Retraso según Carga Lista (CL)',
                headers: `
                    <tr>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Proveedor de Mercancía</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Cantidad de TEUs</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Días Prom. Atraso</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Máximo Atraso</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">% del Total TEUs</th>
                    </tr>
                `,
                rows: `
                    <tr class="expandable-row" onclick="toggleSubTable(this)">
                        <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="expand-icon">▶</span>Proveedor Asia Manufacturing</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">142</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">8.2 días</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">21 días</td>
                        <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">40.8%</td>
                    </tr>
                    <tr class="sub-table-row">
                        <td colspan="5" class="sub-table-cell" style="padding: 0;">
                            <table class="sub-table" style="width: 100%; margin: 12px 0; border: 1px solid #e8edec; border-radius: 6px; overflow: hidden;">
                                <thead style="background: #f0f3f2;">
                                    <tr>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Número de PO</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Cantidad de TEUs</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Días de Atraso</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Etapa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="padding: 10px 12px; font-size: 13px;">PO-2025-001234</td>
                                        <td class="number" style="padding: 10px 12px; font-size: 13px; font-weight: 600; color: #1AAD8A;">4</td>
                                        <td class="days" style="padding: 10px 12px; font-size: 13px; color: #e17055;">12 días</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">Producción</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 12px; font-size: 13px;">PO-2025-001567</td>
                                        <td class="number" style="padding: 10px 12px; font-size: 13px; font-weight: 600; color: #1AAD8A;">2</td>
                                        <td class="days" style="padding: 10px 12px; font-size: 13px; color: #e17055;">6 días</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">Producción</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr class="expandable-row" onclick="toggleSubTable(this)">
                        <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="expand-icon">▶</span>Proveedor Global Textiles</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">108</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">5.7 días</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">15 días</td>
                        <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">31.0%</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Proveedor Electronics Corp</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">98</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">4.3 días</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">11 días</td>
                        <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">28.2%</td>
                    </tr>
                `
            },
            'btn-kpi-teus-adelanto-cl': {
                title: 'TEUs con Adelanto según Carga Lista (CL)',
                headers: `
                    <tr>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Proveedor de Mercancía</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Cantidad de TEUs</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Días Prom. Adelanto</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Máximo Adelanto</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">% del Total TEUs</th>
                    </tr>
                `,
                rows: `
                    <tr class="expandable-row" onclick="toggleSubTable(this)">
                        <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="expand-icon">▶</span>Proveedor Premium Goods</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">86</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">3.5 días</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">9 días</td>
                        <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">43.4%</td>
                    </tr>
                    <tr class="sub-table-row">
                        <td colspan="5" class="sub-table-cell" style="padding: 0;">
                            <table class="sub-table" style="width: 100%; margin: 12px 0; border: 1px solid #e8edec; border-radius: 6px; overflow: hidden;">
                                <thead style="background: #f0f3f2;">
                                    <tr>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Número de PO</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Cantidad de TEUs</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Días de Adelanto</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Etapa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="padding: 10px 12px; font-size: 13px;">PO-2025-004521</td>
                                        <td class="number" style="padding: 10px 12px; font-size: 13px; font-weight: 600; color: #1AAD8A;">4</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">5 días</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">Producción</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 12px; font-size: 13px;">PO-2025-004689</td>
                                        <td class="number" style="padding: 10px 12px; font-size: 13px; font-weight: 600; color: #1AAD8A;">2</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">2 días</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">Booking</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr class="expandable-row" onclick="toggleSubTable(this)">
                        <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="expand-icon">▶</span>Proveedor Fast Logistics</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">70</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">2.8 días</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">7 días</td>
                        <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">35.4%</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Proveedor Quality First</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">42</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">2.1 días</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">5 días</td>
                        <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">21.2%</td>
                    </tr>
                `
            },
            'btn-kpi-teus-capacidad': {
                title: 'Capacidad (Allocation) - TEUs',
                headers: `
                    <tr>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Proveedor de Mercancía</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Proveedor de Servicio</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Cantidad de TEUs</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">% Participación</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Días Prom. Tránsito</th>
                    </tr>
                `,
                rows: `
                    <tr class="expandable-row" onclick="toggleSubTable(this)">
                        <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="expand-icon">▶</span>Asia Manufacturing</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Linktech Logistics</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">356</td>
                        <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">38.5%</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">28.3 días</td>
                    </tr>
                    <tr class="sub-table-row">
                        <td colspan="5" class="sub-table-cell" style="padding: 0;">
                            <table class="sub-table" style="width: 100%; margin: 12px 0; border: 1px solid #e8edec; border-radius: 6px; overflow: hidden;">
                                <thead style="background: #f0f3f2;">
                                    <tr>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Número de PO</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Cantidad de TEUs</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Naviera</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Días de Tránsito</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="padding: 10px 12px; font-size: 13px;">PO-2025-006781</td>
                                        <td class="number" style="padding: 10px 12px; font-size: 13px; font-weight: 600; color: #1AAD8A;">4</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">Maersk Line</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">26 días</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 12px; font-size: 13px;">PO-2025-006892</td>
                                        <td class="number" style="padding: 10px 12px; font-size: 13px; font-weight: 600; color: #1AAD8A;">6</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">MSC</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">31 días</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr class="expandable-row" onclick="toggleSubTable(this)">
                        <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="expand-icon">▶</span>Global Textiles</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Express Freight</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">292</td>
                        <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">31.6%</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">25.7 días</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Electronics Corp</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Global Shipping Co</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">276</td>
                        <td class="align-right percentage" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #0984e3; font-weight: 500;">29.9%</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">30.2 días</td>
                    </tr>
                `
            },
            'btn-kpi-teus-transbordo': {
                title: 'TEUs en Puerto de Transbordo',
                headers: `
                    <tr>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Número de PO</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Cantidad de TEUs</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Proveedor Mercancía</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Proveedor Servicio</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Naviera</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Puerto Transbordo</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">N° Transbordo</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Días Transcurridos</th>
                    </tr>
                `,
                rows: `
                    <tr>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">PO-2025-008456</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">4</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Asia Manufacturing</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Linktech Logistics</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Maersk Line</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Busan, Corea del Sur</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">2</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">3 días</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">PO-2025-008567</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">2</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Global Textiles</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Express Freight</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">MSC</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Manzanillo, Panamá</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">2</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">2 días</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">PO-2025-008678</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">6</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Electronics Corp</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Global Shipping Co</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">CMA CGM</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Cartagena, Colombia</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">3</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">5 días</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">PO-2025-008789</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">2</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Premium Goods</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Linktech Logistics</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Hapag-Lloyd</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Busan, Corea del Sur</td>
                        <td class="align-right" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">2</td>
                        <td class="align-right days" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #e17055;">1 día</td>
                    </tr>
                `
            },
            'btn-kpi-teus-ata': {
                title: 'TEUs con ATA (Puerto de Destino)',
                headers: `
                    <tr>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Proveedor de Mercancía</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Proveedor de Servicio</th>
                        <th class="align-right" style="padding: 12px; text-align: right; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Cantidad de TEUs</th>
                    </tr>
                `,
                rows: `
                    <tr class="expandable-row" onclick="toggleSubTable(this)">
                        <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="expand-icon">▶</span>Asia Manufacturing</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Linktech Logistics</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">452</td>
                    </tr>
                    <tr class="sub-table-row">
                        <td colspan="3" class="sub-table-cell" style="padding: 0;">
                            <table class="sub-table" style="width: 100%; margin: 12px 0; border: 1px solid #e8edec; border-radius: 6px; overflow: hidden;">
                                <thead style="background: #f0f3f2;">
                                    <tr>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Número de PO</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Cantidad de TEUs</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Naviera</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Puerto de Descarga</th>
                                        <th style="padding: 10px 12px; color: #2d3436; font-size: 12px;">Fecha ATA Real</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="padding: 10px 12px; font-size: 13px;">PO-2025-009123</td>
                                        <td class="number" style="padding: 10px 12px; font-size: 13px; font-weight: 600; color: #1AAD8A;">4</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">Maersk Line</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">Limón-Moín, CR</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">2025-12-08</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 12px; font-size: 13px;">PO-2025-009234</td>
                                        <td class="number" style="padding: 10px 12px; font-size: 13px; font-weight: 600; color: #1AAD8A;">2</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">MSC</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">Limón-Moín, CR</td>
                                        <td style="padding: 10px 12px; font-size: 13px;">2025-12-10</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr class="expandable-row" onclick="toggleSubTable(this)">
                        <td style="padding: 12px; border: 1px solid #e5e7eb;"><span class="expand-icon">▶</span>Global Textiles</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Express Freight</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">376</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Electronics Corp</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;">Global Shipping Co</td>
                        <td class="align-right number" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: #1AAD8A;">417</td>
                    </tr>
                `
            }
        };

        return data[filterId] || { title: '', headers: '', rows: '' };
    }
}

// Expandable rows function (reutilizada del script original)
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

// Also handle case where script loads after DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        window.dashboardKPIManager = new DashboardKPIManager();
    });
} else {
    window.dashboardKPIManager = new DashboardKPIManager();
}

