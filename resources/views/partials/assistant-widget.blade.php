{{--
    ودجت "المساعد": أيقونة عائمة أسفل يسار الشاشة في كل صفحات النظام.
    بتفتح لوحة فيها كل أقسام الشاشة الجانبية، وكل قسم فيه عملياته
    (إنشاء / تعديل / مرتجعات / تقارير)، وكل عملية فيها خطوات مرقّمة
    تشرح إزاي تتعمل من واقع حقول الشاشة الحقيقية.

    البيانات مصدرها lang/{ar,en}/assistant.php - محتوى ثابت، مفيش استعلامات قاعدة بيانات هنا.
--}}
@php
    $assistantSections = __('assistant.sections');
    $assistantIcons = [
        'bag' => '<path d="M6 8h12l-1 12H7L6 8Z" /><path d="M9 8V6a3 3 0 0 1 6 0v2" />',
        'cart' => '<circle cx="9" cy="20" r="1" /><circle cx="17" cy="20" r="1" /><path d="M3 4h2l2.4 12.4a1 1 0 0 0 1 .8h8.4a1 1 0 0 0 1-.8L20 8H6" />',
        'doc' => '<rect x="6" y="3" width="12" height="18" rx="1" /><path d="M9 8h6M9 12h6M9 16h4" />',
        'box' => '<path d="M3 8l9-5 9 5-9 5-9-5Z" /><path d="M3 8v8l9 5 9-5V8" /><path d="M12 13v8" />',
        'store' => '<path d="M4 21V10l8-6 8 6v11" /><path d="M9 21v-6h6v6" />',
        'gear' => '<circle cx="12" cy="12" r="3" /><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z" />',
        'return' => '<path d="M3 7v6h6" /><path d="M3 13a9 9 0 1 0 3-6.7L3 9" />',
        'tag' => '<path d="M20.6 12.6 12.6 20.6a2 2 0 0 1-2.83 0l-6.37-6.37a2 2 0 0 1 0-2.83L11.4 3.4A2 2 0 0 1 12.8 2.8H19a2 2 0 0 1 2 2v6.2a2 2 0 0 1-.4 1.2Z" /><circle cx="16.5" cy="7.5" r="1.5" />',
        'ledger' => '<path d="M4 21V6a2 2 0 0 1 2-2h9l5 5v12a0 0 0 0 1 0 0H6a2 2 0 0 1-2-2Z" /><path d="M15 4v4a1 1 0 0 0 1 1h4" /><path d="M8 12h8M8 16h5" />',
        'user' => '<circle cx="12" cy="8" r="3.5" /><path d="M5 20c0-3.5 3-6 7-6s7 2.5 7 6" />',
        'clock' => '<circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 3" />',
        'wallet' => '<path d="M3 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z" /><path d="M16 12h3" />',
        'shield' => '<path d="M12 2 4 5v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V5l-8-3Z" /><path d="M9 12l2 2 4-4" />',
        'truck' => '<rect x="1" y="3" width="15" height="13" rx="1" /><polygon points="16 8 20 8 23 11 23 16 16 16 16 8" /><circle cx="5.5" cy="18.5" r="2.5" /><circle cx="18.5" cy="18.5" r="2.5" />',
        'wrench' => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />',
    ];
    $isRtl = app()->getLocale() === 'ar';
    $backPath = $isRtl ? 'M9 6l6 6-6 6' : 'M15 6l-6 6 6 6';
    $fwdPath = $isRtl ? 'M15 6l-6 6 6 6' : 'M9 6l6 6-6 6';
@endphp

<div
    x-data="{
        assistantOpen: false,
        assistantView: 'sections',
        activeSection: null,
        activeItem: null,
        openSection(i) { this.activeSection = i; this.assistantView = 'items'; },
        openItem(i) { this.activeItem = i; this.assistantView = 'steps'; },
        backToSections() { this.assistantView = 'sections'; this.activeSection = null; this.activeItem = null; },
        backToItems() { this.assistantView = 'items'; this.activeItem = null; },
        toggleAssistant() {
            this.assistantOpen = !this.assistantOpen;
            if (! this.assistantOpen) {
                setTimeout(() => { this.assistantView = 'sections'; this.activeSection = null; this.activeItem = null; }, 250);
            }
        },
    }"
    dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
    style="position: fixed; bottom: 1.25rem; left: 1.25rem; z-index: 60;"
