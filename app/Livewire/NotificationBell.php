<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

final class NotificationBell extends Component
{
    /**
     * @return Collection<int, DatabaseNotification>
     */
    public function getNotifications(): Collection
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Collection<int, DatabaseNotification> $notifications */
        $notifications = $user->unreadNotifications()->latest()->limit(10)->get();

        return $notifications;
    }

    public function getUnreadCount(): int
    {
        /** @var User $user */
        $user = Auth::user();

        return (int) $user->unreadNotifications()->count();
    }

    public function markAsRead(string $notificationId): void
    {
        /** @var User $user */
        $user = Auth::user();

        $notification = $user->notifications()->find($notificationId);

        if ($notification instanceof DatabaseNotification) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $user->unreadNotifications()->update(['read_at' => now()]);
    }

    public function render(): View
    {
        return view('livewire.notification-bell', [
            'notifications' => $this->getNotifications(),
            'unreadCount' => $this->getUnreadCount(),
        ]);
    }
}
