// main.js - Dashboard principal con datos reales del backend

// Lista de nombres reales de etapas del kanban_board_id=1
const kanbanBoard1Stages = [
  "Recepción",
  "Consolidación en Hub teorico", 
  "Validación operativa con el cliente",
  "Pick Up",
  "En tránsito terrestre",
  "Llegada al hub",
  "Consolidación en Hub real"
];

// Estado de filtros activos
const activeFilters = {
  hub_id: [],
  status: [],
  transport: [],
  delay: [],
  stage: [],
  product_id: [],
  material_type: [],
  delay_reason: [],
  date_from: null,
  date_to: null,
  vendor_id: [],
};

// Colores predefinidos para gráficos
const defaultColors = ["#565aff", "#9aabff", "#ff3459", "#f46844", "#c9cfff", "#5ae7f4", "#5dd595"];

// Referencias a los gráficos
let hubChartInstance = null;
let statusChartInstance = null;

// Variables de control para evitar llamadas múltiples
let updateDashboardTimeout = null;
let isUpdating = false;
let chartsCreated = false;

// Función para obtener los datos del backend
async function fetchDashboardData() {
  try {
    // Serializar filtros como query string
    const params = new URLSearchParams();
    // Serializar todos los filtros activos
    if (activeFilters.hub_id && activeFilters.hub_id.length > 0) {
      activeFilters.hub_id.forEach(id => params.append('hub_id[]', id));
    }
    if (activeFilters.status && activeFilters.status.length > 0) {
      activeFilters.status.forEach(status => params.append('status[]', status));
    }
    if (activeFilters.product_id && activeFilters.product_id.length > 0) {
      activeFilters.product_id.forEach(pid => params.append('product_id[]', pid));
    }
    if (activeFilters.material_type && activeFilters.material_type.length > 0) {
      activeFilters.material_type.forEach(mat => params.append('material_type[]', mat));
    }
    if (activeFilters.vendor_id && activeFilters.vendor_id.length > 0) {
      activeFilters.vendor_id.forEach(vid => params.append('vendor_id[]', vid));
    }
    if (activeFilters.transport && activeFilters.transport.length > 0) {
      activeFilters.transport.forEach(tr => params.append('transport[]', tr));
    }
    if (activeFilters.stage && activeFilters.stage.length > 0) {
      console.log('Serializando filtro stage:', activeFilters.stage);
      activeFilters.stage.forEach(st => params.append('stage[]', st));
    }
    if (activeFilters.date_from) {
      params.append('date_from', activeFilters.date_from);
    }
    if (activeFilters.date_to) {
      params.append('date_to', activeFilters.date_to);
    }
    if (activeFilters.delay_reason && activeFilters.delay_reason.length > 0) {
      console.log('Serializando filtro delay_reason:', activeFilters.delay_reason);
      activeFilters.delay_reason.forEach(dr => params.append('delay_reason[]', dr));
    }
    // Puedes agregar más filtros aquí si los necesitas
    const response = await fetch('/dashboard/data?' + params.toString(), {
      method: 'GET',
    });
    if (!response.ok) throw new Error('Error al obtener datos');
    const result = await response.json();
    console.log('Respuesta AJAX:', result);
    if (!result.success) throw new Error(result.message || 'Error en backend');
    return result;
  } catch (e) {
    showError('No se pudo cargar el dashboard: ' + e.message);
    return null;
  }
}

// Renderizar gráfico de Entregas por hub
function renderHubChart(hubData) {
  const ctx = document.getElementById('hubChart').getContext('2d');
  // Destruir cualquier gráfico existente en este canvas
  const prevChart = Chart.getChart('hubChart');
  if (prevChart) {
    console.log('Destruyendo gráfico de hub anterior');
    prevChart.destroy();
  }
  if (hubChartInstance) {
    console.log('Limpiando instancia de hubChart anterior');
    hubChartInstance = null;
  }
  
  // IMPORTANTE: Limpiar el canvas completamente para evitar event listeners acumulados
  const canvas = document.getElementById('hubChart');
  canvas.replaceWith(canvas.cloneNode(true));
  // Obtener nuevamente el contexto del canvas limpio
  const cleanCtx = document.getElementById('hubChart').getContext('2d');
  
  const labels = hubData.map(item => item.name);
  // Para el gráfico, usar un mínimo de 0.1% para elementos con 0 para que aparezcan
  const data = hubData.map(item => item.percentage > 0 ? item.percentage : 0.1);
  const originalData = hubData.map(item => item.percentage); // Datos originales para tooltips
  const ids = hubData.map(item => String(item.id)); // Convertir todos los IDs a string para consistencia
  const colors = ["#565aff", "#9aabff", "#ff3459", "#f46844", "#c9cfff", "#5ae7f4", "#5dd595"];
  
  // Lógica de aclarado: si hay filtros activos, los no seleccionados se aclaran
  let backgroundColors = colors.slice(0, data.length);
  if (activeFilters.hub_id && activeFilters.hub_id.length > 0) {
    const activeHubIds = activeFilters.hub_id.map(id => String(id)); // Convertir filtros a string también
    backgroundColors = ids.map((id, idx) => activeHubIds.includes(id) ? colors[idx % colors.length] : colors[idx % colors.length] + '33');
  }
  
  hubChartInstance = new Chart(cleanCtx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: backgroundColors,
        borderWidth: 0,
        cutout: '70%',
      }],
    },
    options: {
      responsive: false,
      maintainAspectRatio: false,
      onClick: function(evt, elements) {
        if (elements.length > 0) {
          const idx = elements[0].index;
          const hubId = String(ids[idx]);
          const activeHubIds = activeFilters.hub_id.map(id => String(id));
          
          console.log('=== HUB CLICK DEBUG ===');
          console.log('Click en índice:', idx);
          console.log('Hub data completo:', hubData[idx]);
          console.log('Hub ID extraído:', hubId);
          console.log('Tipo de Hub ID:', typeof hubId);
          console.log('IDs array completo:', ids);
          console.log('Filtros activos antes (RAW):', activeFilters.hub_id);
          console.log('Filtros activos antes (STRING):', activeHubIds);
          console.log('¿Hub ya incluido?', activeHubIds.includes(hubId));
          
          // 🔍 DEBUGGING ESPECÍFICO PARA EWR vs MIA
          if (hubId === '2') {
            console.log('🔴 EWR CLICK DETECTED - Análisis detallado:');
            console.log('   Hub name:', hubData[idx].name);
            console.log('   Hub percentage:', hubData[idx].percentage);
            console.log('   Hub value:', hubData[idx].value);
            console.log('   Active filters before:', JSON.parse(JSON.stringify(activeFilters)));
          } else if (hubId === '1') {
            console.log('🟢 MIA CLICK DETECTED - Análisis detallado:');
            console.log('   Hub name:', hubData[idx].name);
            console.log('   Hub percentage:', hubData[idx].percentage);
            console.log('   Hub value:', hubData[idx].value);
            console.log('   Active filters before:', JSON.parse(JSON.stringify(activeFilters)));
          }
          
          if (activeHubIds.includes(hubId)) {
            console.log('→ REMOVIENDO filtro');
            activeFilters.hub_id = activeFilters.hub_id.filter(id => String(id) !== hubId);
          } else {
            console.log('→ AÑADIENDO filtro');
            activeFilters.hub_id.push(hubData[idx].id);
          }
          
          console.log('Filtros activos después:', JSON.stringify(activeFilters.hub_id));
          
          // 🔍 DEBUGGING FINAL
          if (hubId === '2' || hubId === '1') {
            console.log(`🔍 ${hubId === '2' ? 'EWR' : 'MIA'} - Estado final:`, {
              activeFiltersAfter: JSON.parse(JSON.stringify(activeFilters)),
              aboutToCallUpdate: true,
              timestamp: new Date().toISOString()
            });
          }
          
          console.log('=== LLAMANDO updateDashboardUI ===');
          updateDashboardUI();
        }
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function(context) {
              // Usar datos originales en tooltip
              return context.label + ': ' + originalData[context.dataIndex] + '%';
            }
          }
        }
      }
    },
  });
  
  // Leyenda
  const legendContainer = document.getElementById('hubLegend');
  legendContainer.innerHTML = '';
  hubData.forEach((item, i) => {
    const legendItem = document.createElement('div');
    legendItem.className = 'legend-item';
    legendItem.innerHTML = `
      <div class="legend-label">
        <div class="legend-color" style="background-color: ${colors[i % colors.length]}"></div>
        <span>${item.name}</span>
      </div>
      <span>${item.percentage}%</span>
    `;
    legendContainer.appendChild(legendItem);
  });
}

