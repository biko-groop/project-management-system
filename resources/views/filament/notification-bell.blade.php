@php
    $count = \App\Models\Notification::where('user_id', auth()->id())->where('is_read', false)->count();
    $url = \App\Filament\Pages\MyNotifications::getUrl();
@endphp

<a href="{{ $url }}" title="إشعاراتي"
   style="position:relative;display:inline-flex;align-items:center;justify-content:center;
          width:2.25rem;height:2.25rem;border-radius:.6rem;color:inherit;text-decoration:none;">
    <x-heroicon-o-bell style="width:1.4rem;height:1.4rem;" />
    @if ($count > 0)
        <span style="position:absolute;top:-2px;{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}:-2px;
                     min-width:1.1rem;height:1.1rem;padding:0 .25rem;border-radius:99px;
                     background:#ef4444;color:#fff;font-size:.7rem;font-weight:700;
                     display:inline-flex;align-items:center;justify-content:center;">
            {{ $count > 99 ? '99+' : $count }}
        </span>
    @endif
</a>
