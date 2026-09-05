@php
    // نفس التصنيفات المستخدمة في السايدبار - بس هنا شكل أزرار سريعة (Pills).
    // كل زرار بيودّي لأول صفحة في القسم المقابل ليه في السايدبار (نفس
    // الراوتات المستخدمة هناك بالظبط - راجعي main-sidebar.blade.php):
    // المبيعات -> فواتير المبيعات، المشتريات -> فواتير المشتريات،
    // المحاسبة والفواتير -> شجرة/قائمة الحسابات، المخزون -> اختيار
    // الفرع لعرض المنتجات. كانت الأربعة روابط دي href="#" (مؤقتة ومحدش
    // كملها) فمكنتش شغالة خالص - ده الإصلاح.
    $quickLinks = [
        ['label' => __('messages.sales'), 'url' => route('invoices.index'), 'icon' => 'bag', 'tint' => 'text-[#1456E8] bg-[#1456E8]/10'],
        ['label' => __('messages.purchases'), 'url' => route('purchases.index'), 'icon' => 'cart', 'tint' => 'text-[#F5811E] bg-[#F5811E]/10'],
        ['label' => __('messages.accounting_invoices'), 'url' => route('accounts.index'), 'icon' => 'doc', 'tint' => 'text-violet-600 bg-violet-500/10'],
        ['label' => __('messages.inventory'), 'url' => route('products.choose_branch'), 'icon' => 'box', 'tint' => 'text-emerald-600 bg-emerald-500/10'],
    ];
    $icons = [
        'bag' => '<path d="M6 8h12l-1 12H7L6 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
        'cart' => '<circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/><path d="M3 4h2l2.4 12.4a1 1 0 0 0 1 .8h8.4a1 1 0 0 0 1-.8L20 8H6"/>',
        'doc' => '<rect x="6" y="3" width="12" height="18" rx="1"/><path d="M9 8h6M9 12h6M9 16h4"/>',
        'box' => '<path d="M3 8l9-5 9 5-9 5-9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/>',
    ];