// Renderizar gráfico de Estado entrega
function renderStatusChart(statusData) {
  const ctx = document.getElementById('statusChart').getContext('2d');
  // Destruir cualquier gráfico existente en este canvas
  const prevChart = Chart.getChart('statusChart');
  if (prevChart) {
    console.log('Destruyendo gráfico de status anterior');
    prevChart.destroy();
  }
  if (statusChartInstance) {
    console.log('Limpiando instancia de statusChart anterior');
    statusChartInstance = null;
  }
  
  // IMPORTANTE: Limpiar el canvas completamente para evitar event listeners acumulados
  const canvas = document.getElementById('statusChart');
  canvas.replaceWith(canvas.cloneNode(true));
  // Obtener nuevamente el contexto del canvas limpio
  const cleanCtx = document.getElementById('statusChart').getContext('2d');
  
  const labels = statusData.map(item => item.name);
  const data = statusData.map(item => item.percentage);
  const values = statusData.map(item => item.name); // "On Time", "Atrasado" o "Sin datos"
  // Colores específicos para cada estado
  const colors = {
    "On Time": "#565aff",
    "Atrasado": "#c9cfff",
    "Sin datos": "#f0f0f0"
  };
  const backgroundColors = statusData.map(item => colors[item.name] || "#c9cfff");
  
  // Aplicar lógica de aclarado al crear el gráfico
  if (activeFilters.status && activeFilters.status.length > 0) {
    backgroundColors.forEach((color, idx) => {
      const isSelected = statusData[idx].values && statusData[idx].values.some(val => activeFilters.status.includes(val));
      backgroundColors[idx] = isSelected ? color : color + '33';
    });
  }
  
  statusChartInstance = new Chart(cleanCtx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: backgroundColors,
        borderWidth: 0,
        cutout: '70%',
      }],
    },
    options: {
      responsive: false,
      maintainAspectRatio: false,
      onClick: function(evt, elements) {
        if (elements.length > 0) {
          const idx = elements[0].index;
          const statusValue = values[idx];
          
          // No filtrar por "Sin datos" ya que no es un estado real
          if (statusValue === "Sin datos") return;
          
          if (activeFilters.status.includes(statusValue)) {
            activeFilters.status = activeFilters.status.filter(s => s !== statusValue);
          } else {
            activeFilters.status.push(statusValue);
          }
          updateDashboardUI();
        }
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function(context) {
              return context.label + ': ' + context.parsed + '%';
            }
          }
        }
      }
    },
  });
  // Leyenda
  const legendContainer = document.getElementById('statusLegend');
  legendContainer.innerHTML = '';
  statusData.forEach((item) => {
    const legendItem = document.createElement('div');
    legendItem.className = 'legend-item';
    legendItem.innerHTML = `
      <div class="legend-label">
        <div class="legend-color" style="background-color: ${colors[item.name] || "#c9cfff"}"></div>
        <span>${item.name}</span>
      </div>
      <span>${item.percentage}%</span>
    `;
    legendContainer.appendChild(legendItem);
  });
}

// Función para mostrar errores
function showError(msg) {
  alert(msg); // Puedes mejorar esto con un modal si lo deseas
}

// Renderizar indicadores superiores
function renderTopMetrics(metrics) {
  console.log('renderTopMetrics recibe:', metrics);
  if (document.getElementById('totalPosValue')) {
    document.getElementById('totalPosValue').textContent = metrics.total_pos;
  }
  if (document.getElementById('onTimePercentageValue')) {
    document.getElementById('onTimePercentageValue').textContent = metrics.on_time_percentage.toLocaleString('es-ES', {minimumFractionDigits: 1, maximumFractionDigits: 1}) + '%';
  }
  if (document.getElementById('delayedPercentageValue')) {
    document.getElementById('delayedPercentageValue').textContent = metrics.delayed_percentage.toLocaleString('es-ES', {minimumFractionDigits: 1, maximumFractionDigits: 1}) + '%';
  }
  if (document.getElementById('materialCountValue')) {
    document.getElementById('materialCountValue').textContent = metrics.material_count;
  }
}

