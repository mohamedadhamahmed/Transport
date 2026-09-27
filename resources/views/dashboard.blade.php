<x-app-layout>

    <div class="py-6">
        <div class="dc-max-w-page mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- هيدر ترحيبي بلون البراند الكحلي: ترحيب + ساعة/تاريخ حي +
                 فلتر الفرع (بارز في بلوك لوحده عشان يبان واضح إنه فلتر
                 فعلي، مش مجرد تفصيلة صغيرة في الزاوية) --}}
            <div class="dash-anim rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-white font-bold text-lg">
                        {{ __('messages.dashboard_welcome') }}{{ auth()->user()?->name ? '، ' . auth()->user()->name : '' }} 👋
                    </h2>
                    <p class="text-white/60 text-sm mt-1">{{ __('messages.dashboard_subtitle') }}</p>
                </div>
                <div class="flex flex-col items-end gap-1">
                    <p class="text-white font-semibold text-sm tabular-nums bg-white/10 rounded-lg px-3 py-1 flex items-center gap-1.5" id="dashboard-clock">
                        <svg class="w-3.5 h-3.5 text-white/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                        <span id="dashboard-clock-time">--:--:--</span>
                    </p>
                    <p class="text-white/60 text-xs" id="dashboard-today-date"></p>
                </div>
            </div>

            {{-- حالة الشاحنات (قسم النقليات) - ظاهر لكل المستخدمين --}}
            @include('transport.partials.dashboard-trucks')

            {{-- شريط الفرع: select بارز + شارة واضحة تأكّد إن كل الشاشة
                 تحتها فعلاً بتعرض بيانات الفرع المختار (أو كل الفروع) --}}
            <div class="dash-anim bg-white rounded-xl border border-gray-100 shadow-sm px-4 sm:px-5 py-3 flex items-center justify-between flex-wrap gap-3" style="--dash-delay: 20ms">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-[#1456E8]/10 text-[#1456E8] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-6.4 7-11.5A7 7 0 0 0 5 9.5C5 14.6 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                    </span>
                    <label for="dashboard-branch-filter" class="text-xs font-medium text-gray-500 shrink-0">{{ __('messages.dashboard_branch_filter') }}</label>
                    <div class="relative">
                        <select id="dashboard-branch-filter"
                                class="appearance-none bg-gray-50 hover:bg-gray-100 text-[#0F1B4C] font-medium text-sm rounded-lg pe-8 ps-3 py-1.5 border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#1456E8]/30 transition cursor-pointer">
                            <option value="">{{ __('messages.dashboard_all_branches') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <svg class="w-3.5 h-3.5 text-gray-400 absolute top-1/2 -translate-y-1/2 start-2.5 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </div>
                </div>
                <span id="dashboard-viewing-chip" class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 rounded-full px-3 py-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                    {{ __('messages.dashboard_viewing_all_branches') }}
                </span>
            </div>

            {{-- إجراءات سريعة --}}
            @php
                $quickActions = collect([
                    auth()->user()?->hasPermission('transport_invoices.create') ? ['label' => __('transport.new_invoice'), 'url' => route('transport.invoices.create'), 'color' => '#1456E8'] : null,
                    auth()->user()?->hasPermission('purchases.create') ? ['label' => __('purchases.new_purchase'), 'url' => route('purchases.create'), 'color' => '#F5811E'] : null,
                    auth()->user()?->hasPermission('customers.create') ? ['label' => __('customers.new_customer'), 'url' => route('customers.create'), 'color' => '#0F1B4C'] : null,
                    auth()->user()?->hasPermission('suppliers.create') ? ['label' => __('suppliers.new_supplier'), 'url' => route('suppliers.create'), 'color' => '#6B2FD6'] : null,
                    auth()->user()?->hasPermission('vouchers.create') ? ['label' => __('vouchers.new_receipt'), 'url' => route('vouchers.create', ['type' => 'receipt']), 'color' => '#0F9D58'] : null,
                    auth()->user()?->hasPermission('vouchers.create') ? ['label' => __('vouchers.new_payment'), 'url' => route('vouchers.create', ['type' => 'payment']), 'color' => '#E11D48'] : null,
                ])->filter()->values();
            @endphp
            @if ($quickActions->isNotEmpty())
                <div class="dash-anim flex flex-wrap gap-2.5" style="--dash-delay: 40ms">
                    @foreach ($quickActions as $action)
                        <a href="{{ $action['url'] }}"
                           style="border-color: {{ $action['color'] }}33; color: {{ $action['color'] }};"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium bg-white border shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
                            <span class="text-lg leading-none">+</span> {{ $action['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{--
                الشاشة بتظهر على طول بالكروت دي فاضية (skeleton)، وبعد ما
                الصفحة تخلص تحميل بتاخد الأرقام الحقيقية من route('dashboard.stats')
                بطلب Ajax واحد بس - بدل ما المستخدم يستنى كل استعلامات
                قاعدة البيانات (مبيعات/مشتريات/مخزون...) قبل ما يشوف أي حاجة.
                كل مرة يتغيّر فيها فلتر الفرع فوق، نفس الطلب ده بيتكرر تاني
                بـ ?branch_id= وكل حاجة تحت (الكروت/الرسوم/الجداول) بتتحدّث.
            --}}

            {{-- الصف الأول: أرقام اليوم الأساسية (زي الكروت الملونة الكبيرة) --}}
            <h4 class="dash-anim text-xs font-bold text-gray-400 uppercase tracking-wide -mb-2" style="--dash-delay: 50ms">{{ __('messages.dashboard_section_overview') }}</h4>
            <div id="dashboard-stats-today" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <div class="dash-anim dash-hero rounded-2xl p-5 text-white shadow-lg" style="--dash-delay: 60ms; background: linear-gradient(135deg,#1456E8,#0F3FBE); box-shadow: 0 12px 24px -10px #1456E855;">
                    <div class="flex items-center justify-between">
                        <span class="w-11 h-11 rounded-xl bg-white/15 flex items-center justify-center">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8h12l-1 12H7L6 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
                        </span>
                        <span class="text-[11px] bg-white/15 rounded-full px-2 py-0.5" data-stat="today_sales_count">…</span>
                    </div>
                    <p class="text-xs font-medium text-white/70 mt-3">{{ __('messages.dashboard_today_sales') }}</p>
                    <p class="text-2xl font-bold mt-0.5" data-stat="today_sales_net" data-format="money"><span class="dash-skeleton dash-skeleton--light"></span></p>
                    <div class="mt-2 pt-2 border-t border-white/15 flex items-center justify-between text-xs">
                        <span class="text-white/70">{{ __('reports.profit') }}:</span>
                        <span class="font-bold text-white" data-stat="today_sales_profit" data-format="money"><span class="dash-skeleton dash-skeleton--light"></span></span>
                    </div>
                </div>

                <div class="dash-anim dash-hero rounded-2xl p-5 text-white shadow-lg" style="--dash-delay: 100ms; background: linear-gradient(135deg,#F5811E,#C9600C); box-shadow: 0 12px 24px -10px #F5811E55;">
                    <div class="flex items-center justify-between">
                        <span class="w-11 h-11 rounded-xl bg-white/15 flex items-center justify-center">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/><path d="M3 4h2l2.4 12.4a1 1 0 0 0 1 .8h8.4a1 1 0 0 0 1-.8L20 8H6"/></svg>
                        </span>
                        <span class="text-[11px] bg-white/15 rounded-full px-2 py-0.5" data-stat="today_purchases_count">…</span>
                    </div>
                    <p class="text-xs font-medium text-white/70 mt-3">{{ __('messages.dashboard_today_purchases') }}</p>
                    <p class="text-2xl font-bold mt-0.5" data-stat="today_purchases_net" data-format="money"><span class="dash-skeleton dash-skeleton--light"></span></p>
                </div>

                <div class="dash-anim dash-hero rounded-2xl p-5 text-white shadow-lg" style="--dash-delay: 140ms; background: linear-gradient(135deg,#10B981,#047857); box-shadow: 0 12px 24px -10px #10B98155;">
                    <div class="flex items-center justify-between">
                        <span class="w-11 h-11 rounded-xl bg-white/15 flex items-center justify-center">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14V6a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v8"/><rect x="2" y="14" width="20" height="6" rx="1.5"/><path d="M12 17h.01"/></svg>
                        </span>
                        <span class="text-[11px] bg-white/15 rounded-full px-2 py-0.5" data-stat="today_receipts_count">…</span>
                    </div>
                    <p class="text-xs font-medium text-white/70 mt-3">{{ __('messages.dashboard_today_receipts') }}</p>
                    <p class="text-2xl font-bold mt-0.5" data-stat="today_receipts_net" data-format="money"><span class="dash-skeleton dash-skeleton--light"></span></p>
                </div>

                <div class="dash-anim dash-hero rounded-2xl p-5 text-white shadow-lg" style="--dash-delay: 180ms; background: linear-gradient(135deg,#E11D48,#9F1239); box-shadow: 0 12px 24px -10px #E11D4855;">
                    <div class="flex items-center justify-between">
                        <span class="w-11 h-11 rounded-xl bg-white/15 flex items-center justify-center">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 10V6a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v4"/><rect x="2" y="10" width="20" height="10" rx="1.5"/><path d="M12 15h.01"/></svg>
                        </span>
                        <span class="text-[11px] bg-white/15 rounded-full px-2 py-0.5" data-stat="today_payments_count">…</span>
                    </div>
                    <p class="text-xs font-medium text-white/70 mt-3">{{ __('messages.dashboard_today_payments') }}</p>
                    <p class="text-2xl font-bold mt-0.5" data-stat="today_payments_net" data-format="money"><span class="dash-skeleton dash-skeleton--light"></span></p>
                </div>
            </div>

            {{-- بطاقة جديدة: الرصيد النقدي الحالي (خزينة + بنوك) - رقم
                 "لحظي" مختلف عن باقي كروت "اليوم"، فمعمول له بانر مستقل
                 بارز بدل ما يتلخبط جوه صفوف الإحصائيات التانية. --}}
            <div class="dash-anim bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between flex-wrap gap-4" style="--dash-delay: 200ms">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 rounded-xl bg-[#0d9488]/10 text-[#0d9488] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/></svg>
                    </span>
                    <div>
                        <p class="text-xs font-medium text-gray-500">{{ __('messages.dashboard_current_cash_balance') }}</p>
                        <p class="text-[11px] text-gray-400">{{ __('messages.dashboard_current_cash_balance_note') }}</p>
                    </div>
                </div>
                <p class="text-2xl font-bold text-[#0F1B4C]" id="dashboard-cash-balance" data-stat="current_cash_balance" data-format="money"><span class="dash-skeleton"></span></p>
            </div>

            {{-- ملخص الحسابات: سندات القبض والصرف والقيود اليومية/الافتتاحية
                 لليوم الحالي بس - قسم مستقل عشان يبان واضح إن دي أرقام
                 محاسبية منفصلة عن مبيعات/مشتريات اليوم فوق. --}}
            <h4 class="dash-anim text-xs font-bold text-gray-400 uppercase tracking-wide -mb-2" style="--dash-delay: 205ms">{{ __('messages.dashboard_section_accounts') }}</h4>
            <div id="dashboard-stats-accounts" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">

                <div class="dash-anim dash-card bg-white rounded-xl border border-gray-100 shadow-sm p-4" style="--dash-delay: 210ms">
                    <p class="text-[11px] font-medium text-gray-500">{{ __('messages.dashboard_today_receipts') }}</p>
                    <p class="text-lg font-bold text-emerald-600 mt-1" data-stat="today_receipts_net" data-format="money"><span class="dash-skeleton"></span></p>
                    <p class="text-[10px] text-gray-400 mt-0.5"><span data-stat="today_receipts_count">-</span> {{ __('messages.dashboard_vouchers_suffix') }}</p>
                </div>

                <div class="dash-anim dash-card bg-white rounded-xl border border-gray-100 shadow-sm p-4" style="--dash-delay: 220ms">
                    <p class="text-[11px] font-medium text-gray-500">{{ __('messages.dashboard_today_payments') }}</p>
                    <p class="text-lg font-bold text-rose-600 mt-1" data-stat="today_payments_net" data-format="money"><span class="dash-skeleton"></span></p>
                    <p class="text-[10px] text-gray-400 mt-0.5"><span data-stat="today_payments_count">-</span> {{ __('messages.dashboard_vouchers_suffix') }}</p>
                </div>

                <div class="dash-anim dash-card bg-white rounded-xl border border-gray-100 shadow-sm p-4" style="--dash-delay: 230ms">
                    <p class="text-[11px] font-medium text-gray-500">{{ __('messages.dashboard_net_cash_movement_today') }}</p>
                    <p class="text-lg font-bold text-[#0F1B4C] mt-1" id="dashboard-net-movement" data-stat="today_net_cash_movement" data-format="money"><span class="dash-skeleton"></span></p>
                </div>

                <div class="dash-anim dash-card bg-white rounded-xl border border-gray-100 shadow-sm p-4" style="--dash-delay: 240ms">
                    <p class="text-[11px] font-medium text-gray-500">{{ __('messages.dashboard_daily_entries_today') }}</p>
                    <p class="text-lg font-bold text-[#0F1B4C] mt-1" data-stat="today_daily_entries_count"><span class="dash-skeleton"></span></p>
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ __('messages.dashboard_entries_suffix') }}</p>
                </div>

                <div class="dash-anim dash-card bg-white rounded-xl border border-gray-100 shadow-sm p-4" style="--dash-delay: 250ms">
                    <p class="text-[11px] font-medium text-gray-500">{{ __('messages.dashboard_opening_entries_today') }}</p>
                    <p class="text-lg font-bold text-[#0F1B4C] mt-1" data-stat="today_opening_entries_count"><span class="dash-skeleton"></span></p>
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ __('messages.dashboard_entries_suffix') }}</p>
                </div>
            </div>

            {{-- الصف الثاني: أرقام عامة/شهرية أصغر --}}
            <div id="dashboard-stats-secondary" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">

                <div class="dash-anim dash-card bg-white rounded-xl border border-gray-100 shadow-sm p-4" style="--dash-delay: 220ms">
                    <p class="text-[11px] font-medium text-gray-500">{{ __('messages.dashboard_customers_count') }}</p>
                    <p class="text-lg font-bold text-[#0F1B4C] mt-1" data-stat="customers_count"><span class="dash-skeleton"></span></p>
                </div>
                <div class="dash-anim dash-card bg-white rounded-xl border border-gray-100 shadow-sm p-4" style="--dash-delay: 240ms">
                    <p class="text-[11px] font-medium text-gray-500">{{ __('messages.dashboard_suppliers_count') }}</p>
                    <p class="text-lg font-bold text-[#0F1B4C] mt-1" data-stat="suppliers_count"><span class="dash-skeleton"></span></p>
                </div>
                <div class="dash-anim dash-card bg-white rounded-xl border border-gray-100 shadow-sm p-4" style="--dash-delay: 260ms">
                    <p class="text-[11px] font-medium text-gray-500">{{ __('messages.dashboard_month_sales') }}</p>
                    <p class="text-lg font-bold text-[#0F1B4C] mt-1" data-stat="month_sales_net" data-format="money"><span class="dash-skeleton"></span></p>
                    <p class="text-[11px] text-emerald-600 font-semibold mt-0.5">
                        {{ __('reports.net_profit') }}: <span data-stat="month_sales_profit" data-format="money"><span class="dash-skeleton"></span></span>
                    </p>
                </div>
                <div class="dash-anim dash-card bg-white rounded-xl border border-gray-100 shadow-sm p-4" style="--dash-delay: 280ms">
                    <p class="text-[11px] font-medium text-gray-500">{{ __('messages.dashboard_month_purchases') }}</p>
                    <p class="text-lg font-bold text-[#0F1B4C] mt-1" data-stat="month_purchases_net" data-format="money"><span class="dash-skeleton"></span></p>
                </div>
                <div class="dash-anim dash-card bg-white rounded-xl border border-gray-100 shadow-sm p-4" style="--dash-delay: 300ms">
                    <p class="text-[11px] font-medium text-gray-500">{{ __('messages.dashboard_low_stock') }}</p>
                    <p class="text-lg font-bold text-rose-600 mt-1" data-stat="low_stock_count"><span class="dash-skeleton"></span></p>
                </div>
                <div class="dash-anim dash-card bg-white rounded-xl border border-gray-100 shadow-sm p-4" style="--dash-delay: 320ms">
                    <p class="text-[11px] font-medium text-gray-500">{{ __('transport.loaded_trucks') }}</p>
                    <p class="text-lg font-bold text-sky-600 mt-1" data-stat="loaded_trucks_count"><span class="dash-skeleton"></span></p>
                </div>
            </div>

            {{-- الرسوم البيانية --}}
            <h4 class="dash-anim text-xs font-bold text-gray-400 uppercase tracking-wide -mb-2" style="--dash-delay: 330ms">{{ __('messages.dashboard_section_analytics') }}</h4>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                <div class="dash-anim lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5" style="--dash-delay: 340ms">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-[#0F1B4C] text-sm">{{ __('messages.dashboard_sales_purchases_trend') }}</h3>
                        <div class="flex items-center gap-3 text-[11px] text-gray-500">
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full inline-block" style="background:#1456E8"></span>{{ __('messages.dashboard_legend_sales') }}</span>
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full inline-block" style="background:#F5811E"></span>{{ __('messages.dashboard_legend_purchases') }}</span>
                        </div>
                    </div>
                    <div class="relative h-64">
                        <canvas id="dashboard-trend-chart"></canvas>
                    </div>
                </div>

                <div class="dash-anim bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col" style="--dash-delay: 360ms">
                    <h3 class="font-bold text-[#0F1B4C] text-sm mb-3">{{ __('messages.dashboard_customers_suppliers_ratio') }}</h3>
                    <div class="relative flex-1 min-h-[180px]">
                        <canvas id="dashboard-ratio-chart"></canvas>
                    </div>
                    <div class="flex items-center justify-center gap-4 text-[11px] text-gray-500 mt-3">
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full inline-block" style="background:#1456E8"></span>{{ __('messages.dashboard_legend_customers') }}: <b class="text-gray-700" data-stat="customers_count">-</b></span>
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full inline-block" style="background:#F5811E"></span>{{ __('messages.dashboard_legend_suppliers') }}: <b class="text-gray-700" data-stat="suppliers_count">-</b></span>
                    </div>
                </div>
            </div>

            {{-- أكتر موظف وأكتر فرع/منتج بيعًا اليوم --}}
            <h4 class="dash-anim text-xs font-bold text-gray-400 uppercase tracking-wide -mb-2" style="--dash-delay: 370ms">{{ __('messages.dashboard_section_leaders') }}</h4>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                <div class="dash-anim bg-white rounded-2xl border border-gray-100 shadow-sm p-5" style="--dash-delay: 380ms">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-[#0F1B4C] text-sm">{{ __('messages.dashboard_top_employees') }}</h3>
                    </div>
                    <div id="dashboard-top-employees" class="space-y-3">
                        <p class="dash-empty text-sm text-gray-400 text-center py-6">{{ __('messages.dashboard_no_data_today') }}</p>
                    </div>
                </div>

                <div class="dash-anim bg-white rounded-2xl border border-gray-100 shadow-sm p-5" style="--dash-delay: 400ms">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-[#0F1B4C] text-sm" id="dashboard-top-branches-title">{{ __('messages.dashboard_top_branches') }}</h3>
                    </div>
                    <div id="dashboard-top-branches" class="space-y-3">
                        <p class="dash-empty text-sm text-gray-400 text-center py-6">{{ __('messages.dashboard_no_data_today') }}</p>
                    </div>
                </div>
            </div>

            {{-- أحدث العمليات --}}
            <h4 class="dash-anim text-xs font-bold text-gray-400 uppercase tracking-wide -mb-2" style="--dash-delay: 410ms">{{ __('messages.dashboard_section_recent') }}</h4>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                <div class="dash-anim bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" style="--dash-delay: 420ms">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h3 class="font-bold text-[#0F1B4C] text-sm">{{ __('messages.dashboard_recent_sales') }}</h3>
                        <div class="flex items-center gap-3">
                            @can('transport_invoices.view')
                                <a href="{{ route('transport.invoices.index') }}" class="text-xs text-[#1456E8] font-medium hover:underline">{{ __('messages.dashboard_view_all') }}</a>
                            @endcan
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-[11px] text-gray-400 border-b border-gray-50">
                                    <th class="text-start font-medium py-2 px-5">{{ __('messages.dashboard_table_invoice_no') }}</th>
                                    <th class="text-start font-medium py-2 px-2">{{ __('messages.dashboard_table_customer') }}</th>
                                    <th class="text-start font-medium py-2 px-2">{{ __('messages.dashboard_table_amount') }}</th>
                                    <th class="text-start font-medium py-2 px-5">{{ __('messages.dashboard_table_time') }}</th>
                                </tr>
                            </thead>
                            <tbody id="dashboard-recent-sales">
                                <tr><td colspan="4" class="dash-empty text-center text-gray-400 text-sm py-6">{{ __('messages.dashboard_no_data_today') }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="dash-anim bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" style="--dash-delay: 440ms">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h3 class="font-bold text-[#0F1B4C] text-sm">{{ __('messages.dashboard_recent_purchases') }}</h3>
                        @can('purchases.view')
                            <a href="{{ route('purchases.index') }}" class="text-xs text-[#1456E8] font-medium hover:underline">{{ __('messages.dashboard_view_all') }}</a>
                        @endcan
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-[11px] text-gray-400 border-b border-gray-50">
                                    <th class="text-start font-medium py-2 px-5">{{ __('messages.dashboard_table_invoice_no') }}</th>
                                    <th class="text-start font-medium py-2 px-2">{{ __('messages.dashboard_table_supplier') }}</th>
                                    <th class="text-start font-medium py-2 px-2">{{ __('messages.dashboard_table_amount') }}</th>
                                    <th class="text-start font-medium py-2 px-5">{{ __('messages.dashboard_table_time') }}</th>
                                </tr>
                            </thead>
                            <tbody id="dashboard-recent-purchases">
                                <tr><td colspan="4" class="dash-empty text-center text-gray-400 text-sm py-6">{{ __('messages.dashboard_no_data_today') }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <p id="dashboard-stats-error" class="hidden text-sm text-rose-600 bg-rose-50 border border-rose-100 rounded-xl px-4 py-3">
                {{ __('messages.dashboard_loading_error') }}
            </p>
        </div>
    </div>

    <style>
        /* شكل شيمر بسيط لحد ما رقم الكارت الحقيقي يوصل من الـ Ajax،
           بدل ما الكارت يفضل فاضي أو يظهر "0" مؤقت ممكن يلخبط المستخدم. */
        .dash-skeleton {
            display: inline-block;
            width: 3.5rem;
            height: 1.25rem;
            border-radius: 0.375rem;
            background: linear-gradient(90deg, #eef0f4 25%, #e2e5ea 37%, #eef0f4 63%);
            background-size: 400% 100%;
            animation: dash-shimmer 1.4s ease infinite;
        }
        .dash-skeleton--light {
            background: linear-gradient(90deg, rgba(255,255,255,.25) 25%, rgba(255,255,255,.4) 37%, rgba(255,255,255,.25) 63%);
            background-size: 400% 100%;
        }
        @keyframes dash-shimmer {
            0% { background-position: 100% 50%; }
            100% { background-position: 0 50%; }
        }
        .dash-card, .dash-hero { transition: box-shadow .15s ease, transform .15s ease; }
        .dash-card:hover, .dash-hero:hover { transform: translateY(-2px); }
        .dash-top-row { display: flex; align-items: center; gap: .75rem; }
        .dash-top-bar-track { flex: 1; height: .5rem; border-radius: 999px; background: #F1F3F8; overflow: hidden; }
        .dash-top-bar-fill { height: 100%; border-radius: 999px; width: 0%; transition: width 1s cubic-bezier(.22,.9,.3,1); }
        .dash-top-row { opacity: 0; animation: dash-row-in .5s ease forwards; }

        /* دخول متدرّج (staggered) للكروت والأقسام أول ما الصفحة تفتح -
           كل عنصر عليه --dash-delay مختلف فوق. */
        .dash-anim {
            opacity: 0;
            transform: translateY(10px);
            animation: dash-fade-up .5s cubic-bezier(.22,.9,.3,1) forwards;
            animation-delay: var(--dash-delay, 0ms);
        }
        @keyframes dash-fade-up {
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes dash-row-in {
            to { opacity: 1; }
        }
        /* تأثير "عدّاد" بسيط على الأرقام لحظة وصولها من الـ Ajax */
        .dash-value-pop { animation: dash-value-pop .4s ease; }
        @keyframes dash-value-pop {
            0% { transform: scale(.92); }
            60% { transform: scale(1.04); }
            100% { transform: scale(1); }
        }
        #dashboard-branch-filter option { background: #fff; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var locale = @json(app()->getLocale());
            var moneyLocale = 'en-US';
            var trendChart = null;
            var ratioChart = null;

            var dateEl = document.getElementById('dashboard-today-date');
            if (dateEl) {
                dateEl.textContent = new Date().toLocaleDateString(locale === 'ar' ? 'ar-SA' : 'en-US', {
                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
                });
            }

            // ساعة حية بتتحدث كل ثانية - طلب صريح ("عوز تظهر الساعة والتاريخ
            // في شاشة الرئيسية") بجانب التاريخ اللي كان موجود قبل كده.
            var clockTimeEl = document.getElementById('dashboard-clock-time');
            function tickClock() {
                if (!clockTimeEl) { return; }
                clockTimeEl.textContent = new Date().toLocaleTimeString(locale === 'ar' ? 'ar-SA' : 'en-US', {
                    hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: locale !== 'ar',
                });
            }
            tickClock();
            setInterval(tickClock, 1000);

            function formatMoney(value) {
                return Number(value || 0).toLocaleString(moneyLocale, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function formatNumber(value) {
                return Number(value || 0).toLocaleString(moneyLocale);
            }

            /**
             * عداد بسيط (count-up) من القيمة الحالية للعنصر لحد القيمة
             * الجديدة - بيدي إحساس "شغل متقدم" بدل ما الرقم يتغيّر فجأة.
             */
            function animateValue(el, toValue, isMoney) {
                var fromValue = parseFloat((el.textContent || '0').replace(/,/g, '')) || 0;
                if (!isFinite(fromValue)) { fromValue = 0; }
                var duration = 600;
                var start = null;

                function step(timestamp) {
                    if (!start) { start = timestamp; }
                    var progress = Math.min((timestamp - start) / duration, 1);
                    var eased = 1 - Math.pow(1 - progress, 3);
                    var current = fromValue + (toValue - fromValue) * eased;
                    el.textContent = isMoney ? formatMoney(current) : formatNumber(Math.round(current));
                    if (progress < 1) {
                        requestAnimationFrame(step);
                    } else {
                        el.textContent = isMoney ? formatMoney(toValue) : formatNumber(toValue);
                        el.classList.add('dash-value-pop');
                        setTimeout(function () { el.classList.remove('dash-value-pop'); }, 400);
                    }
                }
                requestAnimationFrame(step);
            }

            function renderTopList(containerId, rows, color) {
                var container = document.getElementById(containerId);
                if (!container) { return; }
                if (!rows || !rows.length) {
                    container.innerHTML = '<p class="dash-empty text-sm text-gray-400 text-center py-6">' + @json(__('messages.dashboard_no_data_today')) + '</p>';
                    return;
                }

                var max = Math.max.apply(null, rows.map(function (r) { return r.net; })) || 1;
                container.innerHTML = rows.map(function (r, i) {
                    var pct = Math.max(4, Math.round((r.net / max) * 100));
                    return '' +
                        '<div class="dash-top-row" style="animation-delay:' + (i * 70) + 'ms">' +
                        '  <span class="text-sm text-gray-700 w-28 shrink-0 truncate">' + (r.name || '-') + '</span>' +
                        '  <span class="dash-top-bar-track"><span class="dash-top-bar-fill" data-target-width="' + pct + '" style="background:' + color + '"></span></span>' +
                        '  <span class="text-xs font-semibold text-gray-600 w-20 shrink-0 text-end">' + formatMoney(r.net) + '</span>' +
                        '</div>';
                }).join('');

                // بنأخّر تحديد العرض النهائي شوية عشان الـ transition يتفعّل
                // فعليًا (من عرض 0% لحد العرض الحقيقي) بدل ما يظهر جاهز فجأة.
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        container.querySelectorAll('.dash-top-bar-fill').forEach(function (bar) {
                            bar.style.width = bar.getAttribute('data-target-width') + '%';
                        });
                    });
                });
            }

            function renderTable(containerId, rows, columns) {
                var tbody = document.getElementById(containerId);
                if (!tbody) { return; }
                if (!rows || !rows.length) {
                    tbody.innerHTML = '<tr><td colspan="4" class="dash-empty text-center text-gray-400 text-sm py-6">' + @json(__('messages.dashboard_no_data_today')) + '</td></tr>';
                    return;
                }
                tbody.innerHTML = rows.map(function (row, i) {
                    return '<tr class="border-b border-gray-50 last:border-0 dash-top-row" style="animation-delay:' + (i * 50) + 'ms">' +
                        '<td class="py-2.5 px-5 font-medium text-gray-700 whitespace-nowrap">' + (row.number || '-') + '</td>' +
                        '<td class="py-2.5 px-2 text-gray-600 whitespace-nowrap">' + (row[columns.name] || '-') + '</td>' +
                        '<td class="py-2.5 px-2 text-gray-700 font-semibold whitespace-nowrap">' + formatMoney(row.total) + '</td>' +
                        '<td class="py-2.5 px-5 text-gray-400 text-xs whitespace-nowrap">' + (row.time || '-') + '</td>' +
                        '</tr>';
                }).join('');
            }

            function renderTrendChart(trend) {
                var canvas = document.getElementById('dashboard-trend-chart');
                if (!canvas || typeof Chart === 'undefined' || !trend) { return; }

                var labels = trend.map(function (d) {
                    return new Date(d.date + 'T00:00:00').toLocaleDateString(locale === 'ar' ? 'ar-SA' : 'en-US', { weekday: 'short' });
                });

                var ctx = canvas.getContext('2d');
                var salesGradient = ctx.createLinearGradient(0, 0, 0, 256);
                salesGradient.addColorStop(0, 'rgba(20,86,232,.28)');
                salesGradient.addColorStop(1, 'rgba(20,86,232,0)');
                var purchasesGradient = ctx.createLinearGradient(0, 0, 0, 256);
                purchasesGradient.addColorStop(0, 'rgba(245,129,30,.24)');
                purchasesGradient.addColorStop(1, 'rgba(245,129,30,0)');

                if (trendChart) { trendChart.destroy(); }
                trendChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: @json(__('messages.dashboard_legend_sales')),
                                data: trend.map(function (d) { return d.sales; }),
                                borderColor: '#1456E8',
                                backgroundColor: salesGradient,
                                pointBackgroundColor: '#1456E8',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                borderWidth: 2.5,
                                tension: 0.4,
                                fill: true,
                            },
                            {
                                label: @json(__('messages.dashboard_legend_purchases')),
                                data: trend.map(function (d) { return d.purchases; }),
                                borderColor: '#F5811E',
                                backgroundColor: purchasesGradient,
                                pointBackgroundColor: '#F5811E',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                borderWidth: 2.5,
                                tension: 0.4,
                                fill: true,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        animation: { duration: 900, easing: 'easeOutQuart' },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0F1B4C',
                                padding: 10,
                                cornerRadius: 8,
                                titleFont: { weight: 'bold' },
                            },
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#F1F3F8' } },
                            x: { grid: { display: false } },
                        },
                    },
                });
            }

            function renderRatioChart(customers, suppliers) {
                var canvas = document.getElementById('dashboard-ratio-chart');
                if (!canvas || typeof Chart === 'undefined') { return; }

                if (ratioChart) { ratioChart.destroy(); }
                ratioChart = new Chart(canvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: [@json(__('messages.dashboard_legend_customers')), @json(__('messages.dashboard_legend_suppliers'))],
                        datasets: [{
                            data: [customers || 0, suppliers || 0],
                            backgroundColor: ['#1456E8', '#F5811E'],
                            hoverBackgroundColor: ['#0F3FBE', '#C9600C'],
                            borderWidth: 3,
                            borderColor: '#fff',
                            hoverOffset: 8,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        animation: { animateScale: true, animateRotate: true, duration: 900, easing: 'easeOutQuart' },
                        plugins: {
                            legend: { display: false },
                            tooltip: { backgroundColor: '#0F1B4C', padding: 10, cornerRadius: 8 },
                        },
                    },
                });
            }

            /**
             * تحميل كل أرقام الشاشة (مع فلتر الفرع لو مختار) - بتتنادى مرة
             * أول ما الصفحة تفتح، وبعدين كل مرة يتغيّر فيها فلتر الفرع فوق.
             */
            function loadStats(branchId) {
                document.querySelectorAll('[data-stat]').forEach(function (el) {
                    if (!el.classList.contains('dash-skeleton') && !el.classList.contains('dash-skeleton--light')) {
                        el.dataset.prevValue = (el.textContent || '0').replace(/,/g, '');
                    }
                });

                var url = @json(route('dashboard.stats'));
                if (branchId) {
                    url += '?branch_id=' + encodeURIComponent(branchId);
                }

                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(function (res) {
                        if (!res.ok) { throw new Error('bad response'); }
                        return res.json();
                    })
                    .then(function (data) {
                        document.getElementById('dashboard-stats-error')?.classList.add('hidden');

                        document.querySelectorAll('[data-stat]').forEach(function (el) {
                            var key = el.getAttribute('data-stat');
                            if (!(key in data)) { return; }
                            var isMoney = el.getAttribute('data-format') === 'money';
                            el.classList.remove('dash-skeleton', 'dash-skeleton--light');
                            animateValue(el, Number(data[key] || 0), isMoney);
                        });

                        renderTrendChart(data.sales_purchases_trend);
                        renderRatioChart(data.customers_count, data.suppliers_count);
                        renderTopList('dashboard-top-employees', data.top_employees_today, '#1456E8');
                        renderTopList('dashboard-top-branches', data.top_branches_today, '#6B2FD6');

                        var branchesTitle = document.getElementById('dashboard-top-branches-title');
                        if (branchesTitle) {
                            branchesTitle.textContent = data.top_branches_mode === 'products'
                                ? @json(__('transport.dash_top_trucks_today'))
                                : @json(__('messages.dashboard_top_branches'));
                        }

                        renderTable('dashboard-recent-sales', data.recent_sales, { name: 'customer' });
                        renderTable('dashboard-recent-purchases', data.recent_purchases, { name: 'supplier' });

                        // شارة "بتعرض بيانات فرع كذا" - بتتأكد دايمًا (مش بس
                        // لما يكون فيه فرع مختار) إن المستخدم واثق إن كل
                        // الشاشة فعلاً بتتفلتر زي ما هو متوقع.
                        var chip = document.getElementById('dashboard-viewing-chip');
                        if (chip) {
                            if (data.selected_branch_name) {
                                chip.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-[#1456E8] inline-block"></span>' +
                                    @json(__('messages.dashboard_viewing_branch')) + ': <b>' + data.selected_branch_name + '</b>';
                                chip.className = 'inline-flex items-center gap-1.5 text-xs font-medium text-[#1456E8] bg-[#1456E8]/10 rounded-full px-3 py-1.5';
                            } else {
                                chip.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>' +
                                    @json(__('messages.dashboard_viewing_all_branches'));
                                chip.className = 'inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 rounded-full px-3 py-1.5';
                            }
                        }

                        // لون الرصيد النقدي بيبان بلون مختلف لو سالب (نادر
                        // لكن ممكن يحصل لو حساب خزينة اتسجل عليه أكتر مما فيه).
                        var cashEl = document.getElementById('dashboard-cash-balance');
                        if (cashEl) {
                            cashEl.classList.toggle('text-rose-600', Number(data.current_cash_balance) < 0);
                            cashEl.classList.toggle('text-[#0F1B4C]', Number(data.current_cash_balance) >= 0);
                        }

                        // صافي الحركة النقدية اليوم (سندات القبض - سندات
                        // الصرف): أخضر لو موجب، أحمر لو سالب (يعني الصرف
                        // اليوم كان أكتر من القبض).
                        var netMovementEl = document.getElementById('dashboard-net-movement');
                        if (netMovementEl) {
                            netMovementEl.classList.toggle('text-rose-600', Number(data.today_net_cash_movement) < 0);
                            netMovementEl.classList.toggle('text-emerald-600', Number(data.today_net_cash_movement) >= 0);
                            netMovementEl.classList.toggle('text-[#0F1B4C]', false);
                        }
                    })
                    .catch(function () {
                        document.querySelectorAll('.dash-skeleton, .dash-skeleton--light').forEach(function (el) {
                            el.textContent = '-';
                            el.classList.remove('dash-skeleton', 'dash-skeleton--light');
                        });
                        document.getElementById('dashboard-stats-error')?.classList.remove('hidden');
                    });
            }

            loadStats('');

            var branchFilter = document.getElementById('dashboard-branch-filter');
            if (branchFilter) {
                branchFilter.addEventListener('change', function () {
                    loadStats(branchFilter.value);
                });
            }
        });
    </script>
</x-app-layout>