>
    {{-- زرار الأيقونة العائمة --}}
    <button type="button" @click="toggleAssistant()" title="{{ __('assistant.title') }}"
        class="w-14 h-14 rounded-full shadow-lg shadow-black/25 flex items-center justify-center text-white bg-gradient-to-br from-[#1456E8] to-[#6B2FD6] hover:scale-105 active:scale-95 transition-transform">
        <svg x-show="!assistantOpen" class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 3C7 3 3 6.6 3 11c0 2.4 1.2 4.6 3.1 6.1-.1 1.2-.5 2.4-1.3 3.4a.5.5 0 0 0 .5.8c1.8-.3 3.3-1 4.4-1.8.7.2 1.5.3 2.3.3 5 0 9-3.6 9-8s-4-8-9-8Z" />
            <circle cx="8.5" cy="11" r="1" fill="currentColor" stroke="none" />
            <circle cx="12" cy="11" r="1" fill="currentColor" stroke="none" />
            <circle cx="15.5" cy="11" r="1" fill="currentColor" stroke="none" />
        </svg>
        <svg x-show="assistantOpen" x-cloak class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 6l12 12M18 6L6 18" />
        </svg>
    </button>

    {{-- لوحة المساعد --}}
    <div x-show="assistantOpen" x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="assistantOpen = false"
        style="position: absolute; bottom: 4.5rem; left: 0; width: 22rem; max-width: calc(100vw - 2rem); max-height: 72vh;"
        class="flex flex-col bg-white rounded-2xl shadow-2xl ring-1 ring-black/10 overflow-hidden">

        {{-- هيدر اللوحة --}}
        <div class="shrink-0 bg-gradient-to-br from-[#1456E8] to-[#6B2FD6] text-white px-4 py-3 flex items-center gap-2">
            <button type="button" x-show="assistantView !== 'sections'" x-cloak
                @click="assistantView === 'steps' ? backToItems() : backToSections()"
                class="shrink-0 rounded-full p-1 hover:bg-white/15 transition">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="{{ $backPath }}" />
                </svg>
            </button>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold truncate">{{ __('assistant.title') }}</p>
                <p class="text-[11px] text-white/70 truncate" x-show="assistantView === 'sections'">{{ __('assistant.subtitle') }}</p>
            </div>
            <button type="button" @click="assistantOpen = false" class="shrink-0 rounded-full p-1 hover:bg-white/15 transition" title="{{ __('assistant.close') }}">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>

        {{-- جسم اللوحة (قابل للتمرير) --}}
        <div class="flex-1 overflow-y-auto p-2">

            {{-- 1) قائمة الأقسام --}}
            <div x-show="assistantView === 'sections'" class="space-y-1">
                <p class="px-3 pt-1 pb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400">{{ __('assistant.sections_title') }}</p>
                @foreach ($assistantSections as $sIndex => $section)
                    <button type="button" @click="openSection({{ $sIndex }})"
                        class="w-full flex items-center gap-3 rounded-xl px-3 py-2.5 text-start hover:bg-gray-50 transition">
                        <span class="shrink-0 w-9 h-9 rounded-lg bg-[#1456E8]/10 text-[#1456E8] flex items-center justify-center">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                {!! $assistantIcons[$section['icon']] ?? $assistantIcons['doc'] !!}
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1 text-sm font-medium text-gray-700 truncate">{{ $section['label'] }}</span>
                        <svg class="w-4 h-4 shrink-0 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="{{ $fwdPath }}" />
                        </svg>
                    </button>
                @endforeach
            </div>

            {{-- 2) قائمة عمليات كل قسم --}}
            @foreach ($assistantSections as $sIndex => $section)
                <div x-show="assistantView === 'items' &amp;&amp; activeSection === {{ $sIndex }}" class="space-y-1">
                    <p class="px-3 pt-1 pb-2 text-xs font-semibold text-gray-400">{{ $section['label'] }}</p>
                    @foreach ($section['items'] as $iIndex => $item)
                        <button type="button" @click="activeSection = {{ $sIndex }}; openItem({{ $iIndex }})"
                            class="w-full flex items-center gap-2 rounded-xl px-3 py-2.5 text-start hover:bg-gray-50 transition">
                            <span class="shrink-0 w-6 h-6 rounded-full bg-gray-100 text-gray-500 text-[11px] font-semibold flex items-center justify-center">{{ $iIndex + 1 }}</span>
                            <span class="min-w-0 flex-1 text-sm text-gray-700">{{ $item['label'] }}</span>
                            <svg class="w-4 h-4 shrink-0 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="{{ $fwdPath }}" />
                            </svg>
                        </button>
                    @endforeach
                </div>
            @endforeach

            {{-- 3) خطوات كل عملية --}}
            @foreach ($assistantSections as $sIndex => $section)
                @foreach ($section['items'] as $iIndex => $item)
                    <div x-show="assistantView === 'steps' &amp;&amp; activeSection === {{ $sIndex }} &amp;&amp; activeItem === {{ $iIndex }}">
                        <p class="px-2 pt-1 pb-2 text-sm font-semibold text-gray-800">{{ $item['label'] }}</p>
                        <ol class="space-y-2.5 px-2 pb-2">
                            @foreach ($item['steps'] as $stepIndex => $step)
                                <li class="flex gap-2.5 text-sm text-gray-600 leading-relaxed">
                                    <span class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-[#1456E8] text-white text-[11px] font-bold flex items-center justify-center">{{ $stepIndex + 1 }}</span>
                                    <span>{{ $step }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endforeach
            @endforeach

        </div>
    </div>
</div>
