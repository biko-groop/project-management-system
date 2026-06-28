@php
    $nameColor = $dark ? '#ffffff' : 'rgb(var(--primary-700))';
@endphp

<div style="display:flex;align-items:center;gap:.6rem;">
    @if ($logo)
        <img src="{{ asset('storage/' . $logo) }}"
             alt="{{ $name }}"
             style="height:2.4rem;width:auto;border-radius:.5rem;object-fit:contain;">
    @else
        <span style="display:inline-flex;align-items:center;justify-content:center;
                     width:2.4rem;height:2.4rem;border-radius:.65rem;
                     background:rgb(var(--primary-600));color:#fff;
                     box-shadow:0 2px 6px rgba(0,0,0,.18);flex:none;">
            <x-heroicon-s-rectangle-stack style="width:1.4rem;height:1.4rem;" />
        </span>
    @endif

    <span style="font-weight:800;font-size:1.1rem;line-height:1.2;
                 letter-spacing:-.01em;color:{{ $nameColor }};white-space:nowrap;">
        {{ $name }}
    </span>
</div>
