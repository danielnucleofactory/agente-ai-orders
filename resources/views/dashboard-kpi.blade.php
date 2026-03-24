<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/dashboard-kpi.css') }}">
        <style>
            /* Estilos específicos para inputs de períodos de comparación - sobrescribir Flatpickr */
            .comparison-period-filters input.flatpickr-alt-input {
                width: 150px !important;
                height: 40px !important;
                padding: 8px 14px !important;
                border: 2px solid #28C7A1 !important;
                border-radius: 10px !important;
                font-size: 16px !important;
                font-family: 'Lato', sans-serif !important;
                box-sizing: border-box !important;
                margin: 0 !important;
            }
            
            /* Animación para el spinner del botón de descarga */
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            
            #export-btn:disabled {
                pointer-events: none;
            }
        </style>
    @endpush

    <div class="dashboard-kpi-container">
        <!-- Filtros Globales -->
        <div class="filters-section" id="filtersSection" style="display: flex; align-items: flex-end; gap: 16px; flex-wrap: wrap; font-family: 'Lato', sans-serif; margin-bottom: 24px; background: transparent; border: none; padding: 0;">
            <div class="filter-group" x-data="datePicker('')">
                <label class="filter-label">Fecha inicio</label>
                <input x-ref="picker" type="text" id="filter-date-from" class="date-input filter-input" value="">
            </div>
            <div class="filter-group" x-data="datePicker('')">
                <label class="filter-label">Fecha fin</label>
                <input x-ref="picker" type="text" id="filter-date-to" class="date-input filter-input" value="">
            </div>
            <div class="filter-group">
                <label class="filter-label">Cliente</label>
                <select id="filter-trading-company" class="filter-input">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Etapa</label>
                <select id="filter-stage" class="filter-input">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Proveedor de Mercancía</label>
                <select id="filter-vendor-id" class="filter-input">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Proveedor de Servicio</label>
                <select id="filter-service-provider" class="filter-input">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Puerto de Embarque</label>
                <select id="filter-departure-port" class="filter-input">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Puerto de Arribo</label>
                <select id="filter-arrival-port" class="filter-input">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Naviera</label>
                <select id="filter-shipping-line" class="filter-input">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Ruta Logística</label>
                <select id="filter-route-label" class="filter-input">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Número de PO</label>
                <input type="text" id="filter-order-number" placeholder="Buscar PO..." class="date-input filter-input">
            </div>
            <div class="action-buttons" style="display: flex; gap: 16px; align-items: flex-end; margin-left: auto;">
                <button class="btn-primary" id="btn-apply-filters" style="height: 40px; min-width: 100px; padding: 0 18px; font-size: 16px; border-radius: 8px; border: none; background: #1AAD8A; color: #F7F7F7; font-weight: 700; font-family: 'Lato', sans-serif; display: flex; align-items: center; justify-content: center; cursor: pointer;">Aceptar</button>
                <button id="export-btn" class="btn-secondary" style="height: 40px; min-width: 100px; padding: 0 18px; font-size: 16px; border-radius: 8px; border: 2px solid #1AAD8A; background: #fff; color: #1AAD8A; font-weight: 700; font-family: 'Lato', sans-serif; display: flex; align-items: center; gap: 8px; justify-content: center; cursor: pointer;">
                    <i class="fas fa-download"></i>
                    Descargar
                </button>
            </div>
        </div>

        <!-- Tabs de Vistas -->
        <div class="tabs-container">
            <div class="tabs">
                <button class="tab active" data-view="tendencia">Tiempo real</button>
                <button class="tab" data-view="po-vs-teus">PO / TEUs</button>
                <button class="tab" data-view="comparativo">Comparación por Períodos</button>
                <button class="tab" data-view="proyeccion">Proyección</button>
            </div>
        </div>

        <!-- Sub-tabs para Tiempo real -->
        <div class="subtabs-container active" id="subtabs-tendencia">
            <div class="subtabs">
                <button class="subtab active" data-subtab="tendencia-po">PO</button>
                <button class="subtab" data-subtab="tendencia-teus">TEUs</button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            
            <!-- VISTA: TENDENCIA - PO -->
            <div class="view-content active" id="tendencia-po">
                
                <!-- KPI Cards -->
                <div class="kpi-cards">
                    <div class="kpi-card">
                        <div class="kpi-card-title">Total PO</div>
                        <div class="kpi-card-value" id="kpi-total-pos">-</div>
                        <div class="kpi-card-subtitle">Órdenes activas</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-card-title">PO con Retraso</div>
                        <div class="kpi-card-value" id="kpi-delay-count">-</div>
                        <div class="kpi-card-subtitle" id="kpi-delay-percentage">-</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-card-title">PO con Adelanto</div>
                        <div class="kpi-card-value" id="kpi-advance-count">-</div>
                        <div class="kpi-card-subtitle" id="kpi-advance-percentage">-</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-card-title">PO con ATA</div>
                        <div class="kpi-card-value" id="kpi-ata-count">-</div>
                        <div class="kpi-card-subtitle">Arribos confirmados</div>
                    </div>
                </div>

                <!-- Tabla Principal con Panel de Filtros -->
                <div class="trend-table-section" style="position: relative; width: calc(100% + 5rem); max-width: calc(100% + 5rem); margin-left: -2.5rem; margin-right: -2.5rem; padding-left: 2.5rem; padding-right: 2.5rem; box-sizing: border-box; margin-top: 32px;">
                    <div style="display: flex; gap: 16px; width: 100%; box-sizing: border-box;">
                        <!-- Tabla Principal - 2/3 width -->
                        <div class="table-card" style="display: flex; flex-direction: column; width: 66.67%; box-sizing: border-box; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <h3 class="chart-title" style="text-align: center; margin-bottom: 16px; font-size: 18px; font-weight: 600; color: #374151; font-family: 'Lato', sans-serif;" id="kpiTableTitle">Seleccione un filtro para ver los datos</h3>
                            <div class="table-container" style="overflow-x: auto; width: 100%; max-width: 100%; box-sizing: border-box;">
                                <table class="data-table" id="kpiTrendTable" style="width: 100%; border-collapse: collapse;">
                                    <thead id="kpiTrendTableHead">
                                        <tr>
                                            <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Seleccione un filtro</th>
                                        </tr>
                                    </thead>
                                    <tbody id="kpiTrendTableBody">
                                        <tr>
                                            <td style="padding: 12px; text-align: center; color: #6b7280; border: 1px solid #e5e7eb;">Use los filtros del panel lateral para ver los datos</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Panel de Filtros Adicionales - 1/3 width -->
                        <div class="filters-panel" style="width: 33.33%; box-sizing: border-box;">
                            <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                <h3 style="font-size: 18px; font-weight: 600; color: #374151; margin-bottom: 20px; font-family: 'Lato', sans-serif;">Filtros Adicionales</h3>
                                
                                <!-- Additional Filters Buttons -->
                                <div class="additional-filters-buttons" style="display: flex; flex-direction: column; gap: 12px;">
                                    <button type="button" class="filter-button" id="btn-kpi-po-retraso-cl" data-filter="po_retraso_cl" style="width: 100%; min-height: 60px; padding: 12px 16px; font-size: 14px; border-radius: 8px; border: 2px solid #28C7A1; background: #fff; color: #1AAD8A; font-weight: 600; font-family: 'Lato', sans-serif; cursor: pointer; transition: all 0.2s; text-align: left; display: flex; flex-direction: column; justify-content: center;">
                                        <span class="filter-button-title" style="line-height: 1.2; font-size: 15px;">PO Retraso CL</span>
                                        <span class="filter-button-subtitle" style="font-size: 12px; color: #6b7280; display: block; font-weight: 400; margin-top: 4px; line-height: 1.3;">Carga lista real posterior a la planificada.</span>
                                    </button>
                                    <button type="button" class="filter-button" id="btn-kpi-po-adelanto-cl" data-filter="po_adelanto_cl" style="width: 100%; min-height: 60px; padding: 12px 16px; font-size: 14px; border-radius: 8px; border: 2px solid #28C7A1; background: #fff; color: #1AAD8A; font-weight: 600; font-family: 'Lato', sans-serif; cursor: pointer; transition: all 0.2s; text-align: left; display: flex; flex-direction: column; justify-content: center;">
                                        <span class="filter-button-title" style="line-height: 1.2; font-size: 15px;">PO Adelanto CL</span>
                                        <span class="filter-button-subtitle" style="font-size: 12px; color: #6b7280; display: block; font-weight: 400; margin-top: 4px; line-height: 1.3;">Carga lista real previa a la planificada.</span>
                                    </button>
                                    <button type="button" class="filter-button" id="btn-kpi-indicador-capacidad" data-filter="indicador_capacidad" style="width: 100%; min-height: 60px; padding: 12px 16px; font-size: 14px; border-radius: 8px; border: 2px solid #28C7A1; background: #fff; color: #1AAD8A; font-weight: 600; font-family: 'Lato', sans-serif; cursor: pointer; transition: all 0.2s; text-align: left; display: flex; flex-direction: column; justify-content: center;">
                                        <span class="filter-button-title" style="line-height: 1.2; font-size: 15px;">Indicador Capacidad</span>
                                        <span class="filter-button-subtitle" style="font-size: 12px; color: #6b7280; display: block; font-weight: 400; margin-top: 4px; line-height: 1.3;">Ordenes con fecha de salida confirmada.</span>
                                    </button>
                                    <button type="button" class="filter-button" id="btn-kpi-pos-transbordo" data-filter="pos_transbordo" style="width: 100%; min-height: 60px; padding: 12px 16px; font-size: 14px; border-radius: 8px; border: 2px solid #28C7A1; background: #fff; color: #1AAD8A; font-weight: 600; font-family: 'Lato', sans-serif; cursor: pointer; transition: all 0.2s; text-align: left; display: flex; flex-direction: column; justify-content: center;">
                                        <span class="filter-button-title" style="line-height: 1.2; font-size: 15px;">POs en Puerto de Transbordo</span>
                                        <span class="filter-button-subtitle" style="font-size: 12px; color: #6b7280; display: block; font-weight: 400; margin-top: 4px; line-height: 1.3;">Ordenes en puerto intermedio.</span>
                                    </button>
                                    <button type="button" class="filter-button" id="btn-kpi-pos-ata" data-filter="pos_ata" style="width: 100%; min-height: 60px; padding: 12px 16px; font-size: 14px; border-radius: 8px; border: 2px solid #28C7A1; background: #fff; color: #1AAD8A; font-weight: 600; font-family: 'Lato', sans-serif; cursor: pointer; transition: all 0.2s; text-align: left; display: flex; flex-direction: column; justify-content: center;">
                                        <span class="filter-button-title" style="line-height: 1.2; font-size: 15px;">POs con ATA</span>
                                        <span class="filter-button-subtitle" style="font-size: 12px; color: #6b7280; display: block; font-weight: 400; margin-top: 4px; line-height: 1.3;">Ordenes con fecha de llegada confirmada.</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- VISTA: TENDENCIA - TEUs -->
            <div class="view-content" id="tendencia-teus">
                
                <!-- KPI Cards -->
                <div class="kpi-cards">
                    <div class="kpi-card">
                        <div class="kpi-card-title">Total TEUs</div>
                        <div class="kpi-card-value">2,847</div>
                        <div class="kpi-card-subtitle">Contenedores equivalentes</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-card-title">TEUs con Retraso</div>
                        <div class="kpi-card-value">348</div>
                        <div class="kpi-card-subtitle">12.2% del volumen</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-card-title">TEUs con Adelanto</div>
                        <div class="kpi-card-value">198</div>
                        <div class="kpi-card-subtitle">7.0% del volumen</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-card-title">TEUs con ATA</div>
                        <div class="kpi-card-value">1,245</div>
                        <div class="kpi-card-subtitle">Arribos confirmados</div>
                    </div>
                </div>

                <!-- Tabla Principal con Panel de Filtros -->
                <div class="trend-table-section" style="position: relative; width: calc(100% + 5rem); max-width: calc(100% + 5rem); margin-left: -2.5rem; margin-right: -2.5rem; padding-left: 2.5rem; padding-right: 2.5rem; box-sizing: border-box; margin-top: 32px;">
                    <div style="display: flex; gap: 16px; width: 100%; box-sizing: border-box;">
                        <!-- Tabla Principal - 2/3 width -->
                        <div class="table-card" style="display: flex; flex-direction: column; width: 66.67%; box-sizing: border-box; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <h3 class="chart-title" style="text-align: center; margin-bottom: 16px; font-size: 18px; font-weight: 600; color: #374151; font-family: 'Lato', sans-serif;" id="kpiTableTitleTeus">Seleccione un filtro para ver los datos</h3>
                            <div class="table-container" style="overflow-x: auto; width: 100%; max-width: 100%; box-sizing: border-box;">
                                <table class="data-table" id="kpiTrendTableTeus" style="width: 100%; border-collapse: collapse;">
                                    <thead id="kpiTrendTableHeadTeus">
                                        <tr>
                                            <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Seleccione un filtro</th>
                                        </tr>
                                    </thead>
                                    <tbody id="kpiTrendTableBodyTeus">
                                        <tr>
                                            <td style="padding: 12px; text-align: center; color: #6b7280; border: 1px solid #e5e7eb;">Use los filtros del panel lateral para ver los datos</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Panel de Filtros Adicionales - 1/3 width -->
                        <div class="filters-panel" style="width: 33.33%; box-sizing: border-box;">
                            <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                <h3 style="font-size: 18px; font-weight: 600; color: #374151; margin-bottom: 20px; font-family: 'Lato', sans-serif;">Filtros Adicionales</h3>
                                
                                <!-- Additional Filters Buttons -->
                                <div class="additional-filters-buttons" style="display: flex; flex-direction: column; gap: 12px;">
                                    <button type="button" class="filter-button" id="btn-kpi-teus-retraso-cl" data-filter="teus_retraso_cl" style="width: 100%; min-height: 60px; padding: 12px 16px; font-size: 14px; border-radius: 8px; border: 2px solid #28C7A1; background: #fff; color: #1AAD8A; font-weight: 600; font-family: 'Lato', sans-serif; cursor: pointer; transition: all 0.2s; text-align: left; display: flex; flex-direction: column; justify-content: center;">
                                        <span class="filter-button-title" style="line-height: 1.2; font-size: 15px;">TEUs con Retraso CL</span>
                                        <span class="filter-button-subtitle" style="font-size: 12px; color: #6b7280; display: block; font-weight: 400; margin-top: 4px; line-height: 1.3;">Carga lista real posterior a la planificada.</span>
                                    </button>
                                    <button type="button" class="filter-button" id="btn-kpi-teus-adelanto-cl" data-filter="teus_adelanto_cl" style="width: 100%; min-height: 60px; padding: 12px 16px; font-size: 14px; border-radius: 8px; border: 2px solid #28C7A1; background: #fff; color: #1AAD8A; font-weight: 600; font-family: 'Lato', sans-serif; cursor: pointer; transition: all 0.2s; text-align: left; display: flex; flex-direction: column; justify-content: center;">
                                        <span class="filter-button-title" style="line-height: 1.2; font-size: 15px;">TEUs con Adelanto CL</span>
                                        <span class="filter-button-subtitle" style="font-size: 12px; color: #6b7280; display: block; font-weight: 400; margin-top: 4px; line-height: 1.3;">Carga lista real previa a la planificada.</span>
                                    </button>
                                    <button type="button" class="filter-button" id="btn-kpi-teus-capacidad" data-filter="teus_capacidad" style="width: 100%; min-height: 60px; padding: 12px 16px; font-size: 14px; border-radius: 8px; border: 2px solid #28C7A1; background: #fff; color: #1AAD8A; font-weight: 600; font-family: 'Lato', sans-serif; cursor: pointer; transition: all 0.2s; text-align: left; display: flex; flex-direction: column; justify-content: center;">
                                        <span class="filter-button-title" style="line-height: 1.2; font-size: 15px;">Capacidad (Allocation) - TEUs</span>
                                        <span class="filter-button-subtitle" style="font-size: 12px; color: #6b7280; display: block; font-weight: 400; margin-top: 4px; line-height: 1.3;">Ordenes con fecha de salida confirmada.</span>
                                    </button>
                                    <button type="button" class="filter-button" id="btn-kpi-teus-transbordo" data-filter="teus_transbordo" style="width: 100%; min-height: 60px; padding: 12px 16px; font-size: 14px; border-radius: 8px; border: 2px solid #28C7A1; background: #fff; color: #1AAD8A; font-weight: 600; font-family: 'Lato', sans-serif; cursor: pointer; transition: all 0.2s; text-align: left; display: flex; flex-direction: column; justify-content: center;">
                                        <span class="filter-button-title" style="line-height: 1.2; font-size: 15px;">TEUs en Puerto de Transbordo</span>
                                        <span class="filter-button-subtitle" style="font-size: 12px; color: #6b7280; display: block; font-weight: 400; margin-top: 4px; line-height: 1.3;">Ordenes en puerto intermedio.</span>
                                    </button>
                                    <button type="button" class="filter-button" id="btn-kpi-teus-ata" data-filter="teus_ata" style="width: 100%; min-height: 60px; padding: 12px 16px; font-size: 14px; border-radius: 8px; border: 2px solid #28C7A1; background: #fff; color: #1AAD8A; font-weight: 600; font-family: 'Lato', sans-serif; cursor: pointer; transition: all 0.2s; text-align: left; display: flex; flex-direction: column; justify-content: center;">
                                        <span class="filter-button-title" style="line-height: 1.2; font-size: 15px;">TEUs con ATA</span>
                                        <span class="filter-button-subtitle" style="font-size: 12px; color: #6b7280; display: block; font-weight: 400; margin-top: 4px; line-height: 1.3;">Ordenes con fecha de llegada confirmada.</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- VISTA: PO vs TEUs -->
            <div class="view-content" id="po-vs-teus">

                <!-- KPI Cards -->
                <div class="kpi-cards">
                    <div class="kpi-card">
                        <div class="kpi-card-title">Total POs</div>
                        <div class="kpi-card-value" id="povsteus-total-pos">-</div>
                        <div class="kpi-card-subtitle">Órdenes activas</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-card-title">Total TEUs</div>
                        <div class="kpi-card-value" id="povsteus-total-teus">-</div>
                        <div class="kpi-card-subtitle">Contenedores equivalentes</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-card-title">Variación Semana</div>
                        <div class="kpi-card-value" id="povsteus-week-variation">-</div>
                        <div class="kpi-card-subtitle">vs semana anterior</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-card-title">Variación Mes</div>
                        <div class="kpi-card-value" id="povsteus-month-variation">-</div>
                        <div class="kpi-card-subtitle">vs mes anterior</div>
                    </div>
                </div>

                <!-- Tabla 1: PO vs TEUs por Etapa -->
                <div class="table-section">
                    <div class="table-header">
                        <div>
                            <div class="table-title">PO / TEUs por Etapa</div>
                            <div class="table-description">Comparación de cantidad de órdenes y volumen en cada etapa logística</div>
                        </div>
                    </div>
                    <table class="data-table">
                        <thead id="povsteus-stage-head"><tr><th>Cargando...</th></tr></thead>
                        <tbody id="povsteus-stage-body"><tr><td style="text-align:center;padding:20px;color:#6b7280;">Cargando datos...</td></tr></tbody>
                    </table>
                </div>

                <!-- Tabla 2: PO vs TEUs por Período -->
                <div class="table-section">
                    <div class="table-header">
                        <div>
                            <div class="table-title">PO / TEUs por Período</div>
                            <div class="table-description">Comparación de volumen entre períodos de tiempo para evaluación de tendencias</div>
                        </div>
                    </div>
                    <table class="data-table">
                        <thead id="povsteus-period-head"><tr><th>Cargando...</th></tr></thead>
                        <tbody id="povsteus-period-body"><tr><td style="text-align:center;padding:20px;color:#6b7280;">Cargando datos...</td></tr></tbody>
                    </table>
                </div>

                <!-- Tabla 3: PO vs TEUs por Proveedor -->
                <div class="table-section">
                    <div class="table-header">
                        <div>
                            <div class="table-title">PO / TEUs por Proveedor de Mercancía</div>
                            <div class="table-description">Análisis de contribución de cada proveedor al volumen total de operaciones</div>
                        </div>
                    </div>
                    <table class="data-table">
                        <thead id="povsteus-vendor-head"><tr><th>Cargando...</th></tr></thead>
                        <tbody id="povsteus-vendor-body"><tr><td style="text-align:center;padding:20px;color:#6b7280;">Cargando datos...</td></tr></tbody>
                    </table>
                </div>

                <!-- Tabla 4: PO / TEUs por Naviera -->
                <div class="table-section">
                    <div class="table-header">
                        <div>
                            <div class="table-title">PO / TEUs por Naviera</div>
                            <div class="table-description">Participación y relevancia de cada operador marítimo en el volumen gestionado</div>
                        </div>
                    </div>
                    <table class="data-table">
                        <thead id="povsteus-line-head"><tr><th>Cargando...</th></tr></thead>
                        <tbody id="povsteus-line-body"><tr><td style="text-align:center;padding:20px;color:#6b7280;">Cargando datos...</td></tr></tbody>
                    </table>
                </div>

            </div>

            <!-- VISTA: COMPARATIVO -->
            <div class="view-content" id="comparativo">
                
                <!-- KPI Cards -->
                <div class="kpi-cards">
                    <div class="kpi-card">
                        <div class="kpi-card-title">Variación ATD</div>
                        <div class="kpi-card-value" id="kpi-comp-var-atd">-</div>
                        <div class="kpi-card-subtitle">vs período anterior</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-card-title">Variación ATA</div>
                        <div class="kpi-card-value" id="kpi-comp-var-ata">-</div>
                        <div class="kpi-card-subtitle">vs período anterior</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-card-title">Variación Atrasos</div>
                        <div class="kpi-card-value" id="kpi-comp-var-atrasos">-</div>
                        <div class="kpi-card-subtitle">Mejora en puntualidad</div>
                    </div>
                </div>

                <!-- Filtros de Períodos de Comparación - Sobre la tabla -->
                <div class="comparison-period-filters" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 24px;">
                    <div style="display: flex; align-items: center; gap: 32px; flex-wrap: wrap;">
                        <!-- Título a la izquierda -->
                        <h4 style="font-size: 16px; font-weight: 600; color: #374151; margin: 0; font-family: 'Lato', sans-serif; white-space: nowrap;">Períodos de Comparación</h4>
                        
                        <!-- Períodos juntos -->
                        <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
                            <!-- Período A -->
                            <div style="display: flex; align-items: flex-end; gap: 8px;">
                                <div style="display: flex; flex-direction: column; gap: 4px;" x-data="datePicker('')">
                                    <label style="font-size: 14px; font-weight: 500; color: #1AAD8A; white-space: nowrap;">Inicio</label>
                                    <input x-ref="picker" type="text" id="comp-period-a-from" class="date-input filter-input" style="width: 150px !important; height: 40px !important; padding: 8px 14px; border: 2px solid #28C7A1; border-radius: 10px; font-size: 16px; color: #222; background: white; font-family: 'Lato', sans-serif; box-sizing: border-box; margin: 0;">
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 4px; position: relative;" x-data="datePicker('')">
                                    <div style="display: flex; align-items: center; width: 150px; position: relative;">
                                        <label style="font-size: 14px; font-weight: 500; color: #1AAD8A; white-space: nowrap;">Fin</label>
                                        <span style="font-size: 14px; font-weight: 700; color: #1AAD8A; white-space: nowrap; position: absolute; right: 0;">Período A</span>
                                    </div>
                                    <input x-ref="picker" type="text" id="comp-period-a-to" class="date-input filter-input" style="width: 150px !important; height: 40px !important; padding: 8px 14px; border: 2px solid #28C7A1; border-radius: 10px; font-size: 16px; color: #222; background: white; font-family: 'Lato', sans-serif; box-sizing: border-box; margin: 0;">
                                </div>
                            </div>
                            
                            <!-- Período B -->
                            <div style="display: flex; align-items: flex-end; gap: 8px;">
                                <div style="display: flex; flex-direction: column; gap: 4px;" x-data="datePicker('')">
                                    <label style="font-size: 14px; font-weight: 500; color: #1AAD8A; white-space: nowrap;">Inicio</label>
                                    <input x-ref="picker" type="text" id="comp-period-b-from" class="date-input filter-input" style="width: 150px !important; height: 40px !important; padding: 8px 14px; border: 2px solid #28C7A1; border-radius: 10px; font-size: 16px; color: #222; background: white; font-family: 'Lato', sans-serif; box-sizing: border-box; margin: 0;">
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 4px; position: relative;" x-data="datePicker('')">
                                    <div style="display: flex; align-items: center; width: 150px; position: relative;">
                                        <label style="font-size: 14px; font-weight: 500; color: #1AAD8A; white-space: nowrap;">Fin</label>
                                        <span style="font-size: 14px; font-weight: 700; color: #1AAD8A; white-space: nowrap; position: absolute; right: 0;">Período B</span>
                                    </div>
                                    <input x-ref="picker" type="text" id="comp-period-b-to" class="date-input filter-input" style="width: 150px !important; height: 40px !important; padding: 8px 14px; border: 2px solid #28C7A1; border-radius: 10px; font-size: 16px; color: #222; background: white; font-family: 'Lato', sans-serif; box-sizing: border-box; margin: 0;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla Principal con Panel de Filtros -->
                <div class="trend-table-section" style="position: relative; width: calc(100% + 5rem); max-width: calc(100% + 5rem); margin-left: -2.5rem; margin-right: -2.5rem; padding-left: 2.5rem; padding-right: 2.5rem; box-sizing: border-box; margin-top: 24px;">
                    <div style="display: flex; gap: 16px; width: 100%; box-sizing: border-box;">
                        <!-- Tabla Principal - 2/3 width -->
                        <div class="table-card" style="display: flex; flex-direction: column; width: 66.67%; box-sizing: border-box; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <h3 class="chart-title" style="text-align: center; margin-bottom: 16px; font-size: 18px; font-weight: 600; color: #374151; font-family: 'Lato', sans-serif;" id="kpiCompTableTitle">Seleccione un indicador para ver la comparación</h3>
                            <div class="table-container" style="overflow-x: auto; width: 100%; max-width: 100%; box-sizing: border-box;">
                                <table class="data-table" id="kpiCompTable" style="width: 100%; border-collapse: collapse;">
                                    <thead id="kpiCompTableHead">
                                        <tr>
                                            <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151;">Seleccione un indicador</th>
                                        </tr>
                                    </thead>
                                    <tbody id="kpiCompTableBody">
                                        <tr>
                                            <td style="padding: 12px; text-align: center; color: #6b7280; border: 1px solid #e5e7eb;">Seleccione los períodos y un indicador del panel lateral</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Panel de Indicadores - 1/3 width -->
                        <div class="filters-panel" id="comparativo-filters-panel" style="width: 33.33%; box-sizing: border-box;">
                            <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                <h3 style="font-size: 18px; font-weight: 600; color: #374151; margin-bottom: 20px; font-family: 'Lato', sans-serif;">Indicadores</h3>
                                
                                <!-- Comparison Filters Buttons -->
                                <div class="additional-filters-buttons" style="display: flex; flex-direction: column; gap: 12px;">
                                    <button type="button" class="filter-button" id="btn-comp-retraso-cl" data-filter="comp_retraso_cl" style="width: 100%; min-height: 60px; padding: 12px 16px; font-size: 14px; border-radius: 8px; border: 2px solid #28C7A1; background: #fff; color: #1AAD8A; font-weight: 600; font-family: 'Lato', sans-serif; cursor: pointer; transition: all 0.2s; text-align: left; display: flex; flex-direction: column; justify-content: center;">
                                        <span class="filter-button-title" style="line-height: 1.2; font-size: 15px;">PO Retraso CL</span>
                                        <span class="filter-button-subtitle" style="font-size: 12px; color: #6b7280; display: block; font-weight: 400; margin-top: 4px; line-height: 1.3;">Comparación de atrasos entre períodos</span>
                                    </button>
                                    <button type="button" class="filter-button" id="btn-comp-adelanto-cl" data-filter="comp_adelanto_cl" style="width: 100%; min-height: 60px; padding: 12px 16px; font-size: 14px; border-radius: 8px; border: 2px solid #28C7A1; background: #fff; color: #1AAD8A; font-weight: 600; font-family: 'Lato', sans-serif; cursor: pointer; transition: all 0.2s; text-align: left; display: flex; flex-direction: column; justify-content: center;">
                                        <span class="filter-button-title" style="line-height: 1.2; font-size: 15px;">PO Adelanto CL</span>
                                        <span class="filter-button-subtitle" style="font-size: 12px; color: #6b7280; display: block; font-weight: 400; margin-top: 4px; line-height: 1.3;">Comparación de adelantos entre períodos</span>
                                    </button>
                                    <button type="button" class="filter-button" id="btn-comp-capacidad" data-filter="comp_capacidad" style="width: 100%; min-height: 60px; padding: 12px 16px; font-size: 14px; border-radius: 8px; border: 2px solid #28C7A1; background: #fff; color: #1AAD8A; font-weight: 600; font-family: 'Lato', sans-serif; cursor: pointer; transition: all 0.2s; text-align: left; display: flex; flex-direction: column; justify-content: center;">
                                        <span class="filter-button-title" style="line-height: 1.2; font-size: 15px;">Indicador Capacidad</span>
                                        <span class="filter-button-subtitle" style="font-size: 12px; color: #6b7280; display: block; font-weight: 400; margin-top: 4px; line-height: 1.3;">Volumen despachado por período</span>
                                    </button>
                                    <button type="button" class="filter-button" id="btn-comp-atd" data-filter="comp_atd" style="width: 100%; min-height: 60px; padding: 12px 16px; font-size: 14px; border-radius: 8px; border: 2px solid #28C7A1; background: #fff; color: #1AAD8A; font-weight: 600; font-family: 'Lato', sans-serif; cursor: pointer; transition: all 0.2s; text-align: left; display: flex; flex-direction: column; justify-content: center;">
                                        <span class="filter-button-title" style="line-height: 1.2; font-size: 15px;">PO con ATD</span>
                                        <span class="filter-button-subtitle" style="font-size: 12px; color: #6b7280; display: block; font-weight: 400; margin-top: 4px; line-height: 1.3;">Salidas ejecutadas por período</span>
                                    </button>
                                    <button type="button" class="filter-button" id="btn-comp-ata" data-filter="comp_ata" style="width: 100%; min-height: 60px; padding: 12px 16px; font-size: 14px; border-radius: 8px; border: 2px solid #28C7A1; background: #fff; color: #1AAD8A; font-weight: 600; font-family: 'Lato', sans-serif; cursor: pointer; transition: all 0.2s; text-align: left; display: flex; flex-direction: column; justify-content: center;">
                                        <span class="filter-button-title" style="line-height: 1.2; font-size: 15px;">PO con ATA</span>
                                        <span class="filter-button-subtitle" style="font-size: 12px; color: #6b7280; display: block; font-weight: 400; margin-top: 4px; line-height: 1.3;">Arribos confirmados por período</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- VISTA: PROYECCIÓN -->
            <div class="view-content" id="proyeccion">

                <!-- KPI Cards -->
                <div class="kpi-cards">
                    <div class="kpi-card">
                        <div class="kpi-card-title">Llegadas Proyectadas</div>
                        <div class="kpi-card-value" id="proy-total-pos">-</div>
                        <div class="kpi-card-subtitle">Próximas 12 semanas</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-card-title">En Producción</div>
                        <div class="kpi-card-value" id="proy-produccion-pos">-</div>
                        <div class="kpi-card-subtitle">CL Teórica estimada</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-card-title">En Booking</div>
                        <div class="kpi-card-value" id="proy-booking-pos">-</div>
                        <div class="kpi-card-subtitle">ETD proyectado</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-card-title">En Tránsito</div>
                        <div class="kpi-card-value" id="proy-transito-pos">-</div>
                        <div class="kpi-card-subtitle">ETA confirmado</div>
                    </div>
                </div>

                <!-- Tabla: Llegadas Futuras por Semana -->
                <div class="table-section">
                    <div class="table-header">
                        <div>
                            <div class="table-title">Proyección de Llegadas Futuras por Semana</div>
                            <div class="table-description">
                                Distribución semanal de órdenes por etapa del proceso logístico - próximas 12 semanas
                            </div>
                        </div>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="data-table" id="proy-table">
                            <thead id="proy-table-head">
                                <tr>
                                    <th>Cargando...</th>
                                </tr>
                            </thead>
                            <tbody id="proy-table-body">
                                <tr>
                                    <td style="text-align:center; padding: 20px; color: #6b7280;">Cargando datos...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div class="modal-content" style="background: white; padding: 30px; border-radius: 12px; text-align: center; max-width: 400px;">
            <div class="modal-body">
                <div class="modal-icon success" style="width: 80px; height: 80px; background: #1AAD8A; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 6L9 17L4 12" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h3 class="modal-title success" style="color: #1AAD8A; font-size: 20px; margin-bottom: 10px;">Archivo descargado exitosamente</h3>
                <p class="modal-text" style="color: #666; margin-bottom: 20px;">El archivo Excel se ha descargado correctamente</p>
            </div>
            <button id="closeSuccessBtn" class="modal-btn" style="background: #1AAD8A; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-size: 16px; cursor: pointer;">Aceptar</button>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div class="modal-content" style="background: white; padding: 30px; border-radius: 12px; text-align: center; max-width: 400px;">
            <div class="modal-body">
                <div class="modal-icon error" style="width: 80px; height: 80px; background: #FF3459; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="white" stroke-width="2"/>
                        <path d="M12 8V12M12 16H12.01" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h3 class="modal-title error" style="color: #FF3459; font-size: 20px; margin-bottom: 10px;">¡Ha ocurrido un error!</h3>
                <p class="modal-text" style="color: #666; margin-bottom: 20px;">No se pudo descargar correctamente el reporte</p>
            </div>
            <button id="closeErrorBtn" class="modal-btn" style="background: #FF3459; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-size: 16px; cursor: pointer;">Intentar de nuevo</button>
        </div>
    </div>

    @push('scripts')
        <script>
            // CSRF Token para las peticiones AJAX
            window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        </script>
        <script src="{{ asset('js/dashboard-kpi.js') }}"></script>
        <script src="{{ asset('js/dashboard-dynamic.js') }}"></script>
        <script>
            // Tab switching
            document.querySelectorAll('.tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                    // Add active to clicked tab
                    this.classList.add('active');
                    
                    // Hide all views
                    document.querySelectorAll('.view-content').forEach(v => v.classList.remove('active'));
                    
                    // Hide all subtab containers
                    document.querySelectorAll('.subtabs-container').forEach(s => s.classList.remove('active'));
                    
                    // Show selected view
                    const viewId = this.dataset.view;
                    
                    if (viewId === 'tendencia') {
                        document.getElementById('subtabs-tendencia').classList.add('active');
                        document.getElementById('tendencia-po').classList.add('active');
                    } else {
                        document.getElementById(viewId).classList.add('active');
                        
                        // Si se cambia a la vista comparativa, establecer fechas por defecto
                        if (viewId === 'comparativo' && window.dashboardKPIManager) {
                            // Esperar a que Flatpickr se inicialice
                            setTimeout(() => {
                                window.dashboardKPIManager.setDefaultComparisonPeriods();
                            }, 300);
                        }

                        // Cargar proyección al activar esa vista
                        if (viewId === 'proyeccion' && window.dashboardKPIManager) {
                            window.dashboardKPIManager.currentFilters = window.dashboardKPIManager.collectFilters();
                            window.dashboardKPIManager.loadProyeccion();
                        }

                        // Cargar PO vs TEUs al activar esa vista
                        if (viewId === 'po-vs-teus' && window.dashboardKPIManager) {
                            window.dashboardKPIManager.currentFilters = window.dashboardKPIManager.collectFilters();
                            window.dashboardKPIManager.loadPoVsTeus();
                        }
                    }
                });
            });

            // Subtab switching
            document.querySelectorAll('.subtab').forEach(subtab => {
                subtab.addEventListener('click', function() {
                    // Remove active class from all subtabs
                    document.querySelectorAll('.subtab').forEach(s => s.classList.remove('active'));
                    // Add active to clicked subtab
                    this.classList.add('active');
                    
                    // Hide all tendencia views
                    document.getElementById('tendencia-po').classList.remove('active');
                    document.getElementById('tendencia-teus').classList.remove('active');
                    
                    // Show selected subtab view
                    const subtabId = this.dataset.subtab;
                    document.getElementById(subtabId).classList.add('active');
                });
            });
        </script>
    @endpush
</x-app-layout>

