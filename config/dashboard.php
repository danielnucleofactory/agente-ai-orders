<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Caché del KPI «POs en transbordo» (segundos)
    |--------------------------------------------------------------------------
    |
    | 0 = sin caché. Reduce tiempo de respuesta en recargas y pestañas del
    | dashboard que vuelven a llamar /dashboard-kpi/api/transshipment.
    |
    */
    'kpi_transshipment_cache_seconds' => (int) env('DASHBOARD_KPI_TRANSSHIPMENT_CACHE_SECONDS', 120),

    /*
    |--------------------------------------------------------------------------
    | Tamaño de chunk al iterar POs (lazy)
    |--------------------------------------------------------------------------
    */
    'kpi_transshipment_lazy_chunk' => (int) env('DASHBOARD_KPI_TRANSSHIPMENT_LAZY_CHUNK', 400),

];