// Renderizar gráfico de Tipo de transporte
function createPieChart(canvasId, data, legendId, onClickHandler) {
  // --- DESTRUIR GRÁFICO ANTERIOR SI EXISTE ---
  const prevChart = Chart.getChart(canvasId);
  if (prevChart) {
    console.log(`Destruyendo gráfico anterior: ${canvasId}`);
    prevChart.destroy();
  }

  // IMPORTANTE: Limpiar el canvas completamente para evitar event listeners acumulados
  const canvas = document.getElementById(canvasId);
  if (canvas) {
    canvas.replaceWith(canvas.cloneNode(true));
  }
  
  // Obtener nuevamente el contexto del canvas limpio
  const ctx = document.getElementById(canvasId).getContext("2d");
  
  // Aplicar lógica de aclarado al crear el gráfico
  let backgroundColors = data.map((item) => item.color);
  
  if (canvasId === 'transportChart' && activeFilters.transport && activeFilters.transport.length > 0) {
    backgroundColors = data.map((item) => {
      const isSelected = item.values && item.values.some(val => activeFilters.transport.includes(val));
      return isSelected ? item.color : item.color + '33';
    });
  } else if (canvasId === 'statusChart' && activeFilters.status && activeFilters.status.length > 0) {
    backgroundColors = data.map((item) => {
      const isSelected = item.values && item.values.some(val => activeFilters.status.includes(val));
      return isSelected ? item.color : item.color + '33';
    });
  } else if (canvasId === 'delayChart' && activeFilters.delay_reason && activeFilters.delay_reason.length > 0) {
    backgroundColors = data.map((item) => {
      const isSelected = activeFilters.delay_reason.includes(item.name);
      return isSelected ? item.color : item.color + '33';
    });
  } else if (canvasId === 'stageChart' && activeFilters.stage && activeFilters.stage.length > 0) {
    backgroundColors = data.map((item) => {
      const isSelected = activeFilters.stage.includes(item.name);
      return isSelected ? item.color : item.color + '33';
    });
  }
  
  // Para el gráfico, usar un mínimo de 0.1 para elementos con 0 para que aparezcan
  const displayData = data.map((item) => item.value > 0 ? item.value : 0.1);
  const originalValues = data.map((item) => item.value); // Valores originales para tooltips
  
  const chart = new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: data.map((item) => item.name),
      datasets: [
        {
          data: displayData,
          backgroundColor: backgroundColors,
          borderWidth: 0,
          cutout: "70%",
        },
      ],
    },
    options: {
      responsive: false,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function(context) {
              // Usar valores originales en tooltip
              const originalValue = originalValues[context.dataIndex];
              return context.label + ': ' + (typeof data[context.dataIndex].percentage !== 'undefined' ? data[context.dataIndex].percentage + '%' : originalValue);
            }
          }
        }
      },
      onClick: onClickHandler || undefined,
    },
  });
  window[canvasId] = chart;
  // Leyenda
  const legendContainer = document.getElementById(legendId);
  legendContainer.innerHTML = "";
  data.forEach((item) => {
    const legendItem = document.createElement("div");
    legendItem.className = "legend-item";
    legendItem.innerHTML = `
      <div class="legend-label">
        <div class="legend-color" style="background-color: ${item.color}"></div>
        <span>${item.name}</span>
      </div>
      <span>${typeof item.percentage !== 'undefined' ? item.percentage + '%' : item.value}</span>
    `;
    legendContainer.appendChild(legendItem);
  });
}

// Renderizar gráfico de Motivo de atraso
function renderDelayChart(delayData) {
  const ctx = document.getElementById('delayChart').getContext('2d');
  const prevChart = Chart.getChart('delayChart');
  if (prevChart) prevChart.destroy();
  
  // Limpiar el canvas para evitar problemas de eventos acumulados
  const canvas = document.getElementById('delayChart');
  canvas.replaceWith(canvas.cloneNode(true));
  const cleanCtx = document.getElementById('delayChart').getContext('2d');
  
  const labels = delayData.map(item => item.name);
  const data = delayData.map(item => item.percentage > 0 ? item.percentage : 0.1); // Valor mínimo para visualización
  const originalData = delayData.map(item => item.percentage); // Datos originales para tooltips
  const colors = ["#565aff", "#9aabff", "#5ae7f4", "#f46844", "#5dd595", "#c9cfff"];
  
  // Aplicar lógica de aclarado si hay filtros activos
  let backgroundColors = colors.slice(0, data.length);
  if (activeFilters.delay_reason && activeFilters.delay_reason.length > 0) {
    backgroundColors = labels.map((name, idx) => 
      activeFilters.delay_reason.includes(name) ? colors[idx % colors.length] : colors[idx % colors.length] + '33');
  }
  
  new Chart(cleanCtx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: backgroundColors,
        borderWidth: 0,
        cutout: '70%',
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function(context) {
              // Usar datos originales en tooltip
              return context.label + ': ' + originalData[context.dataIndex] + '%';
            }
          }
        }
      },
      onClick: function(evt, elements) {
        if (elements.length > 0) {
          const idx = elements[0].index;
          const motivo = labels[idx];
          
          if (activeFilters.delay_reason.includes(motivo)) {
            activeFilters.delay_reason = [];
          } else {
            activeFilters.delay_reason = [motivo];
          }
          updateDashboardUI();
        }
      }
    },
  });
  
  // Leyenda mejorada con scroll si hay muchos items
  const legendContainer = document.getElementById('delayLegend');
  legendContainer.innerHTML = '';
  
  // Ordenar por porcentaje descendente para mejor visualización
  const sortedData = [...delayData].sort((a, b) => b.percentage - a.percentage);
  
  sortedData.forEach((item, i) => {
    const legendItem = document.createElement('div');
    legendItem.className = 'legend-item';
    legendItem.innerHTML = `
      <div class="legend-label">
        <div class="legend-color" style="background-color: ${colors[i % colors.length]}"></div>
        <span>${item.name}</span>
      </div>
      <span>${item.percentage}%</span>
    `;
    // Hacer clic en la leyenda también filtra
    legendItem.style.cursor = 'pointer';
    legendItem.addEventListener('click', () => {
      if (activeFilters.delay_reason.includes(item.name)) {
        activeFilters.delay_reason = [];
      } else {
        activeFilters.delay_reason = [item.name];
      }
      updateDashboardUI();
    });
    legendContainer.appendChild(legendItem);
  });
}

