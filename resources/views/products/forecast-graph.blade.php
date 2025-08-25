@push('styles')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles-forecast.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('scripts')
    <script>
        // Pass data from PHP to JavaScript
        window.forecastData = @json($forecastData ?? []);
        window.forecastData.filterOptions = @json($filterOptions ?? []);
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    </script>
    <script src="{{ asset('js/script-forecast.js') }}"></script>
@endpush

<x-app-layout>
    <div class="content">
        @if(isset($error))
            <div class="px-4 py-3 mb-4 text-red-700 bg-red-100 border border-red-400 rounded">
                {{ $error }}
            </div>
        @endif

        <!-- Clear Filters Button -->
        <div class="clear-filters-container">
            <button id="clearFiltersBtn" class="clear-filters-btn" style="display: none;">
                borrar filtros
            </button>
        </div>

        <!-- Filter Controls -->
        <div class="filters-section" style="display: flex; align-items: flex-end; gap: 16px; flex-wrap: nowrap; font-family: 'Lato', sans-serif;">
            <div class="filter-group" data-filter="date-from" style="width: 180px;">
                <label class="filter-label" style="color: #565AFF; font-size: 14px;">Fecha inicio</label>
                <input type="date" id="startDate" name="date_from" value="{{ request('date_from') }}" class="date-input" style="height: 40px; width: 180px; padding: 8px 14px; border: 2px solid #7288FF; border-radius: 10px; font-size: 16px; color: #222; font-family: 'Lato', sans-serif;">
            </div>
            <div class="filter-group" data-filter="date-to" style="width: 180px;">
                <label class="filter-label" style="color: #565AFF; font-size: 14px;">Fecha fin</label>
                <input type="date" id="endDate" name="date_to" value="{{ request('date_to') }}" class="date-input" style="height: 40px; width: 180px; padding: 8px 14px; border: 2px solid #7288FF; border-radius: 10px; font-size: 16px; color: #222; font-family: 'Lato', sans-serif;">
            </div>
            <div class="filter-group" data-filter="vendor" style="width: 180px;">
                <label class="filter-label" style="color: #565AFF; font-size: 14px;">Vendor</label>
                <div class="multi-select" data-multiselect data-placeholder="Seleccionar vendors" style="height: 40px; width: 180px; border: 2px solid #7288FF; border-radius: 10px; padding: 0; background: #fff;">
                    <button type="button" class="multi-select-trigger" style="height: 36px; color: #222; font-size: 16px; font-family: 'Lato', sans-serif; padding: 8px 14px; background: transparent; border: none; width: 100%; text-align: left; display: flex; align-items: center;">
                        <span class="multi-select-value" style="color: #AFAFAF;">Seleccionar vendors</span>
                        <i class="fas fa-chevron-down multi-select-icon"></i>
                    </button>
                    <div class="multi-select-content" style="border-radius: 10px; border: 2px solid #7288FF; margin-top: 0.25rem; box-shadow: 0 2px 8px rgba(86,90,255,0.08);">
                        <div class="multi-select-search">
                            <input type="text" placeholder="Buscar..." class="multi-select-search-input" style="color: #222; font-size: 16px; font-family: 'Lato', sans-serif;">
                        </div>
                        <div class="multi-select-options"></div>
                        <div class="multi-select-clear">Limpiar selección</div>
                    </div>
                </div>
            </div>
            <div class="filter-group" data-filter="product" style="width: 180px;">
                <label class="filter-label" style="color: #565AFF; font-size: 14px;">Producto</label>
                <div class="multi-select" data-multiselect data-placeholder="Seleccionar productos" style="height: 40px; width: 180px; border: 2px solid #7288FF; border-radius: 10px; padding: 0; background: #fff;">
                    <button type="button" class="multi-select-trigger" style="height: 36px; color: #222; font-size: 16px; font-family: 'Lato', sans-serif; padding: 8px 14px; background: transparent; border: none; width: 100%; text-align: left; display: flex; align-items: center;">
                        <span class="multi-select-value" style="color: #AFAFAF;">Seleccionar productos</span>
                        <i class="fas fa-chevron-down multi-select-icon"></i>
                    </button>
                    <div class="multi-select-content" style="border-radius: 10px; border: 2px solid #7288FF; margin-top: 0.25rem; box-shadow: 0 2px 8px rgba(86,90,255,0.08);">
                        <div class="multi-select-search">
                            <input type="text" placeholder="Buscar..." class="multi-select-search-input" style="color: #222; font-size: 16px; font-family: 'Lato', sans-serif;">
                        </div>
                        <div class="multi-select-options"></div>
                        <div class="multi-select-clear">Limpiar selección</div>
                    </div>
                </div>
            </div>
            <div class="filter-group" data-filter="material" style="width: 180px;">
                <label class="filter-label" style="color: #565AFF; font-size: 14px;">Material</label>
                <div class="multi-select" data-multiselect data-placeholder="Seleccionar materiales" style="height: 40px; width: 180px; border: 2px solid #7288FF; border-radius: 10px; padding: 0; background: #fff;">
                    <button type="button" class="multi-select-trigger" style="height: 36px; color: #222; font-size: 16px; font-family: 'Lato', sans-serif; padding: 8px 14px; background: transparent; border: none; width: 100%; text-align: left; display: flex; align-items: center;">
                        <span class="multi-select-value" style="color: #AFAFAF;">Seleccionar materiales</span>
                        <i class="fas fa-chevron-down multi-select-icon"></i>
                    </button>
                    <div class="multi-select-content" style="border-radius: 10px; border: 2px solid #7288FF; margin-top: 0.25rem; box-shadow: 0 2px 8px rgba(86,90,255,0.08);">
                        <div class="multi-select-search">
                            <input type="text" placeholder="Buscar..." class="multi-select-search-input" style="color: #222; font-size: 16px; font-family: 'Lato', sans-serif;">
                        </div>
                        <div class="multi-select-options"></div>
                        <div class="multi-select-clear">Limpiar selección</div>
                    </div>
                </div>
            </div>
            <div class="action-buttons" style="display: flex; gap: 16px; align-items: flex-end; margin-left: auto;">
                <button class="btn-primary" style="height: 40px; min-width: 100px; padding: 0 18px; font-size: 16px; border-radius: 8px; border: none; background: #565AFF; color: #F7F7F7; font-weight: 700; font-family: 'Lato', sans-serif; display: flex; align-items: center; justify-content: center;">Aceptar</button>
                <button class="btn-secondary" id="export-btn" style="height: 40px; min-width: 100px; padding: 0 18px; font-size: 16px; border-radius: 8px; border: 2px solid #565AFF; background: #fff; color: #565AFF; font-weight: 700; font-family: 'Lato', sans-serif; display: flex; align-items: center; gap: 8px; justify-content: center;">
                    <i class="fas fa-download"></i>
                    Descargar
                </button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-title">Total PO's</div>
                <div class="kpi-value" id="total-pos">{{ $forecastData['metrics']['total_pos'] ?? 0 }}</div>
                <!-- <div class="kpi-description">Description Bottom</div> -->
            </div>
            <div class="kpi-card">
                <div class="kpi-title">% PO's on time</div>
                <div class="kpi-value" id="on-time-percentage">{{ $forecastData['metrics']['on_time_percentage'] ?? 0 }}%</div>
                <!-- <div class="kpi-description">Description Bottom</div> -->
            </div>
            <div class="kpi-card">
                <div class="kpi-title">% PO's atrasadas</div>
                <div class="kpi-value" id="delayed-percentage">{{ $forecastData['metrics']['delayed_percentage'] ?? 0 }}%</div>
                <!-- <div class="kpi-description">Description Bottom</div> -->
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Monto total USD</div>
                <div class="kpi-value" id="total-amount">${{ number_format($forecastData['metrics']['total_amount'] ?? 0, 0) }} </div>
                <!-- <div class="kpi-description">Description Bottom</div> -->
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="main-grid">
            <!-- Left Column -->
            <div class="left-column">
                <!-- Forecast Table -->
                <div class="card">
                    <h3 class="card-title">Forecast v/s real - Mensual</h3>
                    <div class="table-container" style="overflow-x: auto; overflow-y: auto; max-height: 285px;">
                        <table class="forecast-table">
                            <thead>
                                <tr>
                                    <th>Material</th>
                                    <th class="text-right">Forecast</th>
                                    <th class="text-right">Cantidad kgs</th>
                                    <th class="text-right">Desviación KG</th>
                                </tr>
                            </thead>
                            <tbody id="forecastTableBody">
                                <!-- Populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bar Chart -->
                <div class="card">
                    <h3 class="card-title">Cantidad kgs por mes</h3>
                    <div class="chart-container">
                        <canvas id="barChart" width="400" height="255"></canvas>
                    </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #565AFF;"></div>
                            <span>Cantidad kgs</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="right-column">
                <!-- Treemap -->
                <div class="card treemap-card">
                    <div class="card-header">
                        <h3 class="card-title !mb-0">Desviación por Material</h3>
                        <select class="treemap-select">
                            <option value="all">Todo</option>
                        </select>
                    </div>
                    <div class="treemap-container" id="treemapContainer">
                        <!-- Populated by JavaScript -->
                    </div>
                </div>

                <!-- Pie Chart -->
                <div class="card">
                    <h3 class="card-title">Desviación por vendor</h3>
                    <div class="pie-chart-container">
                        <div class="pie-chart-wrapper">
                            <canvas id="pieChart" width="240" height="240"></canvas>
                        </div>
                        <div class="vendor-legend" id="vendorLegend">
                            <!-- Populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
