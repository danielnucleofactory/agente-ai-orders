<?php

namespace App\Http\Controllers;

use App\Models\HistoricalPurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HistoricalDataController extends Controller
{
    /**
     * Export historical data to CSV/Excel
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
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

            $filename = 'historico_datos_' . now()->format('Y-m-d_H-i-s') . '.csv';

            return response()->streamDownload(function () use ($data) {
                $handle = fopen('php://output', 'w');

                // Add BOM for proper UTF-8 encoding in Excel
                fwrite($handle, "\xEF\xBB\xBF");

                // Headers
                fputcsv($handle, [
                    'Orden',
                    'Proveedor',
                    'Fecha Emisión',
                    'Total Neto',
                    'Moneda',
                    'Contenedor',
                    'ETD',
                    'ETA',
                    'Empresa'
                ], ';');

                // Data rows
                foreach ($data as $record) {
                    fputcsv($handle, [
                        $record->order_number ?? '',
                        $record->vendor_name ?? '',
                        $record->emision_date_po ? $record->emision_date_po->format('Y-m-d') : 'N/A',
                        $record->net_total ? number_format($record->net_total, 2, '.', '') : '',
                        $record->currency ?? '',
                        $record->container_number ?? 'N/A',
                        $record->date_etd ? $record->date_etd->format('Y-m-d') : 'N/A',
                        $record->date_eta ? $record->date_eta->format('Y-m-d') : 'N/A',
                        $record->trading_company ?? 'N/A'
                    ], ';');
                }

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
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
