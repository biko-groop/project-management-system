<x-filament-panels::page>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales-all.global.min.js"></script>

    <div style="background:var(--fi-color-white,#fff);border-radius:.75rem;padding:1rem;box-shadow:0 1px 3px rgba(0,0,0,.08);">
        <div id="tasks-calendar"
             x-data
             x-init="
                (function () {
                    const init = function () {
                        if (typeof FullCalendar === 'undefined') { setTimeout(init, 100); return; }
                        if ($el.__inited) return; $el.__inited = true;
                        const calendar = new FullCalendar.Calendar($el, {
                            initialView: 'dayGridMonth',
                            locale: 'ar',
                            direction: 'rtl',
                            height: 'auto',
                            headerToolbar: { start: 'title', center: '', end: 'today prev,next' },
                            events: @json($this->getEvents()),
                            eventClick: function (info) {
                                if (info.event.url) { info.jsEvent.preventDefault(); window.location.href = info.event.url; }
                            },
                        });
                        calendar.render();
                    };
                    init();
                })()
             ">
        </div>
    </div>

    {{-- مفتاح الألوان --}}
    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:1rem;font-size:.8rem;">
        <span><span style="display:inline-block;width:.7rem;height:.7rem;border-radius:99px;background:#f59e0b;"></span> قيد الانتظار</span>
        <span><span style="display:inline-block;width:.7rem;height:.7rem;border-radius:99px;background:#3b82f6;"></span> قيد التنفيذ</span>
        <span><span style="display:inline-block;width:.7rem;height:.7rem;border-radius:99px;background:#22c55e;"></span> مكتمل</span>
        <span><span style="display:inline-block;width:.7rem;height:.7rem;border-radius:99px;background:#ef4444;"></span> ملغى</span>
    </div>
</x-filament-panels::page>
