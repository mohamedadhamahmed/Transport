{{-- هيدر موحّد لشاشات النقليات: @include('transport.partials.page-header', ['title' => .., 'subtitle' => .., 'action' => ['url' => .., 'label' => .., 'can' => ..]]) --}}
<div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
    <div class="flex items-center gap-3">
        <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
            <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 6h11v10H3z"/><path d="M14 9h4l3 3v4h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/>
            </svg>
        </span>
        <div>
            <h2 class="text-white font-bold text-lg leading-tight">{{ $title }}</h2>
            @if (!empty($subtitle))
                <p class="text-white/45 text-xs mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    @if (!empty($action) && (empty($action['can']) || auth()->user()?->can($action['can'])))
        <a href="{{ $action['url'] }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition whitespace-nowrap">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 5v14M5 12h14" />
            </svg>
            {{ $action['label'] }}
        </a>
    @endif
</div>
