<x-app-layout>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- رأس الصفحة والعنوان -->
            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.stock_adjustments_title', [], 'تقرير تعديلات المخزون') }}</h2>
                </div>
                <a href="{{ route('reports.products.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.products.title', [], 'المنتجات والمخزون') }}
                </a>
            </div>

            <!-- الفلتر الخاص بالمنتج إن وجد -->
            @if($productFilter ?? null)
                <div class="dc-print-hide rounded-xl border border-[#1456E8]/20 bg-[#1456E8]/5 px-4 py-3 flex items-center justify-between flex-wrap gap-2">
                    <span class="text-sm text-[#0F1B4C]">
                        {{ __('reports.filtered_by_product', ['name' => $productFilter->name, 'code' => $productFilter->code ?? '-']) }}
                    </span>
                    <a href="{{ route('reports.stock_adjustments', request()->except('product_id')) }}"
                       class="text-xs font-medium text-[#1456E8] hover:underline">
                        {{ __('reports.clear_product_filter', [], 'إلغاء فلتر المنتج') }}
                    </a>
                </div>
            @endif

            <!-- عنوان للطباعة فقط -->
            <h2 class="dc-print-only text-xl font-bold text-center">
                {{ __('reports.stock_adjustments_title', [], 'تقرير تعديلات المخزون') }} 
                @if(isset($dateFrom) && isset($dateTo))
                    ({{ $dateFrom }} → {{ $dateTo }})
                @endif
            </h2>

            <!-- حاوية الجدول -->
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                <!-- الفلاتر والبحث (إن رغبت في دمجها عبر ملف الفلاتر الجزئي) -->
                @include('reports._filters', ['hasDateRange' => true, 'searchPlaceholder' => __('reports.search_product_or_reason', [], 'ابحث باسم المنتج أو السبب...')])

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('reports.product_code', [], 'كود المنتج') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.product_name', [], 'اسم المنتج') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.user', [], 'المستخدم') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.date', [], 'التاريخ والوقت') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.reason', [], 'السبب') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.old_quantity', [], 'الكمية القديمة') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.new_quantity', [], 'الكمية الجديدة') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.difference', [], 'الفارق') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($adjustments as $adj)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-[#0F1B4C] font-medium">{{ optional($adj->product)->code ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-gray-800">{{ optional($adj->product)->name ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-gray-600">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ optional($adj->user)->name ?? '---' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $adj->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-2.5 text-gray-600 max-w-xs truncate" title="{{ $adj->reason }}">
                                        {{ $adj->reason ?? '-' }}
                                    </td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format($adj->old_quantity, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end font-semibold text-[#0F1B4C]">{{ number_format($adj->new_quantity, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end font-bold">
                                        <span class="px-2 py-1 rounded-full text-xs {{ $adj->difference >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                                            {{ $adj->difference > 0 ? '+' . number_format($adj->difference, 2) : number_format($adj->difference, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-gray-400">
                                        {{ __('reports.no_adjustments_found', [], 'لا توجد سجلات تعديل مخزون مطابقة') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        
                        @if ($adjustments->hasPages() || $adjustments->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3" colspan="5">{{ __('reports.total_records', [], 'إجمالي السجلات') }}</td>
                                    <td class="px-4 py-3 text-end" colspan="3">{{ $adjustments->total() ?? count($adjustments) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                <!-- روابط التصفح (Pagination) إن وجدت -->
                @if (method_exists($adjustments, 'links'))
                    <div class="px-4 py-3 border-t border-gray-100">
                        {{ $adjustments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>