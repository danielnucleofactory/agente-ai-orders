<x-app-layout>
    @push('styles')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/dashboard-styles.css') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
    @endpush

    <div class="dashboard-container !font-inter">
        @if(isset($error))
            <div class="px-4 py-3 mb-4 text-red-700 bg-red-100 rounded border border-red-400">
                {{ $error }}
            </div>
        @endif

        <!-- Filter Controls -->
        <form id="dashboard-filters" class="justify-between filters-section">
            <div class="flex gap-4">
                <div class="filter-group">
                    <label class="filter-label">Fecha</label>
                    <div class="date-range">
                        <div class="date-input-wrapper">
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                   class="date-input !border-2 !border-[#7288FF] !rounded-xl">
                        </div>
                        <span class="date-separator">→</span>
                        <div class="date-input-wrapper">
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                   class="date-input !border-2 !border-[#7288FF] !rounded-xl">
                        </div>

                    </div>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Producto</label>
                    <select name="product_id" class="filter-select !border-2 !border-[#7288FF] !rounded-xl">
                        <option value="">Seleccionar</option>
                        @foreach($filterOptions['products'] as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} ({{ $product->material_id }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Material</label>
                    <select name="material_type" class="filter-select !border-2 !border-[#7288FF] !rounded-xl">
                        <option value="">Seleccionar Material</option>
                        @foreach($filterOptions['materials'] as $material)
                            <option value="{{ $material }}" {{ request('material_type') == $material ? 'selected' : '' }}>
                                {{ ucfirst($material) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Hub</label>
                    <select name="hub_id" class="filter-select !border-2 !border-[#7288FF] !rounded-xl">
                        <option value="">Seleccionar Hub</option>
                        @foreach($filterOptions['hubs'] as $hub)
                            <option value="{{ $hub->id }}" {{ request('hub_id') == $hub->id ? 'selected' : '' }}>
                                {{ $hub->name }} ({{ $hub->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="action-buttons">
                <button type="submit" class="btn-primary !border-2 !border-light-blue !py-[0.594rem]">
                    Aceptar
                </button>
                <button type="button" id="export-btn" class="btn-secondary !border-2 !border-light-blue !py-[0.594rem]">
                    <i class="fas fa-download"></i>
                    Descargar
                </button>
            </div>
        </form>

        <!-- Top Metrics Cards -->
        <div class="metrics-grid">
            <div class="metric-card">
                <p class="metric-label">Total PO's</p>
                <p class="metric-value" id="total-pos">{{ $dashboardData['metrics']['total_pos'] }}</p>
            </div>
            <div class="metric-card">
                <p class="metric-label">% PO's on time</p>
                <p class="metric-value" id="on-time-percentage">{{ $dashboardData['metrics']['on_time_percentage'] }}%</p>
            </div>
            <div class="metric-card">
                <p class="metric-label">% PO's atrasadas</p>
                <p class="metric-value" id="delayed-percentage">{{ $dashboardData['metrics']['delayed_percentage'] }}%</p>
            </div>
            <div class="metric-card">
                <p class="metric-label">Material</p>
                <p class="metric-value" id="material-count">{{ $dashboardData['metrics']['material_count'] }}</p>
            </div>
        </div>

        <!-- Pie Charts Row -->
        <div class="charts-row">
            <div class="chart-card">
                <h3 class="chart-title">Entregas por hub</h3>
                <div class="chart-content">
                    <div class="chart-container">
                        <canvas id="hubChart" width="120" height="120"></canvas>
                    </div>
                    <div class="chart-legend" id="hubLegend"></div>
                </div>
            </div>

            <div class="chart-card">
                <h3 class="chart-title">Estado entrega</h3>
                <div class="chart-content">
                    <div class="chart-container">
                        <canvas id="deliveryChart" width="120" height="120"></canvas>
                    </div>
                    <div class="chart-legend" id="deliveryLegend"></div>
                </div>
            </div>

            <div class="chart-card">
                <h3 class="chart-title">Tipo de transporte</h3>
                <div class="chart-content">
                    <div class="chart-container">
                        <canvas id="transportChart" width="120" height="120"></canvas>
                    </div>
                    <div class="chart-legend" id="transportLegend"></div>
                </div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="bottom-section">
            <!-- Detail Table -->
            <div class="table-card">
                <h3 class="chart-title">Detalle</h3>
                <div class="table-container">
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>Total POs</th>
                                <th>Fecha pedido</th>
                                <th>Fecha estimada</th>
                                <th class="text-right">Total kgs</th>
                            </tr>
                        </thead>
                        <tbody id="detailTableBody">
                            @foreach($dashboardData['detail_table'] as $row)
                                <tr>
                                    <td>{{ $row['po_number'] }}</td>
                                    <td>{{ $row['fecha_salida'] }}</td>
                                    <td>{{ $row['fecha_estimada'] }}</td>
                                    <td class="text-right">{{ $row['cantidad_kg'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Charts Column -->
            <div class="right-charts">
                <div class="chart-card">
                    <h3 class="chart-title">Motivo de atraso</h3>
                    <div class="chart-content-horizontal">
                        <div class="chart-container-small">
                            <canvas id="delayChart" width="120" height="120"></canvas>
                        </div>
                        <div class="chart-legend-small" id="delayLegend"></div>
                    </div>
                </div>

                <div class="chart-card">
                    <h3 class="chart-title">PO's por etapa</h3>
                    <div class="chart-content-horizontal">
                        <div class="chart-container-small">
                            <canvas id="stageChart" width="120" height="120"></canvas>
                        </div>
                        <div class="chart-legend-small" id="stageLegend"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading indicator -->
        <div id="loading-indicator" class="flex hidden fixed inset-0 justify-center items-center bg-gray-600 bg-opacity-50">
            <div class="p-4 bg-white rounded-lg">
                <div class="flex items-center">
                    <svg class="mr-3 -ml-1 w-5 h-5 text-blue-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Cargando datos...
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <div class="modal-body">
                <div class="modal-icon success">
                    <svg width="87" height="87" viewBox="0 0 87 87" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M72.5 22V39.9C72.5 42.3268 72.5 43.5403 72.972 44.4672C73.387 45.2826 74.05 45.9455 74.866 46.361C75.793 46.8333 77.006 46.8333 79.433 46.8333H97.332M58 81.668L66.667 90.334L86.167 70.834M72.5 21.001H49.966C42.686 21.001 39.045 21.001 36.264 22.4179C33.818 23.6642 31.83 25.653 30.583 28.0991C29.166 30.8799 29.166 34.5203 29.166 41.801V86.868C29.166 94.148 29.166 97.789 30.583 100.57C31.83 103.016 33.818 105.004 36.264 106.251C39.045 107.668 42.686 107.668 49.966 107.668H77.7C84.98 107.668 88.621 107.668 91.401 106.251C93.848 105.004 95.836 103.016 97.083 100.57C98.5 97.789 98.5 94.148 98.5 86.868V47.001L72.5 21.001Z" stroke="white" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h3 class="modal-title success">Descarga iniciada correctamente</h3>
                <p class="modal-text">El archivo se descargará en breve</p>
            </div>
            <button id="closeSuccessBtn" class="modal-btn">Aceptar</button>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <div class="modal-body">
                <div class="modal-icon error">
                    <svg width="87" height="87" viewBox="0 0 87 87" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="43.5" cy="43.5" r="36.25" stroke="white" stroke-width="6"/>
                        <path d="M43.5 29V43.5M43.5 58H43.5435" stroke="white" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h3 class="modal-title error">¡Ha ocurrido un error!</h3>
                <p class="modal-text">No se pudo procesar la solicitud correctamente</p>
            </div>
            <button id="closeErrorBtn" class="modal-btn">Intentar de nuevo</button>
        </div>
    </div>

    @push('scripts')
        <script>
            // Pass data from PHP to JavaScript
            window.dashboardData = @json($dashboardData);
            window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        </script>
        <script src="{{ asset('js/dashboard-dynamic.js') }}"></script>
    @endpush
</x-app-layout>
