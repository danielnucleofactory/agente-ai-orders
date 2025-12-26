<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use App\Models\Notification;
use App\Models\PurchaseOrder;
use App\Models\Vendor;

class NotificationsDropdown extends Component {
    public $notifications = [];
    public $unreadCount = 0;

    protected $listeners = [
        'notificationsUpdated' => 'loadNotifications',
        'refresh-notifications' => 'loadNotifications',
        'notification-received' => 'loadNotifications'
    ];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        \Log::info('Loading notifications for user', ['user_id' => auth()->id()]);

        $this->notifications = Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $this->unreadCount = Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        \Log::info('Notifications loaded', [
            'count' => count($this->notifications),
            'unread' => $this->unreadCount
        ]);
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);

        if ($notification && $notification->user_id === auth()->id()) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->loadNotifications();
    }

    /**
     * Formatear el mensaje de notificación convirtiendo números de PO en hipervínculos
     *
     * @param Notification $notification
     * @return string
     */
    public function formatNotificationMessage($notification)
    {
        $message = $notification->message;
        $data = $notification->data ?? [];
        
        // Intentar obtener el order_id del campo data
        $orderId = $data['order_id'] ?? $data['task_id'] ?? null;
        $orderNumber = $data['order_number'] ?? $data['po_number'] ?? null;
        
        // Si tenemos el order_id pero no el order_number, obtenerlo de la base de datos
        if ($orderId && !$orderNumber) {
            $purchaseOrder = PurchaseOrder::find($orderId);
            if ($purchaseOrder) {
                $orderNumber = $purchaseOrder->order_number;
            }
        }
        
        // Si no tenemos el order_id, intentar extraer el número de PO del mensaje
        if (!$orderId && !$orderNumber) {
            // Buscar el patrón "La orden de compra {número}" en el mensaje
            if (preg_match('/La orden de compra\s+([^\s]+)/i', $message, $matches)) {
                $orderNumber = $matches[1];
            }
        }
        
        // Si tenemos el order_number pero no el order_id, buscar en la base de datos
        if ($orderNumber && !$orderId) {
            $purchaseOrder = PurchaseOrder::where('order_number', $orderNumber)->first();
            if ($purchaseOrder) {
                $orderId = $purchaseOrder->id;
            }
        }
        
        // Si tenemos el order_id y el order_number, crear el enlace
        if ($orderId && $orderNumber) {
            $url = route('purchase-orders.detail', $orderId);
            
            // Crear el enlace HTML con el número de PO escapado
            $escapedOrderNumber = e($orderNumber);
            $link = '<a href="' . e($url) . '" class="text-blue-600 hover:text-blue-800 underline font-semibold">' . $escapedOrderNumber . '</a>';
            
            // Reemplazar el número de PO en el mensaje con el enlace
            // Usar un marcador temporal para evitar problemas con caracteres especiales
            $placeholder = '___PO_LINK_PLACEHOLDER___';
            $messageWithPlaceholder = str_replace($orderNumber, $placeholder, $message);
            
            // Escapar el mensaje completo (el placeholder no se escapará porque no contiene caracteres especiales)
            $escapedMessage = e($messageWithPlaceholder);
            
            // Reemplazar el placeholder con el enlace HTML
            $formattedMessage = str_replace($placeholder, $link, $escapedMessage);
            
            return $formattedMessage;
        }
        
        // Manejar enlaces a vendors (proveedores)
        $vendorId = $data['vendor_id'] ?? null;
        $vendorName = $data['vendor_name'] ?? null;
        
        if ($vendorId && $vendorName && $notification->type === 'vendor_created') {
            $url = route('vendors.edit', $vendorId);
            
            // Crear el enlace HTML con el nombre del vendor escapado
            $escapedVendorName = e($vendorName);
            $link = '<a href="' . e($url) . '" class="text-blue-600 hover:text-blue-800 underline font-semibold">' . $escapedVendorName . '</a>';
            
            // Reemplazar el nombre del vendor en el mensaje con el enlace
            $placeholder = '___VENDOR_LINK_PLACEHOLDER___';
            $messageWithPlaceholder = str_replace($vendorName, $placeholder, $message);
            
            // Escapar el mensaje completo
            $escapedMessage = e($messageWithPlaceholder);
            
            // Reemplazar el placeholder con el enlace HTML
            $formattedMessage = str_replace($placeholder, $link, $escapedMessage);
            
            return $formattedMessage;
        }
        
        // Si no se puede crear el enlace, retornar el mensaje escapado
        return e($message);
    }

    public function render()
    {
        return view('livewire.ui.notifications-dropdown');
    }
}
