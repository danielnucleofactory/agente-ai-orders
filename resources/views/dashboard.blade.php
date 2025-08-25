<x-app-layout>
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="css/styles.css">
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <style>
            .multi-select {
                border: 2px solid #7288FF !important;
                border-radius: 0.75rem !important;
                padding: 0.25rem 0.75rem !important;
                background: #fff;
                min-height: 42px;
                box-sizing: border-box;
            }
            .multi-select-trigger {
                min-height: 38px;
                font-size: 1rem;
                color: #565AFF;
                background: transparent;
                border: none;
                outline: none;
                width: 100%;
                text-align: left;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0;
            }
            .multi-select-content {
                border-radius: 0.75rem;
                border: 2px solid #7288FF;
                margin-top: 0.25rem;
                box-shadow: 0 2px 8px rgba(86,90,255,0.08);
                max-height: 70vh;
                overflow-y: auto;
                z-index: 9999;
            }
            
            /* Mejorar scrollbar para multi-select */
            .multi-select-content::-webkit-scrollbar {
                width: 8px;
            }
            
            .multi-select-content::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 4px;
            }
            
            .multi-select-content::-webkit-scrollbar-thumb {
                background: #565AFF;
                border-radius: 4px;
            }
            
            .multi-select-content::-webkit-scrollbar-thumb:hover {
                background: #434ad1;
            }
            
            /* Mejor separación visual para las opciones */
            .multi-select-option {
                border-bottom: 1px solid #f0f0f0;
                padding: 4px 8px;
                display: flex;
                align-items: center;
                cursor: pointer;
                font-size: 13px;
                line-height: 1.2;
            }
            
            .multi-select-option:last-child {
                border-bottom: none;
            }
            
            .multi-select-option:hover {
                background-color: #f8f9ff;
            }
            

            .multi-select-search-input {
                border-radius: 0.5rem;
                border: 1px solid #7288FF;
                padding: 0.125rem 0.375rem;
                margin-bottom: 0.125rem;
                width: 100%;
                font-size: 12px;
            }
            .multi-select-value {
                color: #565AFF;
                font-size: 1rem;
                font-weight: 500;
            }
        </style>
    @endpush
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
        <input type="date" id="startDate" class="date-input" style="height: 40px; width: 180px; padding: 8px 14px; border: 2px solid #7288FF; border-radius: 10px; font-size: 16px; color: #222; font-family: 'Lato', sans-serif;">
      </div>
      <div class="filter-group" data-filter="date-to" style="width: 180px;">
        <label class="filter-label" style="color: #565AFF; font-size: 14px;">Fecha fin</label>
        <input type="date" id="endDate" class="date-input" style="height: 40px; width: 180px; padding: 8px 14px; border: 2px solid #7288FF; border-radius: 10px; font-size: 16px; color: #222; font-family: 'Lato', sans-serif;">
      </div>
      <div class="filter-group" data-filter="vendor" style="width: 180px;">
        <label class="filter-label" style="color: #565AFF; font-size: 14px;">Vendor</label>
        <div class="multi-select" data-multiselect data-placeholder="Seleccionar vendors" style="height: 40px; width: 180px; border: 2px solid #7288FF; border-radius: 10px; padding: 0; background: #fff;">
          <button type="button" class="multi-select-trigger" style="height: 36px; color: #222; font-size: 16px; font-family: 'Lato', sans-serif; padding: 8px 14px; background: transparent; border: none; width: 100%; text-align: left; display: flex; align-items: center;">
            <span class="multi-select-value" style="color: #AFAFAF;">Seleccionar vendors</span>
            <i class="fas fa-chevron-down multi-select-icon"></i>
          </button>
          <div class="multi-select-content" style="border-radius: 10px; border: 2px solid #7288FF; margin-top: 0.25rem; box-shadow: 0 2px 8px rgba(86,90,255,0.08); max-height: 70vh; overflow-y: auto;">
            <div class="multi-select-search">
              <input type="text" placeholder="Buscar vendors... (ESC para limpiar)" class="multi-select-search-input" style="color: #222; font-size: 16px; font-family: 'Lato', sans-serif;">
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
          <div class="multi-select-content" style="border-radius: 10px; border: 2px solid #7288FF; margin-top: 0.25rem; box-shadow: 0 2px 8px rgba(86,90,255,0.08); max-height: 70vh; overflow-y: auto;">
            <div class="multi-select-search">
              <input type="text" placeholder="Buscar productos... (ESC para limpiar)" class="multi-select-search-input" style="color: #222; font-size: 16px; font-family: 'Lato', sans-serif;">
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
          <div class="multi-select-content" style="border-radius: 10px; border: 2px solid #7288FF; margin-top: 0.25rem; box-shadow: 0 2px 8px rgba(86,90,255,0.08); max-height: 70vh; overflow-y: auto;">
            <div class="multi-select-search">
              <input type="text" placeholder="Buscar materiales... (ESC para limpiar)" class="multi-select-search-input" style="color: #222; font-size: 16px; font-family: 'Lato', sans-serif;">
            </div>
            <div class="multi-select-options"></div>
            <div class="multi-select-clear">Limpiar selección</div>
          </div>
        </div>
      </div>
      <div class="action-buttons" style="display: flex; gap: 16px; align-items: flex-end; margin-left: auto;">
        <button class="btn-primary" style="height: 40px; min-width: 100px; padding: 0 18px; font-size: 16px; border-radius: 8px; border: none; background: #565AFF; color: #F7F7F7; font-weight: 700; font-family: 'Lato', sans-serif; display: flex; align-items: center; justify-content: center;">Aceptar</button>
        <button class="btn-secondary" style="height: 40px; min-width: 100px; padding: 0 18px; font-size: 16px; border-radius: 8px; border: 2px solid #565AFF; background: #fff; color: #565AFF; font-weight: 700; font-family: 'Lato', sans-serif; display: flex; align-items: center; gap: 8px; justify-content: center;">
          <i class="fas fa-download"></i>
          Descargar
        </button>
      </div>
    </div>

    <!-- Top Metrics Cards -->
    <div class="metrics-grid" style="display: flex; gap: 16px; margin-top: 24px;">
      <div class="metric-card">
        <p class="metric-label">PO's Activas</p>
        <span class="metric-value" id="totalPosValue"></span>
      </div>
      <div class="metric-card">
        <p class="metric-label">% PO's on time</p>
        <span class="metric-value" id="onTimePercentageValue"></span>
      </div>
      <div class="metric-card">
        <p class="metric-label">% PO's atrasadas</p>
        <span class="metric-value" id="delayedPercentageValue"></span>
      </div>
      <div class="metric-card">
        <p class="metric-label">Material</p>
        <span class="metric-value" id="materialCountValue"></span>
      </div>
    </div>

    <!-- Pie Charts Row -->
    <div class="charts-row">
      <div class="chart-card">
        <h3 class="chart-title">Entregas por hub</h3>
        <div class="chart-content">
          <div class="chart-container">
            <canvas id="hubChart" width="136" height="136"></canvas>
          </div>
          <div class="chart-legend" id="hubLegend"></div>
        </div>
      </div>

      <div class="chart-card">
        <h3 class="chart-title">Estado entrega</h3>
        <div class="chart-content">
          <div class="chart-container">
            <canvas id="statusChart" width="136" height="136"></canvas>
          </div>
          <div class="chart-legend" id="statusLegend"></div>
        </div>
      </div>

      <div class="chart-card">
        <h3 class="chart-title">Tipo de transporte</h3>
        <div class="chart-content">
          <div class="chart-container">
            <canvas id="transportChart" width="136" height="136"></canvas>
          </div>
          <div class="chart-legend" id="transportLegend"></div>
        </div>
      </div>
    </div>

    <!-- Bottom Section -->
    <div class="bottom-section" style="display: flex; gap: 16px; margin-top: 24px; align-items: stretch; min-height: 600px;">
      <!-- Detail Table -->
      <div class="table-card" style="flex: 2; display: flex; flex-direction: column; height: 600px;">
        <h3 class="chart-title">Detalle</h3>
        <div class="table-container" style="flex: 1; overflow-y: auto; height: calc(100% - 40px); border: 1px solid #f3f4f6; border-radius: 8px;">
          <table class="detail-table">
            <thead>
              <tr>
                <th>Fecha salida</th>
                <th>Fecha estimada</th>
                <th class="text-right">Cantidad kgs</th>
                <th class="text-right">N° PO</th>
              </tr>
            </thead>
            <tbody id="detailTableBody">
              <!-- Populated by JavaScript -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- Right Charts Column - Apilados verticalmente con alturas específicas -->
      <div class="right-charts-vertical" style="display: flex; flex-direction: column; gap: 32px; flex: 1; height: 600px;">
        <!-- Motivo de atraso: 1/3 de la altura -->
        <div class="chart-card delay-chart-small" style="flex: 1.3; max-height: 280px; min-height: 220px;">
          <h3 class="chart-title">Motivo de atraso</h3>
          <div class="chart-content">
            <div class="chart-container">
              <canvas id="delayChart" width="136" height="136"></canvas>
            </div>
            <div class="chart-legend" id="delayLegend"></div>
          </div>
        </div>

        <!-- PO's por etapa: 2/3 de la altura -->
        <div class="chart-card stage-chart-large" style="flex: 2; min-height: 300px;">
          <h3 class="chart-title">PO's por etapa</h3>
          <div class="chart-content">
            <div class="chart-container">
              <canvas id="stageChart" width="136" height="136"></canvas>
            </div>
            <div class="chart-legend" id="stageLegend"></div>
          </div>
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
        <h3 class="modal-title success">Registro fue creado correctamente</h3>
        <p class="modal-text">Tarea completada</p>
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
        <p class="modal-text">No se pudo descargar correctamente el reporte</p>
      </div>
      <button id="closeErrorBtn" class="modal-btn">Intentar de nuevo</button>
    </div>
  </div>

    @push('scripts')
        <script>
            window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        </script>
        <script src="{{ asset('js/main.js') }}"></script>
        <link rel="stylesheet" href="{{ asset('css/table-fix.css') }}">
    @endpush
</x-app-layout>
