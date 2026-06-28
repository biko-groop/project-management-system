<?php

namespace App\Filament\Pages;

use App\Models\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Pages\Page;

class MyNotifications extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $title = 'إشعاراتي';

    protected static string $view = 'filament.pages.my-notifications';

    // تُفتح عبر الجرس — مخفية من القائمة الجانبية
    protected static bool $shouldRegisterNavigation = false;

    public function getMine()
    {
        return Notification::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
    }

    public function markRead(int $id): void
    {
        Notification::where('id', $id)->where('user_id', auth()->id())->update(['is_read' => true]);
    }

    public function markAllRead(): void
    {
        Notification::where('user_id', auth()->id())->where('is_read', false)->update(['is_read' => true]);

        FilamentNotification::make()->title('تم تحديد الكل كمقروء')->success()->send();
    }
}