// Renderizar gráfico de PO's por etapa
function renderStageChart(stageData) {
  const ctx = document.getElementById('stageChart').getContext('2d');
  const prevChart = Chart.getChart('stageChart');
  if (prevChart) prevChart.destroy();
  
  // Limpiar el canvas para evitar problemas de eventos acumulados
  const canvas = document.getElementById('stageChart');
  canvas.replaceWith(canvas.cloneNode(true));
  const cleanCtx = document.getElementById('stageChart').getContext('2d');
  
  const labels = stageData.map(item => item.name);
  const data = stageData.map(item => item.value > 0 ? item.value : 0.1); // Valor mínimo para visualización
  const originalData = stageData.map(item => item.value); // Datos originales para tooltips
  const colors = stageData.map(item => item.color || "#c9cfff"); // Usar colores definidos en el backend
  
  // Aplicar lógica de aclarado si hay filtros activos
  let backgroundColors = colors;
  if (activeFilters.stage && activeFilters.stage.length > 0) {
    backgroundColors = labels.map((name, idx) => 
      activeFilters.stage.includes(name) ? colors[idx] : colors[idx] + '33');
  }
  
  new Chart(cleanCtx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: backgroundColors,
        borderWidth: 0,
        cutout: '70%',
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function(context) {
              // Usar datos originales en tooltip
              return context.label + ': ' + originalData[context.dataIndex];
            }
          }
        }
      },
      onClick: function(evt, elements) {
        if (elements.length > 0) {
          const idx = elements[0].index;
          const stageName = labels[idx];
          
          if (activeFilters.stage.includes(stageName)) {
            activeFilters.stage = [];
          } else {
            activeFilters.stage = [stageName];
          }
          updateDashboardUI();
        }
      }
    },
  });
  
  // Leyenda mejorada con scroll si hay muchos items
  const legendContainer = document.getElementById('stageLegend');
  legendContainer.innerHTML = '';
  
  // Ordenar por valor descendente para mejor visualización
  const sortedData = [...stageData].sort((a, b) => b.value - a.value);
  
  sortedData.forEach((item, i) => {
    const legendItem = document.createElement('div');
    legendItem.className = 'legend-item';
    legendItem.innerHTML = `
      <div class="legend-label">
        <div class="legend-color" style="background-color: ${item.color || colors[i % colors.length]}"></div>
        <span>${item.name}</span>
      </div>
      <span>${item.value}</span>
    `;
    // Hacer clic en la leyenda también filtra
    legendItem.style.cursor = 'pointer';
    legendItem.addEventListener('click', () => {
      if (activeFilters.stage.includes(item.name)) {
        activeFilters.stage = [];
      } else {
        activeFilters.stage = [item.name];
      }
      updateDashboardUI();
    });
    legendContainer.appendChild(legendItem);
  });
}

