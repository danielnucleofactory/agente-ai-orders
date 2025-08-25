<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use App\Models\Notification;

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

    public function render()
    {
        return view('livewire.ui.notifications-dropdown');
    }
}
