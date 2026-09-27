<x-app-layout>
    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 12h4l3 8 4-16 3 8h4" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('products.operations.title') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('products.operations.subtitle') }}</p>
                    </div>
                </div>
            </div>

            {{-- بطاقة بيانات المنتج - عشان المستخدم يتأكد إنه على المنتج الصح --}}
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-5 flex flex-wrap items-center gap-x-8 gap-y-2">
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">{{ __('products.name') }}</p>
                    <p class="font-semibold text-[#0F1B4C]">{{ $product->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">{{ __('products.operations.product_code') }}</p>
                    <p class="font-semibold text-gray-700">{{ $product->code ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">{{ __('products.operations.product_branch') }}</p>
                    <p class="font-semibold text-gray-700">{{ optional($product->branch)->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">{{ __('products.operations.current_stock') }}</p>
                    <p class="font-semibold {{ $product->stock_quantity <= ($product->low_stock_alert_quantity ?? 0) ? 'text-red-600' : 'text-emerald-600' }}">
                        {{ $product->stock_quantity }}
                    </p>
                </div>
            </div>

            @if(!$canViewSales && !$canViewPurchases && !$canViewTransfers)
                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-10 text-center text-gray-400">
                    {{ __('products.operations.no_permission_any') }}
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    @if($canViewPurchases)
                        <a href="{{ route('reports.purchases.by-product', ['product_id' => $product->id]) }}" target="_blank"
                           class="group bg-white shadow-sm border border-gray-100 hover:border-[#F5811E]/40 hover:shadow-md sm:rounded-xl p-5 flex flex-col gap-3 transition">
                            <span class="w-10 h-10 rounded-lg bg-[#F5811E]/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 8 12 3 3 8l9 5 9-5ZM3 8v8l9 5m0-13v13m9-13v8l-9 5"/>
                                </svg>
                            </span>
                            <div>
                                <h3 class="font-semibold text-[#0F1B4C]">{{ __('products.operations.purchases_title') }}</h3>
                                <p class="text-xs text-gray-400 mt-1">{{ __('products.operations.purchases_desc') }}</p>
                            </div>
                            <span class="text-xs font-medium text-[#F5811E] group-hover:underline mt-auto">{{ __('products.operations.open_report') }} ←</span>
                        </a>
                    @endif

                    {{-- (تحويلات المخزون اتشالت) --}}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