// Renderizar tabla de detalle
function renderDetailTable(detailData) {
  const tbody = document.getElementById('detailTableBody');
  tbody.innerHTML = '';
  detailData.forEach((row) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${row.fecha_salida || '-'}</td>
      <td style="color: #6b7280;">${row.fecha_estimada || '-'}</td>
      <td class="text-right">${row.cantidad_kg || '-'}</td>
      <td class="text-right">${row.po_number || '-'}</td>
    `;
    tbody.appendChild(tr);
  });
  // Fila total
  const totalRow = document.createElement('tr');
  totalRow.className = 'total-row';
  totalRow.innerHTML = `
    <td>Total</td>
    <td></td>
    <td class="text-right">${detailData.reduce((acc, row) => acc + parseFloat(row.cantidad_kg || 0), 0).toFixed(2)}</td>
    <td class="text-right">${detailData.length}</td>
  `;
  tbody.appendChild(totalRow);
}

// Función principal para actualizar el dashboard con debouncing
async function updateDashboardUI() {
  // Evitar llamadas múltiples simultáneas
  if (isUpdating) {
    console.log('updateDashboardUI ya está ejecutándose, ignorando llamada duplicada');
    return;
  }
  
  // Cancelar timeout anterior si existe
  if (updateDashboardTimeout) {
    clearTimeout(updateDashboardTimeout);
  }
  
  // Usar debouncing para evitar llamadas muy rápidas
  updateDashboardTimeout = setTimeout(async () => {
    isUpdating = true;
    console.log('=== updateDashboardUI LLAMADA ===');
    console.log('Filtros actuales al momento de la llamada:', JSON.stringify(activeFilters));
    
    try {
      const response = await fetchDashboardData();
      console.log('fetchDashboardData response:', response);
      if (!response) return;
      
      // Soportar ambas rutas posibles para filterOptions
      let filterOptions = null;
      if (response.filterOptions) {
        filterOptions = response.filterOptions;
      } else if (response.data && response.data.filterOptions) {
        filterOptions = response.data.filterOptions;
      }
      if (filterOptions) {
        console.log('Llenando filtros con:', filterOptions);
        populateFilters(filterOptions);
      }
      
      // Indicadores superiores
      const metrics = response.data && response.data.metrics ? response.data.metrics : response.metrics;
      if (metrics) renderTopMetrics(metrics);
      
      // Gráficos - Crear SOLO la primera vez, luego solo actualizar datos
      const charts = response.data && response.data.charts ? response.data.charts : response.charts;
      if (charts) {
        if (!chartsCreated) {
          console.log('Creando gráficos por primera vez');
          createAllChartsOnce(charts);
          chartsCreated = true;
        } else {
          console.log('Actualizando datos de gráficos existentes SIN recrear');
          updateAllChartsData(charts);
        }
      }
      
      // Tabla de detalle
      const detailTable = response.data && response.data.detail_table ? response.data.detail_table : response.detail_table;
      if (detailTable) renderDetailTable(detailTable);
      
    } catch (error) {
      console.error('Error en updateDashboardUI:', error);
    } finally {
      isUpdating = false;
    }
  }, 100); // Debounce de 100ms
}

// Crear todos los gráficos UNA SOLA VEZ
function createAllChartsOnce(charts) {
  // Crear gráfico de hub con handler persistente
  renderHubChart(charts.hub_distribution);
  
  // Crear gráfico de status con handler persistente  
  renderStatusChart(charts.delivery_status);
  
  // Crear otros gráficos
  createPieChart("transportChart", charts.transport_type, "transportLegend", function(evt, elements) {
    if (elements.length > 0) {
      const idx = elements[0].index;
      const transportName = charts.transport_type[idx].name;
      
      // Manejo especial para "SIN ESPECIFICAR"
      if (transportName === "SIN ESPECIFICAR") {
        if (activeFilters.transport.includes("SIN_ESPECIFICAR")) {
          activeFilters.transport = [];
        } else {
          activeFilters.transport = ["SIN_ESPECIFICAR"];
        }
      } else {
        const value = charts.transport_type[idx].values && charts.transport_type[idx].values[0];
        if (activeFilters.transport.includes(value)) {
          activeFilters.transport = [];
        } else {
          activeFilters.transport = [value];
        }
      }
      updateDashboardUI();
    }
  });
  
  createPieChart("delayChart", charts.delay_reasons, "delayLegend", function(evt, elements) {
    if (elements.length > 0) {
      const idx = elements[0].index;
      const motivo = charts.delay_reasons[idx].name;
      if (activeFilters.delay_reason.includes(motivo)) {
        activeFilters.delay_reason = [];
      } else {
        activeFilters.delay_reason = [motivo];
      }
      updateDashboardUI();
    }
  });
  
  createPieChart("stageChart", charts.pos_by_stage, "stageLegend", function(evt, elements) {
    if (elements.length > 0) {
      const idx = elements[0].index;
      const stageName = charts.pos_by_stage[idx].name;
      if (activeFilters.stage.includes(stageName)) {
        activeFilters.stage = [];
      } else {
        activeFilters.stage = [stageName];
      }
      updateDashboardUI();
    }
  });
  
  // Forzar pointer-events:auto en los canvas para asegurar que reciban clicks
  document.getElementById('delayChart').style.pointerEvents = 'auto';
  document.getElementById('stageChart').style.pointerEvents = 'auto';
}

// Actualizar solo datos de gráficos existentes SIN recrear
function updateAllChartsData(charts) {
  // Actualizar hub chart
  if (hubChartInstance && charts.hub_distribution) {
    updateHubChartDataOnly(charts.hub_distribution);
  }
  
  // Actualizar status chart
  if (statusChartInstance && charts.delivery_status) {
    updateStatusChartDataOnly(charts.delivery_status);
  }
  
  // Actualizar otros charts
  if (charts.transport_type) {
    updateChartDataOnly("transportChart", "transportLegend", charts.transport_type);
  }
  if (charts.delay_reasons) {
    updateChartDataOnly("delayChart", "delayLegend", charts.delay_reasons);
  }
  if (charts.pos_by_stage) {
    updateChartDataOnly("stageChart", "stageLegend", charts.pos_by_stage);
  }
}

// Funciones para actualizar SOLO datos sin recrear gráficos
function updateHubChartDataOnly(hubData) {
  if (!hubChartInstance) return;
  
  console.log('Actualizando SOLO datos del gráfico de hub');
  const labels = hubData.map(item => item.name);
  const data = hubData.map(item => item.percentage);
  const ids = hubData.map(item => String(item.id));
  const colors = ["#565aff", "#9aabff", "#ff3459", "#f46844", "#c9cfff", "#5ae7f4", "#5dd595"];
  
  // Aplicar lógica de aclarado
  let backgroundColors = colors.slice(0, data.length);
  if (activeFilters.hub_id && activeFilters.hub_id.length > 0) {
    const activeHubIds = activeFilters.hub_id.map(id => String(id));
    backgroundColors = ids.map((id, idx) => activeHubIds.includes(id) ? colors[idx % colors.length] : colors[idx % colors.length] + '33');
  }
  
  // Actualizar datos del gráfico SIN recrear
  hubChartInstance.data.labels = labels;
  hubChartInstance.data.datasets[0].data = data;
  hubChartInstance.data.datasets[0].backgroundColor = backgroundColors;
  hubChartInstance.update('none'); // Sin animación para ser más rápido
  
  // Actualizar leyenda
  const legendContainer = document.getElementById('hubLegend');
  legendContainer.innerHTML = '';
  hubData.forEach((item, i) => {
    const legendItem = document.createElement('div');
    legendItem.className = 'legend-item';
    legendItem.innerHTML = `
      <div class="legend-label">
        <div class="legend-color" style="background-color: ${colors[i % colors.length]}"></div>
        <span>${item.name}</span>
      </div>
      <span>${item.percentage}%</span>
    `;
    legendContainer.appendChild(legendItem);
  });
}

function updateStatusChartDataOnly(statusData) {
  if (!statusChartInstance) return;
  
  console.log('Actualizando SOLO datos del gráfico de status');
  const labels = statusData.map(item => item.name);
  const data = statusData.map(item => item.percentage);
  const values = statusData.map(item => item.name); // "On Time", "Atrasado" o "Sin datos"
  
  // Colores específicos para cada estado
  const colors = {
    "On Time": "#565aff",
    "Atrasado": "#c9cfff",
    "Sin datos": "#f0f0f0"
  };
  
  // Aplicar lógica de aclarado igual que en hub chart
  const backgroundColors = statusData.map(item => colors[item.name] || "#c9cfff");
  
  if (activeFilters.status && activeFilters.status.length > 0) {
    statusData.forEach((item, idx) => {
      const isSelected = item.values && item.values.some(val => activeFilters.status.includes(val));
      backgroundColors[idx] = isSelected ? colors[item.name] : colors[item.name] + '33';
    });
  }
  
  // Actualizar datos del gráfico SIN recrear
  statusChartInstance.data.labels = labels;
  statusChartInstance.data.datasets[0].data = data;
  statusChartInstance.data.datasets[0].backgroundColor = backgroundColors;
  statusChartInstance.update('none'); // Sin animación
  
  // Actualizar leyenda
  const legendContainer = document.getElementById('statusLegend');
  legendContainer.innerHTML = '';
  statusData.forEach((item) => {
    const legendItem = document.createElement('div');
    legendItem.className = 'legend-item';
    legendItem.innerHTML = `
      <div class="legend-label">
        <div class="legend-color" style="background-color: ${colors[item.name] || "#c9cfff"}"></div>
        <span>${item.name}</span>
      </div>
      <span>${item.percentage}%</span>
    `;
    legendContainer.appendChild(legendItem);
  });
}

function updateChartDataOnly(canvasId, legendId, data) {
  const chart = window[canvasId];
  if (!chart) return;
  
  console.log(`Actualizando SOLO datos del gráfico: ${canvasId}`);
  
  // Aplicar lógica de aclarado basada en el tipo de gráfico
  let backgroundColors = data.map((item) => item.color);
  
  if (canvasId === 'transportChart' && activeFilters.transport && activeFilters.transport.length > 0) {
    backgroundColors = data.map((item) => {
      const isSelected = item.values && item.values.some(val => activeFilters.transport.includes(val));
      return isSelected ? item.color : item.color + '33';
    });
  } else if (canvasId === 'statusChart' && activeFilters.status && activeFilters.status.length > 0) {
    backgroundColors = data.map((item) => {
      const isSelected = item.values && item.values.some(val => activeFilters.status.includes(val));
      return isSelected ? item.color : item.color + '33';
    });
  } else if (canvasId === 'delayChart' && activeFilters.delay_reason && activeFilters.delay_reason.length > 0) {
    backgroundColors = data.map((item) => {
      const isSelected = activeFilters.delay_reason.includes(item.name);
      return isSelected ? item.color : item.color + '33';
    });
  } else if (canvasId === 'stageChart' && activeFilters.stage && activeFilters.stage.length > 0) {
    backgroundColors = data.map((item) => {
      const isSelected = activeFilters.stage.includes(item.name);
      return isSelected ? item.color : item.color + '33';
    });
  }
  
  // Actualizar datos del gráfico SIN recrear
  chart.data.labels = data.map((item) => item.name);
  chart.data.datasets[0].data = data.map((item) => item.value > 0 ? item.value : 0.1); // Usar valor mínimo para visualización
  chart.data.datasets[0].backgroundColor = backgroundColors;
  chart.update('none'); // Sin animación
  
  // Actualizar leyenda
  const legendContainer = document.getElementById(legendId);
  legendContainer.innerHTML = "";
  
  // Ordenar por valor o porcentaje descendente para mejor visualización
  const sortedData = [...data].sort((a, b) => {
    if (typeof a.percentage !== 'undefined' && typeof b.percentage !== 'undefined') {
      return b.percentage - a.percentage;
    }
    return b.value - a.value;
  });
  
  sortedData.forEach((item) => {
    const legendItem = document.createElement("div");
    legendItem.className = "legend-item";
    legendItem.innerHTML = `
      <div class="legend-label">
        <div class="legend-color" style="background-color: ${item.color}"></div>
        <span>${item.name}</span>
      </div>
      <span>${typeof item.percentage !== 'undefined' ? item.percentage + '%' : item.value}</span>
    `;
    
    // Agregar interactividad a las leyendas
    if (canvasId === 'delayChart' || canvasId === 'stageChart') {
      legendItem.style.cursor = 'pointer';
      legendItem.addEventListener('click', () => {
        if (canvasId === 'delayChart') {
          if (activeFilters.delay_reason.includes(item.name)) {
            activeFilters.delay_reason = [];
          } else {
            activeFilters.delay_reason = [item.name];
          }
        } else if (canvasId === 'stageChart') {
          if (activeFilters.stage.includes(item.name)) {
            activeFilters.stage = [];
          } else {
            activeFilters.stage = [item.name];
          }
        }
        updateDashboardUI();
      });
    }
    
    legendContainer.appendChild(legendItem);
  });
}

// Efecto de aclarado en los gráficos
function highlightChartSegment(chartId, selectedIdx) {
  const chart = Chart.getChart(chartId);
  if (!chart) return;
  chart.data.datasets[0].backgroundColor = chart.data.datasets[0].backgroundColor.map((color, idx) => {
    if (selectedIdx === null) return color;
    return idx === selectedIdx ? color : color + '33'; // Añade opacidad a los no seleccionados
  });
  chart.update();
}

// Actualizar todos los multi-selects visualmente según los filtros activos
function syncMultiSelectsWithFilters() {
  // Hub (categoría)
  const categoriaGroup = document.querySelectorAll('.filter-group')[1];
  if (categoriaGroup) {
    const checkboxes = categoriaGroup.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(cb => {
      cb.checked = activeFilters.hub_id.includes(Number(cb.value));
    });
    updateMultiSelectDisplay(categoriaGroup);
  }
  // Vendor (antes Categoría)
  const vendorGroup = document.querySelectorAll('.filter-group')[1];
  if (vendorGroup) {
    const checkboxes = vendorGroup.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(cb => {
      cb.checked = activeFilters.vendor_id.includes(Number(cb.value));
    });
    updateMultiSelectDisplay(vendorGroup);
  }
  // Puedes agregar lógica similar para otros filtros multi-select si los implementas
}

// Actualizar el contador/placeholder de un multi-select
function updateMultiSelectDisplay(multiSelectGroup) {
  const valueSpan = multiSelectGroup.querySelector('.multi-select-value');
  const checked = multiSelectGroup.querySelectorAll('input[type="checkbox"]:checked');
  if (checked.length === 0) {
    valueSpan.textContent = valueSpan.getAttribute('data-placeholder') || 'Seleccionar';
  } else if (checked.length === 1) {
    valueSpan.textContent = checked[0].parentElement.querySelector('label').textContent;
  } else {
    valueSpan.textContent = `${checked.length} seleccionados`;
  }
}

// Función syncChartsWithFilters eliminada - los gráficos se actualizan automáticamente con datos del backend

// MULTISELECT REUTILIZABLE
(function() {
  function closeAllDropdowns(except) {
    document.querySelectorAll('.multi-select-content').forEach(content => {
      if (content !== except) content.classList.remove('active');
    });
    document.querySelectorAll('.multi-select-trigger').forEach(trigger => {
      if (!except || trigger.parentElement.querySelector('.multi-select-content') !== except) {
        trigger.classList.remove('active');
      }
    });
  }

  document.querySelectorAll('[data-multiselect]').forEach(ms => {
    const trigger = ms.querySelector('.multi-select-trigger');
    const content = ms.querySelector('.multi-select-content');
    const valueSpan = ms.querySelector('.multi-select-value');
    const searchInput = ms.querySelector('.multi-select-search-input');
    const optionsBox = ms.querySelector('.multi-select-options');
    const clearBtn = ms.querySelector('.multi-select-clear');
    const checkboxes = optionsBox.querySelectorAll('input[type="checkbox"]');
    const placeholder = ms.getAttribute('data-placeholder') || 'Seleccionar';

    // Abrir/cerrar dropdown
    trigger.addEventListener('click', e => {
      e.stopPropagation();
      const isActive = content.classList.contains('active');
      closeAllDropdowns(content);
      content.classList.toggle('active', !isActive);
      trigger.classList.toggle('active', !isActive);
      if (!isActive && searchInput) {
        searchInput.value = '';
        Array.from(optionsBox.children).forEach(opt => opt.style.display = 'flex');
        searchInput.focus();
      }
    });

    // Selección múltiple
    checkboxes.forEach(cb => {
      cb.addEventListener('change', () => {
        updateDisplay();
      });
    });

    // Búsqueda simple
    if (searchInput) {
      searchInput.addEventListener('input', e => {
        const term = e.target.value.toLowerCase();
        Array.from(optionsBox.children).forEach(opt => {
          const label = opt.textContent.toLowerCase();
          opt.style.display = label.includes(term) ? 'flex' : 'none';
        });
      });
      
      // Limpiar búsqueda con Escape
      searchInput.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
          searchInput.value = '';
          Array.from(optionsBox.children).forEach(opt => opt.style.display = 'flex');
        }
      });
    }

    // Limpiar selección
    clearBtn.addEventListener('click', e => {
      e.stopPropagation();
      console.log('Limpiando selección en', ms.getAttribute('data-filter'));
      checkboxes.forEach(cb => cb.checked = false);
      selectAll.querySelector('input').checked = false;
      if (searchInput) {
        searchInput.value = '';
        Array.from(optionsBox.children).forEach(opt => opt.style.display = 'flex');
      }
      updateDisplay();
      
      // Ya no cerramos el dropdown para mantenerlo abierto
      // content.classList.remove('active');
      // trigger.classList.remove('active');
      
      // Actualizar los activeFilters según el tipo de filtro
      const filterType = ms.getAttribute('data-filter');
      if (filterType === 'product') {
        activeFilters.product_id = [];
        console.log('Limpiado activeFilters.product_id:', activeFilters.product_id);
        updateDashboardUI();
      } else if (filterType === 'vendor') {
        activeFilters.vendor_id = [];
        console.log('Limpiado activeFilters.vendor_id:', activeFilters.vendor_id);
        updateDashboardUI();
      } else if (filterType === 'material') {
        activeFilters.material_type = [];
        console.log('Limpiado activeFilters.material_type:', activeFilters.material_type);
        updateDashboardUI();
      }
    });

    // Cerrar al hacer click fuera
    document.addEventListener('click', e => {
      if (!ms.contains(e.target)) {
        content.classList.remove('active');
        trigger.classList.remove('active');
      }
    });

    function updateDisplay() {
      const selected = Array.from(checkboxes).filter(cb => cb.checked);
      if (selected.length === 0) {
        valueSpan.textContent = placeholder;
      } else if (selected.length === 1) {
        valueSpan.textContent = selected[0].parentElement.textContent.trim();
      } else {
        valueSpan.textContent = `${selected.length} seleccionados`;
      }
    }
    updateDisplay();
  });
})();

// Modificar la lógica de los gráficos para que usen la función centralizada
function setupChartClickFilters() {
  const chartConfigs = [
    { chartId: "hubChart", filterType: "hub_id", options: [1, 2, 3, 4] },
    // ...otros gráficos
  ];
  chartConfigs.forEach(({ chartId, filterType, options }) => {
    const canvas = document.getElementById(chartId);
    if (!canvas) return;
    canvas.onclick = function (evt) {
      const chart = Chart.getChart(chartId);
      if (!chart) return;
      const points = chart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
      if (points.length) {
        const idx = points[0].index;
        const value = options[idx];
        if (activeFilters[filterType].includes(value)) {
          activeFilters[filterType] = activeFilters[filterType].filter(v => v !== value);
        } else {
          activeFilters[filterType].push(value);
        }
        updateDashboardUI();
      }
    };
  });
}

// Tabla de detalle
function populateDetailTable() {
  const tbody = document.getElementById("detailTableBody");
  tbody.innerHTML = "";
  detailData.forEach((row) => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${row.po_number}</td>
      <td style="color: #6b7280;">${row.fecha_estimada}</td>
      <td class="text-right">${row.cantidad_kg}</td>
      <td class="text-right">${row.hub_id}</td>
    `;
    tbody.appendChild(tr);
  });
  // Fila total
  const totalRow = document.createElement("tr");
  totalRow.className = "total-row";
  totalRow.innerHTML = `
    <td>Total</td>
    <td></td>
    <td class="text-right">${detailData.reduce((acc, row) => acc + parseFloat(row.cantidad_kg), 0).toFixed(2)}</td>
    <td class="text-right">${detailData.length}</td>
  `;
  tbody.appendChild(totalRow);
}

