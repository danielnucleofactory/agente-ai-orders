<?php

namespace App\Http\Controllers;

use App\Models\AuthorizationRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderComment;
use App\Services\AuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AuthorizationController extends Controller
{
    protected $authorizationService;

    public function __construct(AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    /**
     * Display a listing of pending authorization requests.
     */
    public function index()
    {
        $pendingRequests = $this->authorizationService->getPendingRequests();
        return view('authorization.index', compact('pendingRequests'));
    }

    /**
     * Approve an authorization request for a PurchaseOrder.
     */
    public function approve(AuthorizationRequest $request, Request $httpRequest)
    {
        Log::info('AuthorizationController@approve siendo ejecutada', [
            'request_id' => $request->id,
            'authorizable_type' => $request->authorizable_type,
            'authorizable_id' => $request->authorizable_id,
            'operation_type' => $request->operation_type
        ]);

        $notes = $httpRequest->input('notes');

        // Guardar los datos originales antes de cambiar el estado
        $originalData = $request->data;
        $operationType = $request->operation_type;
        $purchaseOrder = null;

        if ($request->authorizable_type === PurchaseOrder::class) {
            $purchaseOrder = $request->authorizable;
        }

        // Aprobar la solicitud
        $this->authorizationService->approve($request, $notes);

        // Según el tipo de operación, realizar la acción correspondiente
        if ($request->authorizable_type === PurchaseOrder::class && $purchaseOrder) {
            if ($operationType === 'upload_file') {
                // En caso de aprobación, mostramos instrucciones para que el usuario
                // vuelva a subir el archivo ya que no se guarda temporalmente
                Session::flash('file_upload_approved', true);
                Session::flash('purchase_order_id', $purchaseOrder->id);
                // Incluir los datos originales para que se usen al subir el archivo
                Session::flash('approved_file_data', $originalData);

                return redirect()->route('purchase-orders.detail', ['id' => $purchaseOrder->id])
                    ->with('message', 'Solicitud de subida de archivo aprobada. Por favor, suba el archivo nuevamente.');
            }
            elseif ($operationType === 'attach_file_to_comment') {
                // El servicio de autorización ya maneja el movimiento del archivo
                // desde la colección 'pending_attachments' a 'attachments'
                return redirect()->route('purchase-orders.detail', ['id' => $purchaseOrder->id])
                    ->with('message', 'Archivo adjunto aprobado correctamente.');
            }

            return redirect()->route('purchase-orders.detail', ['id' => $purchaseOrder->id])
                ->with('message', 'Solicitud aprobada correctamente.');
        }

        return redirect()->back()
            ->with('message', 'Solicitud aprobada correctamente.');
    }

    /**
     * Reject an authorization request.
     */
    public function reject(AuthorizationRequest $request, Request $httpRequest)
    {
        $notes = $httpRequest->input('notes');

        // Rechazar la solicitud
        $this->authorizationService->reject($request, $notes);

        // Si es una solicitud de PurchaseOrder, redireccionar a la página de detalle
        if ($request->authorizable_type === PurchaseOrder::class) {
            $purchaseOrder = $request->authorizable;
            return redirect()->route('purchase-orders.detail', ['id' => $purchaseOrder->id])
                ->with('message', 'Solicitud rechazada correctamente.');
        }

        return redirect()->back()
            ->with('message', 'Solicitud rechazada correctamente.');
    }

    /**
     * Show the details of an authorization request.
     */
    public function show(AuthorizationRequest $request)
    {
        return view('authorization.show', compact('request'));
    }
}
