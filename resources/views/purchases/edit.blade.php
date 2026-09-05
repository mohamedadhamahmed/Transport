<x-app-layout>
    {{-- TomSelect - لتحسين قايمة اختيار المورد (بحث + إضافة عنصر جديد ديناميكيًا).
         نفس الأنماط المستخدمة في purchases/create.blade.php بالظبط. --}}
    <style>
        .ts-wrapper {
            position: relative;
            width: 100%;
        }
        .ts-wrapper.single .ts-control {
            display: flex;
            align-items: center;
            width: 100%;
            box-sizing: border-box;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #fff;
            min-height: 42px;
            padding-inline-start: 0.75rem;
            padding-inline-end: 1.75rem;
            padding-block: 0.5rem;
            font-size: 0.875rem;
            color: rgb(17 24 39);
            cursor: pointer;
            overflow: hidden;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .ts-wrapper.single.focus .ts-control,
        .ts-wrapper.single.dropdown-active .ts-control {
            border-color: #1456E8;
            box-shadow: 0 0 0 1px #1456E8;
        }

        .ts-wrapper.single .ts-control::after {
            content: "";
            position: absolute;
            inset-inline-end: 0.85rem;
            top: 50%;
            width: 0.4rem;
            height: 0.4rem;
            border-inline-end: 1.5px solid rgb(156 163 175);
            border-block-end: 1.5px solid rgb(156 163 175);
            transform: translateY(-70%) rotate(45deg);
            pointer-events: none;
        }

        .ts-control input {
            color: inherit;
            font-size: inherit;
            background: transparent;
            min-width: 2rem;
            cursor: pointer;
        }

        .ts-control input::placeholder {
            color: rgb(156 163 175);
        }

        .ts-wrapper.single .ts-control > .item {
            color: rgb(17 24 39);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ts-dropdown {
            z-index: 9999;
            margin-top: 0.25rem;
            background-color: #fff;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            box-shadow: 0 10px 25px -5px rgba(15, 27, 76, 0.18), 0 8px 10px -6px rgba(15, 27, 76, 0.12);
            overflow: hidden;
            font-size: 0.875rem;
            text-align: start;
        }

        .ts-dropdown .ts-dropdown-content {
            max-height: 15rem;
            overflow-y: auto;
        }

        .ts-dropdown .option,
        .ts-dropdown .no-results {
            white-space: normal;
            word-break: break-word;
            padding: 0.55rem 0.75rem;
            cursor: pointer;
        }

        .ts-dropdown .option.active,
        .ts-dropdown .option:hover {
            background-color: #1456E8;
            color: #fff;
        }

        .ts-hidden-accessible {
            border: 0 !important;
            clip: rect(0 0 0 0) !important;
            clip-path: inset(50%) !important;
            height: 1px !important;
            overflow: hidden !important;
            padding: 0 !important;
            position: absolute !important;
            width: 1px !important;
            white-space: nowrap !important;
        }
    </style>
    <div class="py-6" x-data="purchaseForm(@js($existingPurchaseData))">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- هيدر الصفحة بلون البراند الكحلي --}}
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h8.6a2 2 0 0 0 2-1.6L22 8H6" />
                            <circle cx="9" cy="21" r="1" />
                            <circle cx="17" cy="21" r="1" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('purchases.edit_purchase') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('purchases.purchase_no') }} #{{ $purchase->purchase_number ?? $purchase->id }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="open-tax-calculator-btn"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="2" width="16" height="20" rx="2" />
                            <path d="M8 6h8M8 10h.01M12 10h.01M16 10h.01M8 14h.01M12 14h.01M16 14h.01" />
                        </svg>
                        حاسبة الضريبة والخصم
                    </button>
                    <a href="{{ route('purchases.show', $purchase) }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                        {{ __('purchases.back_to_list') }}
                    </a>
                </div>
            </div>

            <form id="purchase-form" method="POST" action="{{ route('purchases.update', $purchase) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="items_json" id="items_json">
                {{-- بيانات الفاتورة الأساسية --}}
                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchases.supplier') }} *</label>
                            <div class="flex gap-2">
                                <select name="supplier_id" id="supplier_select" x-model="selectedSupplierId"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                                    <option value="">-</option>
                                    @foreach ($suppliers as $id => $name)
                                    <option value="{{ $id }}" selected>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchases.payment_method') }} *</label>
                            <select name="payment_account_id" x-model="paymentAccountId"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">{{ __('purchases.credit') }}</option>
                                <template x-for="acc in paymentAccounts" :key="acc.id">
                                    <option :value="acc.id" x-text="acc.name"></option>
                                </template>
                            </select>
                            <p class="text-xs text-gray-400 mt-1" x-show="paymentAccounts.length === 0" x-cloak>{{ __('purchases.no_payment_accounts_for_branch') }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchases.branch') }}</label>
                            {{-- لما تتغيّر، بتجيب حسابات الدفع (خزينة/بنك) الخاصة
                                 بالفرع المختار بس، وبتصفّر اختيار طريقة الدفع
                                 القديم عشان محدش يفضل واقف على حساب فرع تاني. --}}
                            <select name="branch_id" x-model="selectedBranchId" @change="loadPaymentAccounts()"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                                @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected($purchase->branch_id == $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchases.supplier_invoice_number') }}</label>
                            <input type="text" name="supplier_invoice_number" value="{{ old('supplier_invoice_number', $purchase->supplier_invoice_number) }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchases.issue_date') }}</label>
                            <input type="date" name="issue_date" value="{{ old('issue_date', optional($purchase->issue_date)->format('Y-m-d')) }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchases.warehouse_name') }}</label>
                            <input type="text" name="warehouse_name" value="{{ old('warehouse_name', $purchase->warehouse_name) }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchases.cost_center') }}</label>
                            <div class="flex gap-2">
                                <select name="cost_center_id" id="cost_center_select" x-model="costCenterId"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                    <option value="">-</option>
                                    @foreach ($costCenters as $costCenter)
                                    <option value="{{ $costCenter->id }}" @selected($purchase->cost_center_id == $costCenter->id)>{{ $costCenter->cost_center_ar }}</option>
                                    @endforeach
                                </select>
                                <button type="button" @click="costCenterModalOpen = true"
                                    class="px-3 rounded-lg bg-[#0F1B4C]/5 text-[#0F1B4C] hover:bg-[#0F1B4C]/10 transition text-sm whitespace-nowrap font-medium">
                                    + {{ __('purchases.add') }}
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchases.shipping_fee') }}</label>
                            <input type="number" step="0.01" min="0" name="shipping_fee" x-model.number="shippingFee"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchases.note') }}</label>
                            <input type="text" name="note" value="{{ old('note', $purchase->note) }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>
                </div>

                {{-- المرفقات الحالية (لو فيه) - عرض فقط، إضافة مرفقات جديدة تحت --}}
                @if ($purchase->attachments->isNotEmpty())
                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6 mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('purchases.attachments') }}</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach ($purchase->attachments as $attachment)
                        <a href="{{ $attachment->url() }}" target="_blank"
                           class="flex items-center gap-3 px-4 py-3 rounded-lg border border-gray-100 hover:border-[#1456E8]/30 hover:bg-[#1456E8]/5 transition">
                            <span class="w-9 h-9 shrink-0 rounded-lg bg-[#0F1B4C]/5 flex items-center justify-center text-[#0F1B4C]">
                                <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
                            </span>
                            <span class="text-sm text-gray-700 truncate">{{ $attachment->original_name ?? __('purchases.download_attachment') }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- إضافة مرفقات جديدة --}}
                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6 mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchases.attachments') }}</label>
                    <input type="file" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8] text-sm">
                    <p class="text-xs text-gray-400 mt-1">{{ __('purchases.attachments_hint') }}</p>
                </div>

                {{-- إضافة الأصناف --}}
                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6 mt-6">
                    <div class="flex items-start gap-3 mb-4 flex-wrap">
                        <div class="relative">
                            <input type="text" x-model="searchQuery" @input.debounce.300ms="searchProducts()"
                                placeholder="{{ __('purchases.search_product_placeholder') }}"
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
                                <rect x="3" y="4" width="18" height="16" rx="2" />
                                <path d="M3 9h18M8 4v16" />
                            </svg>
                            {{ __('purchases.choose_product') }}
                        </button>
                    </div>

                    {{-- أسعار الشراء السابقة للمنتج المختار حاليًا (لو موجودة) --}}
                    <div class="mb-4 bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4" x-show="costHistoryProductName" x-cloak>
                        <h4 class="font-semibold text-gray-800 text-sm mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#1456E8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M12 8v4l3 3" />
                                <circle cx="12" cy="12" r="9" />
                            </svg>
                            {{ __('purchases.previous_costs_for_product') }}: <span x-text="costHistoryProductName" class="text-[#0F1B4C]"></span>
                        </h4>
                        <div x-show="loadingCostHistory" class="text-xs text-gray-400">...</div>
                        <div class="overflow-x-auto" x-show="!loadingCostHistory && costHistory.length > 0">
                            <table class="min-w-full text-xs">
                                <thead>
                                    <tr class="text-gray-500">
                                        <th class="px-2 py-1 text-start">{{ __('purchases.cost_date') }}</th>
                                        <th class="px-2 py-1 text-start">{{ __('purchases.cost_supplier') }}</th>
                                        <th class="px-2 py-1 text-start">{{ __('purchases.cost_qty') }}</th>
                                        <th class="px-2 py-1 text-start">{{ __('purchases.cost_unit_price') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#0F1B4C]/5">
                                    <template x-for="c in costHistory" :key="c.date + c.unit_price + c.quantity">
                                        <tr>
                                            <td class="px-2 py-1 text-gray-600" x-text="c.date"></td>
                                            <td class="px-2 py-1 text-gray-600" x-text="c.supplier || '-'"></td>
                                            <td class="px-2 py-1 text-gray-600" x-text="c.quantity"></td>
                                            <td class="px-2 py-1 font-medium text-[#0F1B4C]" x-text="parseFloat(c.unit_price).toFixed(2)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        <div x-show="!loadingCostHistory && costHistory.length === 0" class="text-xs text-gray-400">
                            {{ __('purchases.no_previous_costs') }}
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-[#0F1B4C] text-white/80">
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.code') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.product') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.quantity') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.unit_price') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.sale_price') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.discount') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.tax') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.total') }}</th>
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
                                            <input type="number" step="0.01" min="0" x-model.number="item.sale_price"
                                                class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" x-model.number="item.discount_amount"
                                                class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                        </td>
                                        <td class="px-3 py-2">
                                            <select x-model.number="item.tax_rate" class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                                @foreach(\App\Models\Tax::orderBy('priority', 'asc')->where('is_active',1)->get() as $tax)
                                                <option value="{{ $tax->rate/100 }}" >
                                                    {{ $tax->rate }}%
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-3 py-2 font-semibold text-[#0F1B4C]" x-text="lineTotal(item).toFixed(2)"></td>
                                        <td class="px-3 py-2">
                                            <button type="button" @click="removeItem(index)"
                                                class="inline-flex items-center gap-1 text-red-600 hover:text-red-700 text-xs font-medium">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M6 7l1 13a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-13" />
                                                </svg>
                                                {{ __('purchases.remove') }}
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="items.length === 0">
                                    <td colspan="9" class="px-3 py-8 text-center text-gray-400">
                                        {{ __('purchases.no_items_yet') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('purchases.invoice_discount') }}</label>
                            <input type="number" step="0.01" min="0" name="invoice_level_discount" x-model.number="extraDiscount"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mt-6">
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">{{ __('purchases.subtotal') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="subtotal.toFixed(2)"></div>
                        </div>
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">{{ __('purchases.discount_total') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="discountTotal.toFixed(2)"></div>
                        </div>
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">{{ __('purchases.tax_total') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="taxTotal.toFixed(2)"></div>
                        </div>
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">{{ __('purchases.shipping') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="(parseFloat(shippingFee) || 0).toFixed(2)"></div>
                        </div>
                        <div class="rounded-lg p-4 text-center text-white bg-[#0F1B4C] relative overflow-hidden">
                            <span class="absolute inset-x-0 bottom-0 h-0.5 bg-[#F5811E]"></span>
                            <div class="text-xs text-white/50 mb-1">{{ __('purchases.grand_total') }}</div>
                            <div class="font-bold text-lg" x-text="grandTotal.toFixed(2)"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 mt-6">
                        <button type="button" @click="submitPurchase()" :disabled="isSubmitting"
                            class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting">{{ __('purchases.update_purchase') }}</span>
                            <span x-show="isSubmitting" x-cloak>{{ __('purchases.saving_please_wait') }}</span>
                        </button>
                        <a href="{{ route('purchases.show', $purchase) }}"
                            class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                            {{ __('purchases.cancel') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>

        @include('partials.tax-calculator-modal')

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
                        {{ __('purchases.add') }}
                    </button>
                    <button type="button" @click="costCenterModalOpen = false"
                        class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        {{ __('purchases.cancel') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- مودال اختيار منتج من قائمة كاملة (بحث + صفحات 20 منتج) --}}
        <div x-show="productPickerOpen" x-cloak
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl w-full max-w-7xl my-8 flex flex-col max-h-[90vh] shadow-2xl" @click.outside="productPickerOpen = false">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/10 bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] rounded-t-xl">
                    <h3 class="font-semibold text-white">{{ __('purchases.choose_product') }}</h3>
                    <button type="button" @click="productPickerOpen = false" class="text-white/60 hover:text-white transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-4 border-b border-gray-100">
                    <input type="text" x-model="pickerSearch" @input.debounce.300ms="loadProducts(1)"
                        placeholder="{{ __('purchases.search_product_placeholder') }}"
                        class="w-full max-w-md rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                </div>
                <div class="overflow-y-auto">
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0">
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">#</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.code') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.product') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.product_location') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.quantity') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.unit_price') }}</th>
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
                                            <span x-show="!isAdded(p.id)">+ {{ __('purchases.add') }}</span>
                                            <span x-show="isAdded(p.id)">✓ {{ __('purchases.added') }}</span>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="!pickerLoading && pickerProducts.length === 0">
                                <td colspan="7" class="px-3 py-8 text-center text-gray-400">
                                    {{ __('purchases.no_purchases_found') ?? '' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-between px-6 py-3 border-t border-gray-100 flex-wrap gap-2">
                    <span class="text-xs text-gray-400"
                        x-text="pickerTotal > 0 ? '{{ __('purchases.page_of_total') }}'.replace(':current', pickerPage).replace(':last', pickerLastPage).replace(':total', pickerTotal) : ''"></span>
                    <div class="flex gap-2">
                        <button type="button" @click="loadProducts(pickerPage - 1)" :disabled="pickerPage <= 1"
                            class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:hover:bg-white transition">
                            {{ __('purchases.previous') }}
                        </button>
                        <button type="button" @click="loadProducts(pickerPage + 1)" :disabled="pickerPage >= pickerLastPage"
                            class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:hover:bg-white transition">
                            {{ __('purchases.next') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        function purchaseForm(existingData = null) {
            return {
                isSubmitting: false,
                // معبّاة من بيانات فاتورة المشتريات الحالية (existingPurchaseData
                // من الكونترولر) - نفس شكل sourcePO في create.blade.php بالظبط.
                items: existingData ? existingData.items : [],
                searchQuery: '',
                searchResults: [],
                productPickerOpen: false,
                pickerSearch: '',
                pickerProducts: [],
                pickerPage: 1,
                pickerLastPage: 1,
                pickerTotal: 0,
                pickerLoading: false,
                addedProductIds: existingData ? existingData.items.map(i => i.product_id).filter(Boolean) : [],
                selectedSupplierId: existingData ? String(existingData.supplier_id) : '',
                supplierTomSelect: null,
                paymentAccountId: existingData && existingData.payment_account_id ? String(existingData.payment_account_id) : '',
                // قايمة حسابات الدفع (خزينة/بنك) الخاصة بالفرع المختار حاليًا
                // بس - بتتحمّل أول مرة من السيرفر (@js($paymentAccounts))
                // وبتتحدّث كل ما الفرع يتغيّر عن طريق loadPaymentAccounts().
                paymentAccounts: @js($paymentAccounts->map(fn($a) => ['id' => $a->id, 'name' => $a->name])->values()),
                selectedBranchId: '{{ $purchase->branch_id }}',
                costCenterId: existingData && existingData.cost_center_id ? String(existingData.cost_center_id) : '',
                costCenterModalOpen: false,
                newCostCenter: {
                    cost_center_ar: '',
                    cost_center_en: ''
                },
                shippingFee: existingData ? existingData.shipping_fee : 0,
                extraDiscount: existingData ? existingData.invoice_level_discount : 0,
                costHistory: [],
                costHistoryProductName: '',
                loadingCostHistory: false,
                init() {
                    const el = document.getElementById('supplier_select');
                    if (el && window.TomSelect) {
                        this.supplierTomSelect = new TomSelect(el, {
                            create: false,
                            allowEmptyOption: true,
                            placeholder: '-',
                            valueField: 'id',
                            labelField: 'text',
                            searchField: [],
                            load: (query, callback) => {
                                if (!query || query.length < 2) {
                                    callback();
                                    return;
                                }
                                fetch(`{{ route('suppliers.search') }}?q=` + encodeURIComponent(query))
                                    .then((res) => res.json())
                                    .then((json) => callback(json))
                                    .catch(() => callback());
                            },
                            onChange: (value) => {
                                this.selectedSupplierId = value;
                            },
                        });
                    }
                },
                // بتتنفذ لما تتغيّر قيمة الفرع - بتجيب حسابات الدفع (خزينة/بنك)
                // الخاصة بالفرع المختار بس من السيرفر، وبتصفّر اختيار طريقة
                // الدفع القديم عشان محدش يفضل واقف على حساب فرع تاني بالغلط.
                async loadPaymentAccounts() {
                    if (!this.selectedBranchId) {
                        this.paymentAccounts = [];
                        this.paymentAccountId = '';
                        return;
                    }
                    try {
                        const res = await fetch(`/purchases/payment-accounts/${this.selectedBranchId}`);
                        this.paymentAccounts = await res.json();
                    } catch (e) {
                        this.paymentAccounts = [];
                    }
                    this.paymentAccountId = '';
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
                        sale_price: parseFloat(p.sale_price) || 0,
                        discount_amount: 0,
                        tax_rate: {{ $defaultTaxRate }},
                    });
                    this.searchQuery = '';
                    this.searchResults = [];
                    this.loadCostHistory(p);
                },
                // بيتفتح لوحة "آخر أسعار الشراء" لنفس المنتج لما يتضاف لجدول
                // الأصناف - عشان تقدر تقارن السعر الجديد بالسعر القديم
                // قبل ما تأكد الفاتورة.
                async loadCostHistory(p) {
                    this.costHistoryProductName = p.name;
                    this.loadingCostHistory = true;
                    try {
                        const res = await fetch(`/purchases/product-cost-history/${p.id}`);
                        this.costHistory = await res.json();
                    } finally {
                        this.loadingCostHistory = false;
                    }
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
                submitPurchase() {
                    if (this.isSubmitting) {
                        return;
                    }
                    if (!this.selectedSupplierId) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('purchases.select_supplier_required')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('purchases.ok')),
                        });
                        return;
                    }
                    if (this.items.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('purchases.no_items_error')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('purchases.ok')),
                        });
                        return;
                    }
                    this.isSubmitting = true;
                    document.getElementById('items_json').value = JSON.stringify(this.items);
                    document.getElementById('purchase-form').submit();
                },
                async createCostCenter() {
                    if (!this.newCostCenter.cost_center_ar) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('purchases.enter_cost_center_name')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('purchases.ok')),
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
                        this.newCostCenter = {
                            cost_center_ar: '',
                            cost_center_en: ''
                        };
                    }
                },
            }
        }
    </script>
</x-app-layout>
