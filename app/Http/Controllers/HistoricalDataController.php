<?php

namespace App\Http\Controllers;

use App\Exports\HistoricalPurchaseOrdersExport;
use App\Models\HistoricalPurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class HistoricalDataController extends Controller
{
    /**
     * Export historical data to Excel (.xlsx).
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        try {
            Log::info('HistoricalDataController::export starting', [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            $query = HistoricalPurchaseOrder::query();

            // Aplicar búsqueda (case-insensitive)
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = '%' . strtolower(trim($request->search)) . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->whereRaw('LOWER(order_number) LIKE ?', [$searchTerm])
                      ->orWhereRaw('LOWER(vendor_name) LIKE ?', [$searchTerm])
                      ->orWhereRaw('LOWER(container_number) LIKE ?', [$searchTerm])
                      ->orWhereRaw('LOWER(mbl_number) LIKE ?', [$searchTerm]);
                });
            }

            // Aplicar filtros
            if ($request->has('vendor') && !empty($request->vendor)) {
                $query->where('vendor_id', $request->vendor);
            }

            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->where('emision_date_po', '>=', $request->date_from);
            }

            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->where('emision_date_po', '<=', $request->date_to);
            }

            if ($request->has('trading_company') && !empty($request->trading_company)) {
                $query->where('trading_company', $request->trading_company);
            }

            // Obtener todos los datos sin paginación
            $data = $query->orderBy('emision_date_po', 'desc')
                         ->orderBy('id', 'desc')
                         ->get();

            Log::info('HistoricalDataController::export data retrieved', [
                'rows_count' => $data->count()
            ]);

            $filename = 'historico_datos_' . now(config('app.timezone'))->format('Y-m-d_H-i-s') . '.xlsx';

            return Excel::download(new HistoricalPurchaseOrdersExport($data), $filename);
        } catch (\Exception $e) {
            Log::error('Error exporting historical data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => $request->all(),
                'user_id' => auth()->id(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->streamDownload(function () use ($e) {
                echo "Error al exportar los datos: " . $e->getMessage();
            }, 'error.txt');
        }
    }
}
