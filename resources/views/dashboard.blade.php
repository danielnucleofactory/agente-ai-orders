<x-app-layout>
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <style>
            /* Estilos generales para multi-select */
            .multi-select {
                border: 2px solid #28C7A1 !important;
                border-radius: 0.75rem !important;
                padding: 0.25rem 0.75rem !important;
                background: #fff;
                box-sizing: border-box;
            }
            .multi-select-trigger {
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
            
            .filter-button {
                position: relative;
            }
            
            .filter-button.active {
                background: #1AAD8A !important;
                color: #fff !important;
                border-color: #1AAD8A !important;
            }
            
            .filter-button.active span {
                color: rgba(255, 255, 255, 0.9) !important;
            }
            
            .filter-button:hover {
                background: #E6F9F4;
                transform: translateY(-1px);
            }
            
            .filter-button.active:hover {
                background: #127A62 !important;
            }
            
            /* Estilos para filtros de 150x40 - Máxima especificidad para sobrescribir styles.css */
            .filters-section .filter-group {
                width: 150px !important;
                max-width: 150px !important;
            }
            
            .filters-section .date-input,
            .filters-section .date-input-wrapper {
                width: 150px !important;
                max-width: 150px !important;
            }
            
            .filters-section input.flatpickr-alt-input {
                width: 150px !important;
                max-width: 150px !important;
                height: 40px !important;
                min-height: 40px !important;
                max-height: 40px !important;
                padding: 8px 14px !important;
                border: 2px solid #28C7A1 !important;
                border-radius: 10px !important;
                font-size: 16px !important;
                color: #222 !important;
                font-family: 'Lato', sans-serif !important;
                box-sizing: border-box !important;
            }
            
            /* Sobrescribir estilos globales de styles.css con máxima especificidad */
            .filters-section .filter-group .multi-select,
            .filters-section .filter-group div.multi-select,
            .filters-section .filter-group [data-multiselect].multi-select,
            .filters-section .multi-select,
            .filters-section div.multi-select,
            .filters-section [data-multiselect].multi-select {
                width: 150px !important;
                max-width: 150px !important;
                min-width: 150px !important;
                height: 40px !important;
                min-height: 40px !important;
                max-height: 40px !important;
                box-sizing: border-box !important;
            }
            
            .filters-section .filter-group .multi-select-trigger,
            .filters-section .multi-select-trigger,
            .filters-section button.multi-select-trigger {
                width: 100% !important;
                height: 36px !important;
                min-height: 36px !important;
                max-height: 36px !important;
                box-sizing: border-box !important;
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
    <div class="filters-section" style="display: flex; align-items: flex-end; gap: 16px; flex-wrap: wrap; font-family: 'Lato', sans-serif; margin-bottom: 24px;">
      <div class="filter-group" data-filter="date-from" style="width: 150px;">
        <label class="filter-label" style="color: #1AAD8A; font-size: 14px;">Fecha inicio</label>
        <input type="date" id="startDate" class="date-input" style="height: 40px; width: 150px; padding: 8px 14px; border: 2px solid #28C7A1; border-radius: 10px; font-size: 16px; color: #222; font-family: 'Lato', sans-serif;">
      </div>
      <div class="filter-group" data-filter="date-to" style="width: 150px;">
        <label class="filter-label" style="color: #1AAD8A; font-size: 14px;">Fecha fin</label>
        <input type="date" id="endDate" class="date-input" style="height: 40px; width: 150px; padding: 8px 14px; border: 2px solid #28C7A1; border-radius: 10px; font-size: 16px; color: #222; font-family: 'Lato', sans-serif;">
      </div>
      <div class="filter-group" data-filter="customer-type" style="width: 150px;">
        <label class="filter-label" style="color: #1AAD8A; font-size: 14px;">Tipo de cliente</label>
        <div class="multi-select" id="customer-type-filter" data-multiselect data-placeholder="Seleccionar tipo" style="height: 40px; width: 150px; border: 2px solid #28C7A1; border-radius: 10px; padding: 0; background: #fff;">
          <button type="button" class="multi-select-trigger" style="height: 36px; color: #222; font-size: 16px; font-family: 'Lato', sans-serif; padding: 8px 14px; background: transparent; border: none; width: 100%; text-align: left; display: flex; align-items: center;">
            <span class="multi-select-value" style="color: #AFAFAF;">Seleccionar tipo</span>
            <i class="fas fa-chevron-down multi-select-icon"></i>
          </button>
          <div class="multi-select-content" style="border-radius: 10px; border: 2px solid #28C7A1; margin-top: 0.25rem; box-shadow: 0 2px 8px rgba(26,173,138,0.08); max-height: 70vh; overflow-y: auto;">
            <div class="multi-select-search">
              <input type="text" placeholder="Buscar tipo... (ESC para limpiar)" class="multi-select-search-input" style="color: #222; font-size: 16px; font-family: 'Lato', sans-serif;">
            </div>
            <div class="multi-select-options"></div>
            <div class="multi-select-clear">Limpiar selección</div>
          </div>
        </div>
      </div>
      <div class="filter-group" data-filter="arrival-status" style="width: 150px;">
        <label class="filter-label" style="color: #1AAD8A; font-size: 14px;">Estado</label>
        <div class="multi-select" id="arrival-status-filter" data-multiselect data-placeholder="Seleccionar estado" style="height: 40px; width: 150px; border: 2px solid #28C7A1; border-radius: 10px; padding: 0; background: #fff;">
          <button type="button" class="multi-select-trigger" style="height: 36px; color: #222; font-size: 16px; font-family: 'Lato', sans-serif; padding: 8px 14px; background: transparent; border: none; width: 100%; text-align: left; display: flex; align-items: center;">
            <span class="multi-select-value" style="color: #AFAFAF;">Seleccionar estado</span>
            <i class="fas fa-chevron-down multi-select-icon"></i>
          </button>
          <div class="multi-select-content" style="border-radius: 10px; border: 2px solid #28C7A1; margin-top: 0.25rem; box-shadow: 0 2px 8px rgba(26,173,138,0.08); max-height: 70vh; overflow-y: auto;">
            <div class="multi-select-search">
              <input type="text" placeholder="Buscar estado... (ESC para limpiar)" class="multi-select-search-input" style="color: #222; font-size: 16px; font-family: 'Lato', sans-serif;">
            </div>
            <div class="multi-select-options"></div>
            <div class="multi-select-clear">Limpiar selección</div>
          </div>
        </div>
      </div>
      <div class="filter-group" data-filter="vendor" style="width: 150px;">
        <label class="filter-label" style="color: #1AAD8A; font-size: 14px;">Proveedor</label>
        <div class="multi-select" id="vendor-filter-top" data-multiselect data-placeholder="Seleccionar proveedores" style="height: 40px; width: 150px; border: 2px solid #28C7A1; border-radius: 10px; padding: 0; background: #fff;">
          <button type="button" class="multi-select-trigger" style="height: 36px; color: #222; font-size: 16px; font-family: 'Lato', sans-serif; padding: 8px 14px; background: transparent; border: none; width: 100%; text-align: left; display: flex; align-items: center;">
            <span class="multi-select-value" style="color: #AFAFAF;">Seleccionar proveedores</span>
            <i class="fas fa-chevron-down multi-select-icon"></i>
          </button>
          <div class="multi-select-content" style="border-radius: 10px; border: 2px solid #28C7A1; margin-top: 0.25rem; box-shadow: 0 2px 8px rgba(26,173,138,0.08); max-height: 70vh; overflow-y: auto;">
            <div class="multi-select-search">
              <input type="text" placeholder="Buscar proveedores... (ESC para limpiar)" class="multi-select-search-input" style="color: #222; font-size: 16px; font-family: 'Lato', sans-serif;">
            </div>
            <div class="multi-select-options"></div>
            <div class="multi-select-clear">Limpiar selección</div>
          </div>
        </div>
      </div>
      <div class="filter-group" data-filter="departure-port" style="width: 150px;">
        <label class="filter-label" style="color: #1AAD8A; font-size: 14px;">Puerto de embarque</label>
        <div class="multi-select" id="departure-port-filter" data-multiselect data-placeholder="Seleccionar puerto" style="height: 40px; width: 150px; border: 2px solid #28C7A1; border-radius: 10px; padding: 0; background: #fff;">
          <button type="button" class="multi-select-trigger" style="height: 36px; color: #222; font-size: 16px; font-family: 'Lato', sans-serif; padding: 8px 14px; background: transparent; border: none; width: 100%; text-align: left; display: flex; align-items: center;">
            <span class="multi-select-value" style="color: #AFAFAF;">Seleccionar puerto</span>
            <i class="fas fa-chevron-down multi-select-icon"></i>
          </button>
          <div class="multi-select-content" style="border-radius: 10px; border: 2px solid #28C7A1; margin-top: 0.25rem; box-shadow: 0 2px 8px rgba(26,173,138,0.08); max-height: 70vh; overflow-y: auto;">
            <div class="multi-select-search">
              <input type="text" placeholder="Buscar puerto... (ESC para limpiar)" class="multi-select-search-input" style="color: #222; font-size: 16px; font-family: 'Lato', sans-serif;">
            </div>
            <div class="multi-select-options"></div>
            <div class="multi-select-clear">Limpiar selección</div>
          </div>
        </div>
      </div>
      <div class="filter-group" data-filter="arrival-port" style="width: 150px;">
        <label class="filter-label" style="color: #1AAD8A; font-size: 14px;">Puerto de Arribo</label>
        <div class="multi-select" id="arrival-port-filter" data-multiselect data-placeholder="Seleccionar puerto" style="height: 40px; width: 150px; border: 2px solid #28C7A1; border-radius: 10px; padding: 0; background: #fff;">
          <button type="button" class="multi-select-trigger" style="height: 36px; color: #222; font-size: 16px; font-family: 'Lato', sans-serif; padding: 8px 14px; background: transparent; border: none; width: 100%; text-align: left; display: flex; align-items: center;">
            <span class="multi-select-value" style="color: #AFAFAF;">Seleccionar puerto</span>
            <i class="fas fa-chevron-down multi-select-icon"></i>
          </button>
          <div class="multi-select-content" style="border-radius: 10px; border: 2px solid #28C7A1; margin-top: 0.25rem; box-shadow: 0 2px 8px rgba(26,173,138,0.08); max-height: 70vh; overflow-y: auto;">
            <div class="multi-select-search">
              <input type="text" placeholder="Buscar puerto... (ESC para limpiar)" class="multi-select-search-input" style="color: #222; font-size: 16px; font-family: 'Lato', sans-serif;">
            </div>
            <div class="multi-select-options"></div>
            <div class="multi-select-clear">Limpiar selección</div>
          </div>
        </div>
      </div>
      <div class="filter-group" data-filter="shipping-line" style="width: 150px;">
        <label class="filter-label" style="color: #1AAD8A; font-size: 14px;">Naviera</label>
        <div class="multi-select" id="shipping-line-filter" data-multiselect data-placeholder="Seleccionar naviera" style="height: 40px; width: 150px; border: 2px solid #28C7A1; border-radius: 10px; padding: 0; background: #fff;">
          <button type="button" class="multi-select-trigger" style="height: 36px; color: #222; font-size: 16px; font-family: 'Lato', sans-serif; padding: 8px 14px; background: transparent; border: none; width: 100%; text-align: left; display: flex; align-items: center;">
            <span class="multi-select-value" style="color: #AFAFAF;">Seleccionar naviera</span>
            <i class="fas fa-chevron-down multi-select-icon"></i>
          </button>
          <div class="multi-select-content" style="border-radius: 10px; border: 2px solid #28C7A1; margin-top: 0.25rem; box-shadow: 0 2px 8px rgba(26,173,138,0.08); max-height: 70vh; overflow-y: auto;">
            <div class="multi-select-search">
              <input type="text" placeholder="Buscar naviera... (ESC para limpiar)" class="multi-select-search-input" style="color: #222; font-size: 16px; font-family: 'Lato', sans-serif;">
            </div>
            <div class="multi-select-options"></div>
            <div class="multi-select-clear">Limpiar selección</div>
          </div>
        </div>
      </div>
      <div class="filter-group" data-filter="service-provider" style="width: 150px;">
        <label class="filter-label" style="color: #1AAD8A; font-size: 14px;">Proveedor de servicios</label>
        <div class="multi-select" id="service-provider-filter" data-multiselect data-placeholder="Seleccionar proveedor" style="height: 40px; width: 150px; border: 2px solid #28C7A1; border-radius: 10px; padding: 0; background: #fff;">
          <button type="button" class="multi-select-trigger" style="height: 36px; color: #222; font-size: 16px; font-family: 'Lato', sans-serif; padding: 8px 14px; background: transparent; border: none; width: 100%; text-align: left; display: flex; align-items: center;">
            <span class="multi-select-value" style="color: #AFAFAF;">Seleccionar proveedor</span>
            <i class="fas fa-chevron-down multi-select-icon"></i>
          </button>
          <div class="multi-select-content" style="border-radius: 10px; border: 2px solid #28C7A1; margin-top: 0.25rem; box-shadow: 0 2px 8px rgba(26,173,138,0.08); max-height: 70vh; overflow-y: auto;">
            <div class="multi-select-search">
              <input type="text" placeholder="Buscar proveedor... (ESC para limpiar)" class="multi-select-search-input" style="color: #222; font-size: 16px; font-family: 'Lato', sans-serif;">
            </div>
            <div class="multi-select-options"></div>
            <div class="multi-select-clear">Limpiar selección</div>
          </div>
        </div>
      </div>
      <div class="action-buttons" style="display: flex; gap: 16px; align-items: flex-end; margin-left: auto;">
        <button class="btn-primary" id="apply-filters-btn-top" style="height: 40px; min-width: 100px; padding: 0 18px; font-size: 16px; border-radius: 8px; border: none; background: #1AAD8A; color: #F7F7F7; font-weight: 700; font-family: 'Lato', sans-serif; display: flex; align-items: center; justify-content: center;">Aceptar</button>
        <button id="export-btn" class="btn-secondary" style="height: 40px; min-width: 100px; padding: 0 18px; font-size: 16px; border-radius: 8px; border: 2px solid #1AAD8A; background: #fff; color: #1AAD8A; font-weight: 700; font-family: 'Lato', sans-serif; display: flex; align-items: center; gap: 8px; justify-content: center;">
          <i class="fas fa-download"></i>
          Descargar
        </button>
      </div>
    </div>

    <!-- Trend Table Section with Filters Panel -->
    <div class="trend-table-section" style="position: relative; width: calc(100% + 5rem); max-width: calc(100% + 5rem); margin-left: -2.5rem; margin-right: -2.5rem; padding-left: 2.5rem; padding-right: 2.5rem; box-sizing: border-box;">
      <!-- Table and Filters Container -->
      <div style="display: flex; gap: 16px; width: 100%; box-sizing: border-box;">
        <!-- Trend Table - 2/3 width -->
        <div class="table-card" style="display: flex; flex-direction: column; width: 66.67%; box-sizing: border-box; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
          <h3 class="chart-title" style="text-align: center; margin-bottom: 16px; font-size: 18px; font-weight: 600; color: #374151; font-family: 'Lato', sans-serif;">Tendencia por Etapas del Kanban</h3>
          <div class="table-container" style="overflow-x: auto; width: 100%; max-width: 100%; box-sizing: border-box;">
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

        <!-- Filters Panel - 1/3 width -->
        <div class="filters-panel" style="width: 33.33%; box-sizing: border-box;">
          <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="font-size: 18px; font-weight: 600; color: #374151; margin-bottom: 20px; font-family: 'Lato', sans-serif;">Filtros Adicionales</h3>
            
            <!-- Additional Filters Buttons -->
            <div class="additional-filters-buttons" style="display: flex; flex-direction: column; gap: 12px;">
              <button type="button" class="filter-button" id="btn-po-retraso-cl" data-filter="po_retraso_cl" style="width: 100%; min-height: 60px; padding: 12px 16px; font-size: 14px; border-radius: 8px; border: 2px solid #28C7A1; background: #fff; color: #1AAD8A; font-weight: 600; font-family: 'Lato', sans-serif; cursor: pointer; transition: all 0.2s; text-align: left; display: flex; flex-direction: column; justify-content: center;">
                <span style="line-height: 1.2; font-size: 15px;">PO Retraso CL</span>
                <span style="font-size: 12px; color: #6b7280; display: block; font-weight: 400; margin-top: 4px; line-height: 1.3;">Retraso > 7 días en carga lista</span>
              </button>
              <button type="button" class="filter-button" id="btn-po-adelanto-cl" data-filter="po_adelanto_cl" style="width: 100%; min-height: 60px; padding: 12px 16px; font-size: 14px; border-radius: 8px; border: 2px solid #28C7A1; background: #fff; color: #1AAD8A; font-weight: 600; font-family: 'Lato', sans-serif; cursor: pointer; transition: all 0.2s; text-align: left; display: flex; flex-direction: column; justify-content: center;">
                <span style="line-height: 1.2; font-size: 15px;">PO Adelanto CL</span>
                <span style="font-size: 12px; color: #6b7280; display: block; font-weight: 400; margin-top: 4px; line-height: 1.3;">Adelanto > 7 días en carga lista</span>
              </button>
              <button type="button" class="filter-button" id="btn-indicador-capacidad" data-filter="indicador_capacidad" style="width: 100%; min-height: 60px; padding: 12px 16px; font-size: 14px; border-radius: 8px; border: 2px solid #28C7A1; background: #fff; color: #1AAD8A; font-weight: 600; font-family: 'Lato', sans-serif; cursor: pointer; transition: all 0.2s; text-align: left; display: flex; flex-direction: column; justify-content: center;">
                <span style="line-height: 1.2; font-size: 15px;">Indicador Capacidad</span>
                <span style="font-size: 12px; color: #6b7280; display: block; font-weight: 400; margin-top: 4px; line-height: 1.3;">PO sin fecha ETD</span>
              </button>
            </div>
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
            // Pass filter options to JavaScript
            window.filterOptions = @json($filterOptions ?? []);
        </script>
        <script src="{{ asset('js/dashboard-dynamic.js') }}"></script>
        <script src="{{ asset('js/main.js') }}"></script>
        <link rel="stylesheet" href="{{ asset('css/table-fix.css') }}">
    @endpush
</x-app-layout>
