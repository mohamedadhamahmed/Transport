{{-- اسم السائق + رقمه + زرار اتصال + واتساب.
     @include('transport.partials.driver-contact', ['driver' => $driver, 'msg' => 'نص اختياري للواتساب', 'compact' => false]) --}}
@php $msg = $msg ?? ''; $compact = $compact ?? false; @endphp
@if ($driver)
    <div class="dc-wrap {{ $compact ? 'dc-compact' : '' }}">
        <div class="dc-info">
            <span class="dc-name">👤 {{ $driver->name }}</span>
            @if ($driver->hasValidPhone())
                <a class="dc-phone" href="{{ $driver->telUrl() }}" dir="ltr">{{ $driver->phone }}</a>
            @else
                <span class="dc-nophone">{{ __('transport.no_phone_warning') }}</span>
            @endif
        </div>
        @if ($driver->hasValidPhone())
            <div class="dc-actions">
                <a href="{{ $driver->telUrl() }}" class="dc-btn dc-call" title="{{ __('transport.call') }}">📞 <span>{{ __('transport.call') }}</span></a>
                <a href="{{ $driver->whatsappUrl($msg) }}" target="_blank" rel="noopener" class="dc-btn dc-wa" title="WhatsApp">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91C21.95 6.45 17.5 2 12.04 2Zm5.8 14.02c-.24.68-1.42 1.3-1.96 1.35-.5.05-1.13.07-1.82-.11-.42-.13-.96-.31-1.65-.61-2.9-1.25-4.8-4.18-4.94-4.37-.14-.19-1.18-1.57-1.18-3 0-1.43.75-2.13 1.02-2.42.27-.29.58-.36.78-.36h.56c.18 0 .42-.07.66.5.24.58.82 2.01.89 2.16.07.14.12.31.02.5-.09.19-.14.31-.28.48-.14.17-.3.37-.43.5-.14.14-.29.3-.12.58.17.29.74 1.22 1.59 1.98 1.09.97 2.01 1.27 2.3 1.41.29.14.46.12.63-.07.17-.19.72-.84.91-1.13.19-.29.38-.24.65-.14.26.1 1.68.79 1.97.94.29.14.48.22.55.34.07.12.07.7-.17 1.38Z"/></svg>
                    <span>واتساب</span>
                </a>
            </div>
        @endif
    </div>
@else
    <span class="text-gray-400 text-xs">{{ __('transport.no_driver') }}</span>
@endif

@once
<style>
    .dc-wrap { display:flex; align-items:center; justify-content:space-between; gap:.5rem; flex-wrap:wrap; }
    .dc-info { display:flex; flex-direction:column; min-width:0; }
    .dc-name { font-size:.8rem; color:#374151; font-weight:600; }
    .dc-phone { font-size:1rem; font-weight:800; color:#0F1B4C; text-decoration:none; letter-spacing:.5px; }
    .dc-phone:hover { color:#1456E8; }
    .dc-nophone { font-size:.72rem; color:#be123c; font-weight:600; }
    .dc-actions { display:flex; gap:.35rem; }
    .dc-btn { display:inline-flex; align-items:center; gap:.25rem; padding:.35rem .6rem; border-radius:.5rem; font-size:.75rem; font-weight:700; text-decoration:none; white-space:nowrap; }
    .dc-call { background:rgba(20,86,232,.1); color:#1456E8; }
    .dc-call:hover { background:rgba(20,86,232,.2); }
    .dc-wa { background:#dcfce7; color:#15803d; }
    .dc-wa:hover { background:#bbf7d0; }
    .dc-compact .dc-btn span { display:none; }
    .dc-compact .dc-phone { font-size:.85rem; }
</style>
@endonce