@endphp
<header class="bg-white/90 backdrop-blur border-b border-gray-100 shadow-sm sticky top-0 z-20">
    <div class="flex items-center gap-4 px-4 sm:px-7 py-3.5">
        <!-- زرار فتح القائمة على الموبايل -->
        <button type="button" @click="sidebarOpen = !sidebarOpen"
                class="lg:hidden flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <span class="hidden lg:block w-px h-8 bg-gray-100 shrink-0"></span>

        <!-- أزرار وصول سريع -->
        <div class="hidden md:flex items-center gap-2 overflow-x-auto">
            @foreach ($quickLinks as $link)
                <a href="{{ $link['url'] }}"
                   class="group flex items-center gap-2.5 shrink-0 rounded-xl border border-gray-100 bg-gray-50/70 ps-2.5 pe-4 py-2 text-sm font-medium text-gray-600 transition-all duration-200 hover:bg-white hover:border-gray-200 hover:shadow-md hover:-translate-y-0.5">
                    <span class="flex items-center justify-center w-7 h-7 rounded-lg {{ $link['tint'] }} shrink-0">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            {!! $icons[$link['icon']] !!}
                        </svg>
                    </span>
                    <span class="group-hover:text-gray-900 transition-colors">{{ $link['label'] }}</span>
                </a>
            @endforeach
        </div>

        <div class="flex-1"></div>

        <span class="hidden sm:block w-px h-8 bg-gray-100 shrink-0"></span>

        <!-- جرس الإشعارات -->
        <div class="relative shrink-0" x-data="{
                open: false,
                loading: true,
                loadingMore: false,
                hasMore: false,
                count: 0,
                items: [],
                recent: [],
                init() {
                    fetch('{{ route('notifications.summary') }}', { headers: { 'Accept': 'application/json' } })
                        .then(res => res.ok ? res.json() : { count: 0, items: [], recent: [], has_more: false })
                        .then(data => {
                            this.count = data.count || 0;
                            this.items = data.items || [];
                            this.recent = data.recent || [];
                            this.hasMore = data.has_more || false;
                            this.loading = false;
                        })
                        .catch(() => { this.loading = false; });
                },
                loadMore() {
                    if (this.loadingMore || !this.hasMore) return;
                    this.loadingMore = true;
                    fetch('{{ route('notifications.recent') }}?offset=' + this.recent.length, { headers: { 'Accept': 'application/json' } })
                        .then(res => res.ok ? res.json() : { recent: [], has_more: false })
                        .then(data => {
                            this.recent = this.recent.concat(data.recent || []);
                            this.hasMore = data.has_more || false;
                            this.loadingMore = false;
                        })
                        .catch(() => { this.loadingMore = false; });
                }
             }"
             @click.outside="open = false">
            <button type="button" @click="open = !open"
                    class="relative flex items-center justify-center w-10 h-10 rounded-xl border border-gray-100 bg-gray-50/70 text-gray-500 transition-all duration-200 hover:bg-white hover:border-gray-200 hover:shadow-md hover:text-[#1456E8]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5" />
                    <path d="M9 17a3 3 0 0 0 6 0" />
                </svg>
                <span x-show="!loading && count > 0" x-cloak
                      class="absolute -top-1 -end-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 ring-2 ring-white text-white text-[10px] leading-[18px] font-bold text-center">
                    <span x-text="count > 9 ? '9+' : count"></span>
                </span>
            </button>

            <div x-show="open" x-cloak x-transition
                 class="absolute end-0 mt-2.5 w-80 max-w-[90vw] bg-white rounded-2xl shadow-xl ring-1 ring-black/5 border border-gray-100 py-2 z-30 overflow-hidden">
                <div class="px-4 py-3 text-sm font-bold text-gray-700 border-b border-gray-100 bg-gray-50/60">
                    {{ __('messages.notifications') }}
                </div>
                <div class="max-h-[28rem] overflow-y-auto">
                    <!-- تنبيهات (زاتكا فشلت / مخزون ناقص) -->
                    <template x-if="!loading && items.length === 0">
                        <p class="px-4 py-5 text-center text-sm text-gray-400">{{ __('messages.no_notifications') }}</p>
                    </template>
                    <template x-for="item in items" :key="item.type">
                        <a :href="item.url" class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 border-b border-gray-50 transition-colors">
                            <span class="mt-0.5 flex items-center justify-center w-8 h-8 rounded-lg bg-rose-500/10 text-rose-600 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 9v4M12 17h.01M10.3 4.3 2.7 18a1.5 1.5 0 0 0 1.3 2.2h16a1.5 1.5 0 0 0 1.3-2.2L13.7 4.3a1.5 1.5 0 0 0-2.6 0Z" />
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <p class="text-sm text-gray-700" x-text="item.message"></p>
                                <p class="text-xs text-[#1456E8] mt-1 font-medium">{{ __('messages.click_to_review') }}</p>
                            </span>
                        </a>
                    </template>

                    <!-- آخر العمليات (للعلم فقط - مش تنبيه لمشكلة) -->
                    <div class="px-4 py-2 text-[11px] font-bold text-gray-400 uppercase tracking-wide bg-gray-50/60 border-b border-gray-100">
                        {{ __('messages.recent_operations') }}
                    </div>
                    <template x-if="!loading && recent.length === 0">
                        <p class="px-4 py-5 text-center text-sm text-gray-400">{{ __('messages.no_recent_operations') }}</p>
                    </template>
                    <template x-for="(op, idx) in recent" :key="idx">
                        <a :href="op.url" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 border-b border-gray-50 last:border-0 transition-colors">
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg shrink-0"
                                  :class="{
                                      'bg-[#1456E8]/10 text-[#1456E8]': op.type === 'sale',
                                      'bg-[#F5811E]/10 text-[#F5811E]': op.type === 'purchase',
                                      'bg-emerald-500/10 text-emerald-600': op.type === 'receipt',
                                      'bg-rose-500/10 text-rose-600': op.type === 'payment'
                                  }">
                                <svg x-show="op.type === 'sale'" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 8h12l-1 12H7L6 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>
                                </svg>
                                <svg x-show="op.type === 'purchase'" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/><path d="M3 4h2l2.4 12.4a1 1 0 0 0 1 .8h8.4a1 1 0 0 0 1-.8L20 8H6"/>
                                </svg>
                                <svg x-show="op.type === 'receipt'" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 3v9m0 0-4-4m4 4 4-4" /><path d="M5 15v3a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3" />
                                </svg>
                                <svg x-show="op.type === 'payment'" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 15V6m0 0-4 4m4-4 4 4" /><path d="M5 15v3a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3" />
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <p class="text-sm text-gray-700 truncate">
                                    <span x-text="op.label"></span> <span class="text-gray-400" x-text="op.number"></span>
                                    <template x-if="op.party"><span class="text-gray-400">- </span></template>
                                    <span class="text-gray-500" x-text="op.party"></span>
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    <span x-text="Number(op.total).toFixed(2)"></span> · <span x-text="op.time"></span>
                                </p>
                            </span>
                        </a>
                    </template>

                    <button type="button" x-show="!loading && hasMore" x-cloak @click="loadMore()"
                            class="w-full px-4 py-3 text-sm font-medium text-[#1456E8] hover:bg-gray-50 transition-colors disabled:opacity-60"
                            :disabled="loadingMore">
                        <span x-show="!loadingMore">{{ __('messages.show_more') }}</span>
                        <span x-show="loadingMore" x-cloak>{{ __('messages.loading') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>