// Modales
function showModal(modalId) {
  const modal = document.getElementById(modalId);
  modal.style.display = "flex";
  document.body.style.overflow = "hidden";
}
function hideModal(modalId) {
  const modal = document.getElementById(modalId);
  modal.style.display = "none";
  document.body.style.overflow = "auto";
}

// --- INTEGRACIÓN CLICK EN GRÁFICOS Y MULTISELECTS ---
function selectMultiSelectOption(filterType, value) {
  let key;
  if (filterType === 'categoría') key = 'hub_id';
  else if (filterType === 'producto') key = 'product_id';
  else if (filterType === 'material') key = 'material_type';
  else if (filterType === 'status') key = 'status';
  else if (filterType === 'transport') key = 'transport';
  else if (filterType === 'delay_reason') key = 'delay_reason';
  else if (filterType === 'stage') key = 'stage';
  else return;
  if (!activeFilters[key].includes(value)) {
    activeFilters[key].push(value);
  }
  console.log('activeFilters.transport:', activeFilters.transport);
  updateDashboardUI();
}

// Función para limpiar todos los filtros
function clearAllFilters() {
  activeFilters.hub_id = [];
  activeFilters.status = [];
  activeFilters.product_id = [];
  activeFilters.material_type = [];
  activeFilters.transport = [];
  activeFilters.delay_reason = [];
  activeFilters.stage = [];
  activeFilters.date_from = null;
  activeFilters.date_to = null;
  activeFilters.vendor_id = [];
  updateDashboardUI();
  updateClearFiltersButton();
}

