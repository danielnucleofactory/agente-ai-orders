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
                border: 2px solid #28C7A1 !important;
                border-radius: 0.75rem !important;
                padding: 0.25rem 0.75rem !important;
                background: #fff;
                min-height: 42px;
                box-sizing: border-box;
            }
            .multi-select-trigger {
                min-height: 38px;
                font-size: 1rem;
                color: #1AAD8A;
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
                border: 2px solid #28C7A1;
                margin-top: 0.25rem;
                box-shadow: 0 2px 8px rgba(26,173,138,0.08);
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
                background: #1AAD8A;
                border-radius: 4px;
            }
            
            .multi-select-content::-webkit-scrollbar-thumb:hover {
                background: #127A62;
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
                background-color: #E6F9F4;
            }
            

            .multi-select-search-input {
                border-radius: 0.5rem;
                border: 1px solid #28C7A1;
                padding: 0.125rem 0.375rem;
                margin-bottom: 0.125rem;
                width: 100%;
                font-size: 12px;
            }
            .multi-select-value {
                color: #1AAD8A;
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
        <label class="filter-label" style="color: #1AAD8A; font-size: 14px;">Fecha inicio</label>
        <input type="date" id="startDate" class="date-input" style="height: 40px; width: 180px; padding: 8px 14px; border: 2px solid #28C7A1; border-radius: 10px; font-size: 16px; color: #222; font-family: 'Lato', sans-serif;">
      </div>
      <div class="filter-group" data-filter="date-to" style="width: 180px;">
        <label class="filter-label" style="color: #1AAD8A; font-size: 14px;">Fecha fin</label>
        <input type="date" id="endDate" class="date-input" style="height: 40px; width: 180px; padding: 8px 14px; border: 2px solid #28C7A1; border-radius: 10px; font-size: 16px; color: #222; font-family: 'Lato', sans-serif;">
      </div>
      <div class="filter-group" data-filter="vendor" style="width: 180px;">
        <label class="filter-label" style="color: #1AAD8A; font-size: 14px;">Vendor</label>
        <div class="multi-select" data-multiselect data-placeholder="Seleccionar vendors" style="height: 40px; width: 180px; border: 2px solid #28C7A1; border-radius: 10px; padding: 0; background: #fff;">
          <button type="button" class="multi-select-trigger" style="height: 36px; color: #222; font-size: 16px; font-family: 'Lato', sans-serif; padding: 8px 14px; background: transparent; border: none; width: 100%; text-align: left; display: flex; align-items: center;">
            <span class="multi-select-value" style="color: #AFAFAF;">Seleccionar vendors</span>
            <i class="fas fa-chevron-down multi-select-icon"></i>
          </button>
          <div class="multi-select-content" style="border-radius: 10px; border: 2px solid #28C7A1; margin-top: 0.25rem; box-shadow: 0 2px 8px rgba(26,173,138,0.08); max-height: 70vh; overflow-y: auto;">
            <div class="multi-select-search">
              <input type="text" placeholder="Buscar vendors... (ESC para limpiar)" class="multi-select-search-input" style="color: #222; font-size: 16px; font-family: 'Lato', sans-serif;">
            </div>
            <div class="multi-select-options"></div>
            <div class="multi-select-clear">Limpiar selección</div>
          </div>
        </div>
      </div>
      <div class="action-buttons" style="display: flex; gap: 16px; align-items: flex-end; margin-left: auto;">
        <button class="btn-primary" style="height: 40px; min-width: 100px; padding: 0 18px; font-size: 16px; border-radius: 8px; border: none; background: #1AAD8A; color: #F7F7F7; font-weight: 700; font-family: 'Lato', sans-serif; display: flex; align-items: center; justify-content: center;">Aceptar</button>
        <button id="export-btn" class="btn-secondary" style="height: 40px; min-width: 100px; padding: 0 18px; font-size: 16px; border-radius: 8px; border: 2px solid #1AAD8A; background: #fff; color: #1AAD8A; font-weight: 700; font-family: 'Lato', sans-serif; display: flex; align-items: center; gap: 8px; justify-content: center;">
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
    </div>

    <!-- Trend Table Section - Full Width -->
    <div class="trend-table-section" style="position: relative; margin-top: 24px; width: calc(100% + 5rem); max-width: calc(100% + 5rem); margin-left: -2.5rem; margin-right: -2.5rem; padding-left: 2.5rem; padding-right: 2.5rem; box-sizing: border-box;">
      <!-- Header with metadata -->
      <div class="trend-table-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding: 16px; background: #f9fafb; border-radius: 8px; width: 100%; box-sizing: border-box;">
        <div class="header-left" style="display: flex; flex-direction: column; gap: 4px; flex-shrink: 0;">
          <span style="font-size: 14px; color: #374151;">Vista: Global</span>
          <span style="font-size: 14px; color: #374151;">CC: Todas</span>
          <span style="font-size: 14px; color: #374151;">Unidades: Miles de Dolares</span>
        </div>
        <div class="header-right" style="display: flex; flex-direction: column; gap: 4px; text-align: right; flex-shrink: 0;">
          <span style="font-size: 14px; color: #374151;">Fecha: Actual</span>
          <span style="font-size: 14px; color: #374151;">Ejercicio: Mes</span>
          <span style="font-size: 14px; color: #374151;">Periodo: <span id="currentYear">{{ now()->year }}</span></span>
        </div>
      </div>

      <!-- Trend Table -->
      <div class="table-card" style="display: flex; flex-direction: column; width: 100%; max-width: 100%; box-sizing: border-box;">
        <h3 class="chart-title" style="text-align: center; margin-bottom: 16px;">Tendencia para Lineas canceladas con inventario</h3>
        <div class="table-container" style="overflow-x: auto; border: 1px solid #e5e7eb; border-radius: 8px; width: 100%; max-width: 100%; box-sizing: border-box;">
          <table class="trend-table" id="trendTable" style="width: 100%; border-collapse: collapse; table-layout: fixed;">
            <thead>
              <tr style="background: #f9fafb;">
                <th style="padding: 12px; text-align: left; border: 1px solid #e5e7eb; font-weight: 600; color: #374151; width: 15%;">Descripción</th>
                <th class="month-header" data-month="1" style="padding: 12px; text-align: center; border: 1px solid #e5e7eb; font-weight: 600; color: #374151; width: calc(85% / 12);">1-<span class="year">{{ now()->year }}</span></th>
                <th class="month-header" data-month="2" style="padding: 12px; text-align: center; border: 1px solid #e5e7eb; font-weight: 600; color: #374151; width: calc(85% / 12);">2-<span class="year">{{ now()->year }}</span></th>
                <th class="month-header" data-month="3" style="padding: 12px; text-align: center; border: 1px solid #e5e7eb; font-weight: 600; color: #374151; width: calc(85% / 12);">3-<span class="year">{{ now()->year }}</span></th>
                <th class="month-header" data-month="4" style="padding: 12px; text-align: center; border: 1px solid #e5e7eb; font-weight: 600; color: #374151; width: calc(85% / 12);">4-<span class="year">{{ now()->year }}</span></th>
                <th class="month-header" data-month="5" style="padding: 12px; text-align: center; border: 1px solid #e5e7eb; font-weight: 600; color: #374151; width: calc(85% / 12);">5-<span class="year">{{ now()->year }}</span></th>
                <th class="month-header" data-month="6" style="padding: 12px; text-align: center; border: 1px solid #e5e7eb; font-weight: 600; color: #374151; width: calc(85% / 12);">6-<span class="year">{{ now()->year }}</span></th>
                <th class="month-header" data-month="7" style="padding: 12px; text-align: center; border: 1px solid #e5e7eb; font-weight: 600; color: #374151; width: calc(85% / 12);">7-<span class="year">{{ now()->year }}</span></th>
                <th class="month-header" data-month="8" style="padding: 12px; text-align: center; border: 1px solid #e5e7eb; font-weight: 600; color: #374151; width: calc(85% / 12);">8-<span class="year">{{ now()->year }}</span></th>
                <th class="month-header" data-month="9" style="padding: 12px; text-align: center; border: 1px solid #e5e7eb; font-weight: 600; color: #374151; width: calc(85% / 12);">9-<span class="year">{{ now()->year }}</span></th>
                <th class="month-header" data-month="10" style="padding: 12px; text-align: center; border: 1px solid #e5e7eb; font-weight: 600; color: #374151; width: calc(85% / 12);">10-<span class="year">{{ now()->year }}</span></th>
                <th class="month-header" data-month="11" style="padding: 12px; text-align: center; border: 1px solid #e5e7eb; font-weight: 600; color: #374151; width: calc(85% / 12);">11-<span class="year">{{ now()->year }}</span></th>
                <th class="month-header" data-month="12" style="padding: 12px; text-align: center; border: 1px solid #e5e7eb; font-weight: 600; color: #374151; width: calc(85% / 12);">12-<span class="year">{{ now()->year }}</span></th>
              </tr>
            </thead>
            <tbody id="trendTableBody">
              <!-- Populated by JavaScript -->
            </tbody>
          </table>
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
        <h3 class="modal-title success">Archivo descargado exitosamente</h3>
        <p class="modal-text">El archivo CSV se ha descargado correctamente</p>
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
            // Pass initial dashboard data to JavaScript
            window.dashboardData = @json($dashboardData ?? []);
        </script>
        <script src="{{ asset('js/dashboard-dynamic.js') }}"></script>
        <script src="{{ asset('js/main.js') }}"></script>
        <link rel="stylesheet" href="{{ asset('css/table-fix.css') }}">
    @endpush
</x-app-layout>
