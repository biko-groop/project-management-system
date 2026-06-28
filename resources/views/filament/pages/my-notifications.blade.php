<x-filament-panels::page>
    @php $items = $this->getMine(); $unread = $items->where('is_read', false)->count(); @endphp

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
        <div style="color:#64748b;">لديك {{ $unread }} إشعاراً غير مقروء</div>
        @if ($unread > 0)
            <x-filament::button wire:click="markAllRead" icon="heroicon-o-check" color="gray">
                تحديد الكل كمقروء
            </x-filament::button>
        @endif
    </div>

    <div style="display:flex;flex-direction:column;gap:.5rem;">
        @forelse ($items as $n)
            <div wire:key="notif-{{ $n->id }}"
                 style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;
                        background:var(--fi-color-white,#fff);border:1px solid rgba(0,0,0,.08);
                        border-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}:4px solid {{ $n->is_read ? 'transparent' : 'rgb(var(--primary-500))' }};
                        border-radius:.6rem;padding:.75rem 1rem;">
                <div>
                    <div style="font-weight:700;margin-bottom:.2rem;">{{ $n->title }}</div>
                    <div style="color:#475569;">{{ $n->message }}</div>
                    <div style="color:#94a3b8;font-size:.75rem;margin-top:.3rem;">
                        <x-heroicon-o-clock style="width:.9rem;height:.9rem;display:inline;" />
                        {{ $n->created_at?->diffForHumans() }}
                    </div>
                </div>
                @unless ($n->is_read)
                    <button wire:click="markRead({{ $n->id }})"
                            style="flex:none;font-size:.75rem;padding:.25rem .6rem;border:1px solid rgba(0,0,0,.12);border-radius:.4rem;cursor:pointer;">
                        تحديد كمقروء
                    </button>
                @endunless
            </div>
        @empty
            <div style="text-align:center;padding:3rem;color:#94a3b8;">
                <x-heroicon-o-bell-slash style="width:3rem;height:3rem;display:inline;" />
                <div style="margin-top:.5rem;">لا توجد إشعارات</div>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