// Código de inicialización obsoleto eliminado - los gráficos se renderizan con datos reales del backend

function updateClearFiltersButton() {
  const clearBtn = document.getElementById("clearFiltersBtn");
  if (!clearBtn) return;
  const anyActive = (
    activeFilters.hub_id.length > 0 ||
    activeFilters.status.length > 0 ||
    activeFilters.product_id.length > 0 ||
    activeFilters.material_type.length > 0 ||
    activeFilters.transport.length > 0 ||
    activeFilters.delay_reason.length > 0 ||
    activeFilters.stage.length > 0 ||
    activeFilters.date_from ||
    activeFilters.date_to ||
    activeFilters.vendor_id.length > 0
  );
  clearBtn.style.display = anyActive ? "block" : "none";
}

// --- INICIO: Conexión de multifiltros superiores ---
function applyTopFilters() {
  // Fecha
  const startDate = document.getElementById('startDate').value;
  const endDate = document.getElementById('endDate').value;
  activeFilters.date_from = startDate || null;
  activeFilters.date_to = endDate || null;

  // Vendor (filtro múltiple)
  const vendorGroup = document.querySelector('.filter-group[data-filter="vendor"]');
  if (vendorGroup) {
    const checked = vendorGroup.querySelectorAll('input[type="checkbox"]:checked');
    activeFilters.vendor_id = Array.from(checked).map(cb => cb.value);
  }

  // Producto (filtro múltiple)
  const productoGroup = document.querySelector('.filter-group[data-filter="product"]');
  if (productoGroup) {
    const checked = productoGroup.querySelectorAll('input[type="checkbox"]:checked');
    activeFilters.product_id = Array.from(checked).map(cb => cb.value);
  }

  // Material (filtro múltiple)
  const materialGroup = document.querySelector('.filter-group[data-filter="material"]');
  if (materialGroup) {
    const checked = materialGroup.querySelectorAll('input[type="checkbox"]:checked');
    activeFilters.material_type = Array.from(checked).map(cb => cb.value);
  }
}

