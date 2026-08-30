<x-app-layout>
    {{-- TomSelect - لتحسين قايمة اختيار المورد (بحث + إضافة عنصر جديد ديناميكيًا) --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        .ts-wrapper.single .ts-control {
            border-radius: 0.5rem;
            border-color: #d1d5db;
            background-color: #fff;
            min-height: 42px;
            padding: 0.5rem 0.75rem;
        }
        .ts-wrapper.single.focus .ts-control {
            border-color: #1456E8;
            box-shadow: 0 0 0 1px #1456E8;
        }
        .ts-dropdown {
            z-index: 9999;
            background-color: #fff;
            border-radius: 0.5rem;
            border-color: #d1d5db;
            box-shadow: 0 10px 25px -5px rgba(15, 27, 76, 0.18), 0 8px 10px -6px rgba(15, 27, 76, 0.12);
            overflow: hidden;
        }
        .ts-dropdown .option,
        .ts-dropdown .no-results {
            white-space: normal;
            word-break: break-word;
            padding: 0.55rem 0.75rem;
        }
        .ts-dropdown .active {
            background-color: #1456E8;
            color: #fff;
        }
    </style>
    <div class="py-6" x-data="purchaseOrderForm()">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- هيدر الصفحة بلون البراند الكحلي --}}
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="6" y="3" width="12" height="18" rx="1"/><path d="M9 8h6M9 12h6M9 16h4"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('purchase_orders.new_purchase_order') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('purchase_orders.title') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="supplierModalOpen = true"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="8" r="3.2"/><path d="M3.5 19c0-3 2.5-5.2 5.5-5.2s5.5 2.2 5.5 5.2"/><path d="M18 8v5M15.5 10.5h5"/>
                        </svg>
                        {{ __('purchase_orders.add_new_supplier') }}
                    </button>
                    <a href="{{ route('purchase-orders.index') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                        {{ __('purchase_orders.back_to_list') }}
                    </a>
                </div>
            </div>

            <form id="purchase-order-form" method="POST" action="{{ route('purchase-orders.store') }}">
                @csrf
                <input type="hidden" name="items_json" id="items_json">
                {{-- بيانات الأمر الأساسية --}}
                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchase_orders.supplier') }} *</label>
                            <div class="flex gap-2">
                                <select name="supplier_id" id="supplier_select" x-model="selectedSupplierId"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                                    <option value="">-</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" @click="supplierModalOpen = true"
                                        class="px-3 rounded-lg bg-[#0F1B4C]/5 text-[#0F1B4C] hover:bg-[#0F1B4C]/10 transition text-sm whitespace-nowrap font-medium">
                                    + {{ __('purchase_orders.add_new_supplier') }}
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchase_orders.branch') }}</label>
                            <select name="branch_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(auth()->user()->branch_id == $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchase_orders.issue_date') }}</label>
                            <input type="date" name="issue_date" value="{{ old('issue_date', now()->toDateString()) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchase_orders.warehouse_name') }}</label>
                            <input type="text" name="warehouse_name" value="{{ old('warehouse_name') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchase_orders.cost_center') }}</label>
                            <div class="flex gap-2">
                                <select name="cost_center_id" id="cost_center_select" x-model="costCenterId"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                    <option value="">-</option>
                                    @foreach ($costCenters as $costCenter)
                                        <option value="{{ $costCenter->id }}">{{ $costCenter->cost_center_ar }}</option>
                                    @endforeach
                                </select>
                                <button type="button" @click="costCenterModalOpen = true"
                                        class="px-3 rounded-lg bg-[#0F1B4C]/5 text-[#0F1B4C] hover:bg-[#0F1B4C]/10 transition text-sm whitespace-nowrap font-medium">
                                    + {{ __('purchase_orders.add') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchase_orders.shipping_fee') }}</label>
                            <input type="number" step="0.01" min="0" name="shipping_fee" x-model.number="shippingFee"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchase_orders.note') }}</label>
                            <input type="text" name="note" value="{{ old('note') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>
                </div>

                {{-- إضافة الأصناف --}}
                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6 mt-6">
                    <div class="flex items-start gap-3 mb-4 flex-wrap">
                        <div class="relative">
                            <input type="text" x-model="searchQuery" @input.debounce.300ms="searchProducts()"
                                   placeholder="{{ __('purchase_orders.search_product_placeholder') }}"
                                   class="w-full max-w-md rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <div x-show="searchResults.length > 0" x-cloak
                                 class="absolute z-10 mt-1 w-full max-w-md bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-y-auto">
                                <template x-for="p in searchResults" :key="p.id">
                                    <button type="button" @click="addProduct(p)"
                                            class="w-full text-start px-4 py-2 hover:bg-[#1456E8]/5 flex items-center justify-between border-b border-gray-50 last:border-0">
                                        <span>
                                            <span class="font-medium text-gray-800" x-text="p.name"></span>
                                            <span class="text-xs text-gray-400" x-text="p.code ? ' (' + p.code + ')' : ''"></span>
                                        </span>
                                        <span class="text-sm text-gray-500" x-text="p.purchase_price"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                        <button type="button" @click="openProductPicker()"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white text-sm font-medium bg-[#0F1B4C] hover:bg-[#0F1B4C]/90 transition whitespace-nowrap">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18M8 4v16"/>
                            </svg>
                            {{ __('purchase_orders.choose_product') }}
                        </button>
                    </div>
                    <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-[#0F1B4C] text-white/80">
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.code') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.product') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.quantity') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.unit_price') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.discount') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.tax') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.total') }}</th>
                                    <th class="px-3 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="hover:bg-[#1456E8]/5 transition">
                                        <td class="px-3 py-2 text-gray-400 text-xs" x-text="item.code || '-'"></td>
                                        <td class="px-3 py-2 font-medium text-gray-800 min-w-[400px] whitespace-normal" x-text="item.name"></td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0.01" x-model.number="item.quantity"
                                                   class="w-20 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" x-model.number="item.unit_price"
                                                   class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" x-model.number="item.discount_amount"
                                                   class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                        </td>
                                        <td class="px-3 py-2">
                                            <select x-model.number="item.tax_rate" class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                                <option value="0.15">15%</option>
                                                <option value="0">0%</option>
                                            </select>
                                        </td>
                                        <td class="px-3 py-2 font-semibold text-[#0F1B4C]" x-text="lineTotal(item).toFixed(2)"></td>
                                        <td class="px-3 py-2">
                                            <button type="button" @click="removeItem(index)"
                                                    class="inline-flex items-center gap-1 text-red-600 hover:text-red-700 text-xs font-medium">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M6 7l1 13a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-13"/>
                                                </svg>
                                                {{ __('purchase_orders.remove') }}
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="items.length === 0">
                                    <td colspan="8" class="px-3 py-8 text-center text-gray-400">
                                        {{ __('purchase_orders.no_items_yet') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchase_orders.invoice_discount') }}</label>
                            <input type="number" step="0.01" min="0" name="invoice_level_discount" x-model.number="extraDiscount"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mt-6">
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">{{ __('purchase_orders.subtotal') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="subtotal.toFixed(2)"></div>
                        </div>
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">{{ __('purchase_orders.discount_total') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="discountTotal.toFixed(2)"></div>
                        </div>
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">{{ __('purchase_orders.tax_total') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="taxTotal.toFixed(2)"></div>
                        </div>
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">{{ __('purchase_orders.shipping') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="(parseFloat(shippingFee) || 0).toFixed(2)"></div>
                        </div>
                        <div class="rounded-lg p-4 text-center text-white bg-[#0F1B4C] relative overflow-hidden">
                            <span class="absolute inset-x-0 bottom-0 h-0.5 bg-[#F5811E]"></span>
                            <div class="text-xs text-white/50 mb-1">{{ __('purchase_orders.grand_total') }}</div>
                            <div class="font-bold text-lg" x-text="grandTotal.toFixed(2)"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 mt-6">
                        <button type="button" @click="submitPurchaseOrder()" :disabled="isSubmitting"
                                class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting">{{ __('purchase_orders.save_purchase_order') }}</span>
                            <span x-show="isSubmitting" x-cloak>{{ __('purchase_orders.saving_please_wait') }}</span>
                        </button>
                        <a href="{{ route('purchase-orders.index') }}"
                           class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                            {{ __('purchase_orders.cancel') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- مودال إضافة مورد سريع (نفس مودال شاشة فاتورة المشتريات) --}}
        <div x-show="supplierModalOpen" x-cloak
             class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto">
            <div class="bg-white rounded-xl p-6 w-full max-w-2xl my-8" @click.outside="supplierModalOpen = false">
                <h3 class="font-semibold text-lg text-gray-800 mb-4">{{ __('purchase_orders.add_new_supplier') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchases.supplier_name') }} *</label>
                        <input type="text" x-model="newSupplier.name"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchases.phone') }}</label>
                        <input type="text" x-model="newSupplier.phone"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchases.email') }}</label>
                        <input type="email" x-model="newSupplier.email"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                </div>
                <div class="flex items-center gap-3 mt-6">
                    <button type="button" @click="createSupplier()"
                            class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                        {{ __('purchase_orders.add') }}
                    </button>
                    <button type="button" @click="supplierModalOpen = false"
                            class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        {{ __('purchase_orders.cancel') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- مودال إضافة مركز تكلفة جديد --}}
        <div x-show="costCenterModalOpen" x-cloak
             class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto">
            <div class="bg-white rounded-xl p-6 w-full max-w-md my-8" @click.outside="costCenterModalOpen = false">
                <h3 class="font-semibold text-lg text-gray-800 mb-4">{{ __('purchases.add_new_cost_center') }}</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchases.cost_center_name_ar') }} *</label>
                        <input type="text" x-model="newCostCenter.cost_center_ar"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchases.cost_center_name_en') }}</label>
                        <input type="text" x-model="newCostCenter.cost_center_en"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                </div>
                <div class="flex items-center gap-3 mt-6">
                    <button type="button" @click="createCostCenter()"
                            class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                        {{ __('purchase_orders.add') }}
                    </button>
                    <button type="button" @click="costCenterModalOpen = false"
                            class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        {{ __('purchase_orders.cancel') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- مودال اختيار منتج من قائمة كاملة --}}
        <div x-show="productPickerOpen" x-cloak
             class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl w-full max-w-7xl my-8 flex flex-col max-h-[90vh] shadow-2xl" @click.outside="productPickerOpen = false">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/10 bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] rounded-t-xl">
                    <h3 class="font-semibold text-white">{{ __('purchase_orders.choose_product') }}</h3>
                    <button type="button" @click="productPickerOpen = false" class="text-white/60 hover:text-white transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-6 py-4 border-b border-gray-100">
                    <input type="text" x-model="pickerSearch" @input.debounce.300ms="loadProducts(1)"
                           placeholder="{{ __('purchase_orders.search_product_placeholder') }}"
                           class="w-full max-w-md rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                </div>
                <div class="overflow-y-auto">
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0">
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">#</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.code') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.product') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.product_location') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.quantity') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.unit_price') }}</th>
                                <th class="px-3 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <template x-for="(p, idx) in pickerProducts" :key="p.id">
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-3 py-2 text-gray-400" x-text="(pickerPage - 1) * 20 + idx + 1"></td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100" x-text="p.code || '-'"></span>
                                    </td>
                                    <td class="px-3 py-2 font-medium text-gray-800 min-w-[220px] whitespace-normal" x-text="p.name"></td>
                                    <td class="px-3 py-2 text-gray-500" x-text="p.location || '-'"></td>
                                    <td class="px-3 py-2 text-gray-500" x-text="p.stock_quantity ?? 0"></td>
                                    <td class="px-3 py-2 text-gray-500" x-text="(parseFloat(p.purchase_price) || 0).toFixed(2)"></td>
                                    <td class="px-3 py-2">
                                        <button type="button" @click="addProduct(p); markAdded(p.id)"
                                                class="px-3 py-1.5 rounded-lg text-white text-xs font-medium transition whitespace-nowrap"
                                                :class="isAdded(p.id) ? 'bg-emerald-500' : 'bg-[#F5811E] hover:brightness-95'">
                                            <span x-show="!isAdded(p.id)">+ {{ __('purchase_orders.add') }}</span>
                                            <span x-show="isAdded(p.id)">✓ {{ __('purchase_orders.added') }}</span>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="!pickerLoading && pickerProducts.length === 0">
                                <td colspan="7" class="px-3 py-8 text-center text-gray-400">
                                    {{ __('purchase_orders.no_purchase_orders_found') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-between px-6 py-3 border-t border-gray-100 flex-wrap gap-2">
                    <span class="text-xs text-gray-400"
                          x-text="pickerTotal > 0 ? '{{ __('purchase_orders.page_of_total') }}'.replace(':current', pickerPage).replace(':last', pickerLastPage).replace(':total', pickerTotal) : ''"></span>
                    <div class="flex gap-2">
                        <button type="button" @click="loadProducts(pickerPage - 1)" :disabled="pickerPage <= 1"
                                class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:hover:bg-white transition">
                            {{ __('purchase_orders.previous') }}
                        </button>
                        <button type="button" @click="loadProducts(pickerPage + 1)" :disabled="pickerPage >= pickerLastPage"
                                class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:hover:bg-white transition">
                            {{ __('purchase_orders.next') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        function purchaseOrderForm() {
            return {
                isSubmitting: false,
                items: [],
                searchQuery: '',
                searchResults: [],
                supplierModalOpen: false,
                costCenterModalOpen: false,
                newCostCenter: { cost_center_ar: '', cost_center_en: '' },
                costCenterId: '',
                productPickerOpen: false,
                pickerSearch: '',
                pickerProducts: [],
                pickerPage: 1,
                pickerLastPage: 1,
                pickerTotal: 0,
                pickerLoading: false,
                addedProductIds: [],
                selectedSupplierId: '',
                supplierTomSelect: null,
                shippingFee: 0,
                extraDiscount: 0,
                newSupplier: { name: '', phone: '', email: '' },
                init() {
                    const el = document.getElementById('supplier_select');
                    if (el && window.TomSelect) {
                        this.supplierTomSelect = new TomSelect(el, {
                            create: false,
                            allowEmptyOption: true,
                            placeholder: '-',
                            onChange: (value) => {
                                this.selectedSupplierId = value;
                            },
                        });
                    }
                },
                async searchProducts() {
                    if (this.searchQuery.trim().length < 1) {
                        this.searchResults = [];
                        return;
                    }
                    const res = await fetch(`{{ route('invoices.products.search') }}?q=` + encodeURIComponent(this.searchQuery));
                    this.searchResults = await res.json();
                },
                addProduct(p) {
                    this.items.push({
                        product_id: p.id,
                        name: p.name,
                        code: p.code,
                        quantity: 1,
                        unit_price: parseFloat(p.purchase_price) || 0,
                        discount_amount: 0,
                        tax_rate: 0.15,
                    });
                    this.searchQuery = '';
                    this.searchResults = [];
                },
                removeItem(index) {
                    const removed = this.items[index];
                    this.items.splice(index, 1);
                    if (removed && removed.product_id) {
                        this.addedProductIds = this.addedProductIds.filter(id => id !== removed.product_id);
                    }
                },
                openProductPicker() {
                    this.productPickerOpen = true;
                    this.pickerSearch = '';
                    this.loadProducts(1);
                },
                async loadProducts(page) {
                    if (page < 1) return;
                    this.pickerLoading = true;
                    try {
                        const res = await fetch(`{{ route('invoices.products.pick') }}?q=` + encodeURIComponent(this.pickerSearch) + `&page=` + page);
                        const data = await res.json();
                        this.pickerProducts = data.data;
                        this.pickerPage = data.current_page;
                        this.pickerLastPage = data.last_page;
                        this.pickerTotal = data.total;
                    } finally {
                        this.pickerLoading = false;
                    }
                },
                isAdded(id) {
                    return this.addedProductIds.includes(id);
                },
                markAdded(id) {
                    if (!this.addedProductIds.includes(id)) this.addedProductIds.push(id);
                },
                lineSubtotal(item) {
                    return ((parseFloat(item.unit_price) || 0) * (parseFloat(item.quantity) || 0)) - (parseFloat(item.discount_amount) || 0);
                },
                lineTax(item) {
                    return this.lineSubtotal(item) * (parseFloat(item.tax_rate) || 0);
                },
                lineTotal(item) {
                    return this.lineSubtotal(item) + this.lineTax(item);
                },
                get subtotal() {
                    return this.items.reduce((sum, i) => sum + this.lineSubtotal(i), 0);
                },
                get taxTotal() {
                    return this.items.reduce((sum, i) => sum + this.lineTax(i), 0);
                },
                get discountTotal() {
                    const itemsDiscount = this.items.reduce((sum, i) => sum + (parseFloat(i.discount_amount) || 0), 0);
                    return itemsDiscount + (parseFloat(this.extraDiscount) || 0);
                },
                get grandTotal() {
                    const netAfterDiscount = this.subtotal + this.taxTotal - (parseFloat(this.extraDiscount) || 0);
                    const total = (netAfterDiscount > 0 ? netAfterDiscount : 0) + (parseFloat(this.shippingFee) || 0);
                    return total > 0 ? total : 0;
                },
                submitPurchaseOrder() {
                    if (this.isSubmitting) {
                        return;
                    }
                    if (!this.selectedSupplierId) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('purchase_orders.select_supplier_required')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('purchase_orders.ok')),
                        });
                        return;
                    }
                    if (this.items.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('purchase_orders.no_items_error')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('purchase_orders.ok')),
                        });
                        return;
                    }
                    this.isSubmitting = true;
                    document.getElementById('items_json').value = JSON.stringify(this.items);
                    document.getElementById('purchase-order-form').submit();
                },
                async createSupplier() {
                    if (!this.newSupplier.name) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('purchases.enter_supplier_name_phone')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('purchase_orders.ok')),
                        });
                        return;
                    }
                    const res = await fetch(`{{ route('purchases.suppliers.quick') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(this.newSupplier),
                    });
                    const data = await res.json();
                    if (data.id) {
                        if (this.supplierTomSelect) {
                            this.supplierTomSelect.addOption({ value: String(data.id), text: data.name });
                            this.supplierTomSelect.addItem(String(data.id));
                        } else {
                            const select = document.getElementById('supplier_select');
                            const opt = document.createElement('option');
                            opt.value = data.id;
                            opt.text = data.name;
                            opt.selected = true;
                            select.add(opt);
                        }
                        this.selectedSupplierId = String(data.id);
                        this.supplierModalOpen = false;
                        this.newSupplier = { name: '', phone: '', email: '' };
                    }
                },
                async createCostCenter() {
                    if (!this.newCostCenter.cost_center_ar) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('purchases.enter_cost_center_name')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('purchase_orders.ok')),
                        });
                        return;
                    }
                    const res = await fetch(`{{ route('purchases.cost-centers.quick') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(this.newCostCenter),
                    });
                    const data = await res.json();
                    if (data.id) {
                        const select = document.getElementById('cost_center_select');
                        const opt = document.createElement('option');
                        opt.value = data.id;
                        opt.text = data.name;
                        select.add(opt);
                        this.costCenterId = String(data.id);
                        this.costCenterModalOpen = false;
                        this.newCostCenter = { cost_center_ar: '', cost_center_en: '' };
                    }
                },
            }
        }
    </script>
</x-app-layout>