// Inicialización principal del dashboard
document.addEventListener('DOMContentLoaded', () => {
  // Configurar botón Aceptar
  const aceptarBtn = document.querySelector('.action-buttons .btn-primary');
  if (aceptarBtn) {
    aceptarBtn.addEventListener('click', () => {
      applyTopFilters();
      updateDashboardUI();
    });
  }
  
  // Configurar modales
  const closeSuccessBtn = document.getElementById("closeSuccessBtn");
  const closeErrorBtn = document.getElementById("closeErrorBtn");
  if (closeSuccessBtn) closeSuccessBtn.addEventListener("click", () => hideModal("successModal"));
  if (closeErrorBtn) closeErrorBtn.addEventListener("click", () => hideModal("errorModal"));
  document.addEventListener("keydown", (e) => { 
    if (e.key === "Escape") { 
      hideModal("successModal"); 
      hideModal("errorModal"); 
    } 
  });
  
  // Configurar botón borrar filtros
  const clearBtn = document.getElementById("clearFiltersBtn");
  if (clearBtn) {
    clearBtn.addEventListener("click", clearAllFilters);
  }
  
  // Configurar inputs de fecha
  const startDateInput = document.getElementById('startDate');
  const endDateInput = document.getElementById('endDate');
  if (startDateInput) {
    startDateInput.type = 'date';
    startDateInput.addEventListener('change', () => {
      activeFilters.date_from = startDateInput.value || null;
      updateDashboardUI();
    });
  }
  if (endDateInput) {
    endDateInput.type = 'date';
    endDateInput.addEventListener('change', () => {
      activeFilters.date_to = endDateInput.value || null;
      updateDashboardUI();
    });
  }
  
  // Cargar datos iniciales del dashboard
  updateDashboardUI();
});
// --- FIN: Conexión de multifiltros superiores ---

// --- INICIO: Poblar filtros dinámicamente y eliminar valores mock ---
function populateFilters(filterOptions) {
  console.log('populateFilters called', filterOptions);
  
  // Función helper para crear un multi-select con más espacio
  function createEnhancedMultiSelect(container, items, valueKey, labelKey) {
    const optionsBox = container.querySelector('.multi-select-options');
    const clearBtn = container.querySelector('.multi-select-clear');
    const searchInput = container.querySelector('.multi-select-search-input');
    const trigger = container.querySelector('.multi-select-trigger');
    const content = container.querySelector('.multi-select-content');
    const valueSpan = container.querySelector('.multi-select-value');
    const placeholder = container.getAttribute('data-placeholder') || 'Seleccionar';
    
    // Limpiar opciones existentes
    while (optionsBox.firstChild) optionsBox.removeChild(optionsBox.firstChild);
    
    // Seleccionar todos (al inicio)
    const selectAll = document.createElement('div');
    selectAll.className = 'multi-select-option';
    selectAll.style.fontWeight = 'bold';
    selectAll.style.borderBottom = '2px solid #e0e0e0';
    selectAll.innerHTML = `<label><input type="checkbox" class="select-all"> Seleccionar todos</label>`;
    optionsBox.appendChild(selectAll);
    
    // Agregar elementos individuales
    items.forEach(item => {
      const label = document.createElement('label');
      label.className = 'multi-select-option';
      const value = valueKey ? item[valueKey] : item;
      const text = labelKey ? item[labelKey] : item;
      label.innerHTML = `<input type="checkbox" value="${value}"> ${text}`;
      optionsBox.appendChild(label);
    });
    
    // Obtener todos los checkboxes después de crearlos
    const checkboxes = optionsBox.querySelectorAll('input[type="checkbox"]:not(.select-all)');
    
    // Event listener para "Seleccionar todos"
    selectAll.querySelector('input').addEventListener('change', function() {
      checkboxes.forEach(cb => cb.checked = this.checked);
      updateDisplay();
    });
    
    // Event listener para cada checkbox
    checkboxes.forEach(cb => {
      cb.addEventListener('change', () => {
        updateDisplay();
      });
    });
    
    // Event listener para el botón "Limpiar selección"
    if (clearBtn) {
      // Eliminar event listeners anteriores para evitar duplicados
      const newClearBtn = clearBtn.cloneNode(true);
      clearBtn.parentNode.replaceChild(newClearBtn, clearBtn);
      
      // Agregar nuevo event listener
      newClearBtn.addEventListener('click', e => {
        e.stopPropagation();
        console.log('Limpiando selección en', container.getAttribute('data-filter'));
        checkboxes.forEach(cb => cb.checked = false);
        selectAll.querySelector('input').checked = false;
        if (searchInput) {
          searchInput.value = '';
          Array.from(optionsBox.children).forEach(opt => opt.style.display = 'flex');
        }
        updateDisplay();
        
        // Ya no cerramos el dropdown para mantenerlo abierto
        // content.classList.remove('active');
        // trigger.classList.remove('active');
        
        // Actualizar los activeFilters según el tipo de filtro
        const filterType = container.getAttribute('data-filter');
        if (filterType === 'product') {
          activeFilters.product_id = [];
          console.log('Limpiado activeFilters.product_id:', activeFilters.product_id);
          updateDashboardUI();
        } else if (filterType === 'vendor') {
          activeFilters.vendor_id = [];
          console.log('Limpiado activeFilters.vendor_id:', activeFilters.vendor_id);
          updateDashboardUI();
        } else if (filterType === 'material') {
          activeFilters.material_type = [];
          console.log('Limpiado activeFilters.material_type:', activeFilters.material_type);
          updateDashboardUI();
        }
      });
    }
    
    // Función para actualizar la visualización
    function updateDisplay() {
      const selected = Array.from(checkboxes).filter(cb => cb.checked);
      if (selected.length === 0) {
        valueSpan.textContent = placeholder;
      } else if (selected.length === 1) {
        valueSpan.textContent = selected[0].parentElement.textContent.trim();
      } else {
        valueSpan.textContent = `${selected.length} seleccionados`;
      }
    }
    
    // Inicializar la visualización
    updateDisplay();
  }
  
  // Productos
  const productoGroup = document.querySelector('.filter-group[data-filter="product"]');
  if (productoGroup && filterOptions.products) {
    createEnhancedMultiSelect(productoGroup, filterOptions.products, 'id', 'name');
  }
  // Materiales
  const materialGroup = document.querySelector('.filter-group[data-filter="material"]');
  if (materialGroup && filterOptions.materials) {
    let materials = filterOptions.materials;
    if (materials && !Array.isArray(materials)) {
      materials = Object.values(materials);
    }
    if (materials && Array.isArray(materials)) {
      createEnhancedMultiSelect(materialGroup, materials, null, null);
    }
  }
  
  // Vendor
  const vendorGroup = document.querySelector('.filter-group[data-filter="vendor"]');
  if (vendorGroup && filterOptions.vendors) {
    createEnhancedMultiSelect(vendorGroup, filterOptions.vendors, 'id', 'name');
  }
}

// Esta función duplicada fue eliminada - usar solo la de la línea 486
// --- FIN: Poblar filtros dinámicamente y eliminar valores mock ---


