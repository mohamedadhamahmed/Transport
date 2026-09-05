<x-app-layout>
    @php
        // صلاحية عرض الربح: صاحب الشركة ممكن يمنع بعض الموظفين من شوفان
        // عمود/بطاقة الربح في شاشة تعديل عرض السعر (نفس الصلاحية مستخدمة
        // في الفواتير والتسليم وشاشة الإنشاء كمان).
        $canViewProfit = auth()->user()?->hasPermission('sensitive_data.view_profit');
    @endphp
    {{-- TomSelect - لتحسين قايمة اختيار العميل (بحث + إضافة عنصر جديد ديناميكيًا).
         نفس الأنماط المستخدمة بالظبط في شاشة "تسعيرة جديدة". --}}
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
    <div class="py-6" x-data="quotationForm({{ $maxDiscountPercent ?? 0 }})">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- هيدر الصفحة بلون البراند الكحلي --}}
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 7h6M9 11h6M9 15h3" />
                            <path d="M5 4h14a1 1 0 0 1 1 1v15l-3-2-3 2-3-2-3 2-3-2-3 2V5a1 1 0 0 1 1-1Z" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('quotations.edit_quotation') }} #{{ $quotation->id }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('quotations.title') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="customerModalOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="8" r="3.2" />
                            <path d="M3.5 19c0-3 2.5-5.2 5.5-5.2s5.5 2.2 5.5 5.2" />
                            <path d="M18 8v5M15.5 10.5h5" />
                        </svg>
                        {{ __('quotations.add_new_customer') }}
                    </button>
                    <button type="button" @click="productModalOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                        {{ __('quotations.new_product') }}
                    </button>
                    <button type="button" id="open-tax-calculator-btn"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="2" width="16" height="20" rx="2" />
                            <path d="M8 6h8M8 10h.01M12 10h.01M16 10h.01M8 14h.01M12 14h.01M16 14h.01" />
                        </svg>
                        حاسبة الضريبة والخصم
                    </button>
                    <a href="{{ route('quotations.show', $quotation) }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                        {{ __('quotations.back_to_list') }}
                    </a>
                </div>
            </div>

            <form id="quotation-form" method="POST" action="{{ route('quotations.update', $quotation) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="items_json" id="items_json">
                {{-- بيانات التسعيرة الأساسية --}}
                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.customer') }} *</label>
                            <div class="flex gap-2">
                                <select name="customer_id" id="customer_select" x-model="selectedCustomerId" @change="loadCustomerHistory()"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                                    <option value="">-</option>
                                    @foreach ($customers as $id => $name)
                                    <option value="{{ $id }}" selected>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" @click="customerModalOpen = true"
                                    class="px-3 rounded-lg bg-[#0F1B4C]/5 text-[#0F1B4C] hover:bg-[#0F1B4C]/10 transition text-sm whitespace-nowrap font-medium">
                                    + {{ __('quotations.add_new_customer') }}
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.payment_method') }} *</label>
                            <select name="payment_method" x-model="paymentMethod"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                                <option value="cash">{{ __('quotations.cash') }}</option>
                                <option value="bank_transfer">{{ __('quotations.bank_transfer') }}</option>
                                <option value="card">{{ __('quotations.card') }}</option>
                                <option value="credit">{{ __('quotations.credit') }}</option>
                                <option value="split">{{ __('quotations.split') }}</option>
                            </select>
                        </div>
                    </div>
                    {{-- الدفع المقسّم (كاش/بنك) - بيظهر بس لو طريقة الدفع "split" --}}
                    <template x-if="paymentMethod === 'split'">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-[#0F1B4C]/5 p-4 rounded-lg border border-[#0F1B4C]/10 mt-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.cash_amount') }}</label>
                                <input type="number" step="0.01" min="0" name="cash_amount" x-model.number="cashAmount"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.bank_amount') }}</label>
                                <input type="number" step="0.01" min="0" name="bank_amount" x-model.number="bankAmount"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            </div>
                        </div>
                    </template>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.branch') }}</label>
                            <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-gray-600">
                                {{ $quotation->branch->name ?? '-' }}
                            </div>
                            <input type="hidden" name="branch_id" value="{{ $quotation->branch_id }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.tax') }}</label>
                            <select x-model.number="defaultTaxRate" @change="applyDefaultTaxRate()"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                @foreach(\App\Models\Tax::orderBy('priority', 'asc')->where('is_active',1)->get() as $tax)
                                <option value="{{ $tax->rate/100}}" >
                                    {{ $tax->name }} ({{ $tax->rate  }}%)
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.note') }}</label>
                            <input type="text" name="note" value="{{ old('note', $quotation->note) }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>
                </div>

                {{-- التسعيرات السابقة لنفس العميل - بتظهر تلقائي لما تختار
                     عميل، وبتوريكي كل التسعيرات القديمة بتاعته (ما عدا
                     التسعيرة اللي بنعدلها دلوقتي نفسها) عشان تقدر تراجع/
                     تقارن الأسعار قبل ما تعدّل. --}}
                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6 mt-6" x-show="selectedCustomerId" x-cloak>
                    <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#1456E8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 19.5V6a2 2 0 0 1 2-2h9l5 5v10.5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z" />
                            <path d="M14 4v4a1 1 0 0 0 1 1h4" />
                        </svg>
                        {{ __('quotations.previous_quotations_for_customer') }}
                    </h3>
                    <div x-show="loadingHistory" class="text-sm text-gray-400">...</div>
                    <div class="overflow-x-auto rounded-xl border border-gray-100" x-show="!loadingHistory && previousQuotations.length > 0">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500">
                                    <th class="px-3 py-2 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.quotation_no') }}</th>
                                    <th class="px-3 py-2 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.date') }}</th>
                                    <th class="px-3 py-2 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.status') }}</th>
                                    <th class="px-3 py-2 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.items_count') }}</th>
                                    <th class="px-3 py-2 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.grand_total') }}</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="q in previousQuotations" :key="q.id">
                                    <tr class="hover:bg-[#1456E8]/5 transition">
                                        <td class="px-3 py-2 font-medium text-gray-800" x-text="'#' + q.id"></td>
                                        <td class="px-3 py-2 text-gray-500" x-text="q.date"></td>
                                        <td class="px-3 py-2">
                                            <span class="px-2 py-1 rounded-full text-xs"
                                                :class="{
                                                      'bg-emerald-50 text-emerald-700': q.status === 'approved',
                                                      'bg-red-50 text-red-700': q.status === 'rejected',
                                                      'bg-amber-50 text-amber-700': q.status === 'pending',
                                                  }"
                                                x-text="q.status === 'approved' ? @json(__('quotations.status_approved')) : (q.status === 'rejected' ? @json(__('quotations.status_rejected')) : @json(__('quotations.status_pending')))"></span>
                                        </td>
                                        <td class="px-3 py-2 text-gray-500" x-text="q.items_count"></td>
                                        <td class="px-3 py-2 font-semibold text-[#0F1B4C]" x-text="parseFloat(q.grand_total).toFixed(2)"></td>
                                        <td class="px-3 py-2">
                                            <a :href="q.url" target="_blank" class="text-[#1456E8] text-xs font-medium hover:underline">{{ __('quotations.view') }}</a>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div x-show="!loadingHistory && previousQuotations.length === 0" class="text-sm text-gray-400 text-center py-4">
                        {{ __('quotations.no_previous_quotations') }}
                    </div>
                </div>

                {{-- إضافة الأصناف --}}
                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6 mt-6">
                    <div class="flex items-start gap-3 mb-4 flex-wrap">
                        <div class="relative">
                            <input type="text" x-model="searchQuery" @input.debounce.300ms="searchProducts()"
                                placeholder="{{ __('quotations.search_product_placeholder') }}"
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
                                        <span class="text-sm text-gray-500" x-text="p.sale_price"></span>
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
                            {{ __('quotations.choose_product') }}
                        </button>
                    </div>
                    <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-[#0F1B4C] text-white/80">
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.code') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.product') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.quantity') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.unit_price') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.price_with_tax') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.discount') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.tax') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.total') }}</th>
                                    @if ($canViewProfit)
                                        <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.profit_per_unit') }}</th>
                                    @endif
                                    <th class="px-3 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="hover:bg-[#1456E8]/5 transition">
                                        <td class="px-3 py-2 text-gray-400 text-xs" x-text="item.code || '-'"></td>
                                        <td class="px-3 py-2 font-medium text-gray-800 min-w-[600px] whitespace-normal" x-text="item.name"></td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0.01" x-model.number="item.quantity"
                                                class="w-20 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" x-model.number="item.unit_price"
                                                class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                        </td>
                                        <td class="px-3 py-2 text-gray-500" x-text="linePriceWithTax(item).toFixed(2)"></td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" x-model.number="item.discount_amount"
                                                class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                        </td>
                                        <td class="px-3 py-2 text-gray-500" x-text="formatTaxRate(item.tax_rate) + '%'"></td>
                                        <td class="px-3 py-2 font-semibold text-[#0F1B4C]" x-text="lineTotal(item).toFixed(2)"></td>
                                        @if ($canViewProfit)
                                            <td class="px-3 py-2" :class="lineProfit(item) < 0 ? 'text-red-600' : 'text-emerald-600'" x-text="lineProfit(item).toFixed(2)"></td>
                                        @endif
                                        <td class="px-3 py-2">
                                            <button type="button" @click="removeItem(index)"
                                                class="inline-flex items-center gap-1 text-red-600 hover:text-red-700 text-xs font-medium">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M6 7l1 13a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-13" />
                                                </svg>
                                                {{ __('quotations.remove') }}
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="items.length === 0">
                                    <td colspan="{{ $canViewProfit ? 10 : 9 }}" class="px-3 py-8 text-center text-gray-400">
                                        {{ __('quotations.no_items_yet') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.invoice_discount') }}</label>
                            <input type="number" step="0.01" min="0" name="invoice_level_discount" x-model.number="extraDiscount"
                                @change="checkDiscountLimit()"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.po_number') }}</label>
                            <input type="text" name="purchase_order_number" value="{{ old('purchase_order_number', $quotation->purchase_order_number) }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 {{ $canViewProfit ? 'md:grid-cols-5' : 'md:grid-cols-4' }} gap-4 mt-6">
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">{{ __('quotations.subtotal') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="subtotal.toFixed(2)"></div>
                        </div>
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">{{ __('quotations.discount_total') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="discountTotal.toFixed(2)"></div>
                        </div>
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">{{ __('quotations.tax_total') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="taxTotal.toFixed(2)"></div>
                        </div>
                        @if ($canViewProfit)
                            <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4 text-center">
                                <div class="text-xs text-gray-500 mb-1">{{ __('quotations.total_profit') }}</div>
                                <div class="font-semibold text-emerald-700" x-text="totalProfit.toFixed(2)"></div>
                            </div>
                        @endif
                        <div class="rounded-lg p-4 text-center text-white bg-[#0F1B4C] relative overflow-hidden">
                            <span class="absolute inset-x-0 bottom-0 h-0.5 bg-[#F5811E]"></span>
                            <div class="text-xs text-white/50 mb-1">{{ __('quotations.grand_total') }}</div>
                            <div class="font-bold text-lg" x-text="grandTotal.toFixed(2)"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 mt-6">
                        <button type="button" @click="submitQuotation()" :disabled="isSubmitting"
                            class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting">{{ __('quotations.update_quotation') }}</span>
                            <span x-show="isSubmitting" x-cloak>{{ __('quotations.saving_please_wait') }}</span>
                        </button>
                        <a href="{{ route('quotations.show', $quotation) }}"
                            class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                            {{ __('quotations.cancel') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>

        @include('partials.tax-calculator-modal')

        {{-- مودال إضافة عميل سريع (نفس مودال شاشة الفواتير) --}}
        <div x-show="customerModalOpen" x-cloak
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto">
            <div class="bg-white rounded-xl p-6 w-full max-w-2xl my-8" @click.outside="customerModalOpen = false">
                <h3 class="font-semibold text-lg text-gray-800 mb-4">{{ __('quotations.add_new_customer') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.product_name') }} *</label>
                        <input type="text" x-model="newCustomer.name"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.phone') }} *</label>
                        <input type="text" x-model="newCustomer.phone"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.email') }}</label>
                        <input type="email" x-model="newCustomer.email"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.company_name') }}</label>
                        <input type="text" x-model="newCustomer.company_name"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.tax_number') }}</label>
                        <input type="text" x-model="newCustomer.tax_number"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.crn') }}</label>
                        <input type="text" x-model="newCustomer.commercial_registration_number"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.credit_limit') }}</label>
                        <input type="number" step="0.01" min="0" x-model.number="newCustomer.credit_limit"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.notes') }}</label>
                        <input type="text" x-model="newCustomer.notes"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.street_name') }}</label>
                        <input type="text" x-model="newCustomer.street_name"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.building_number') }}</label>
                        <input type="text" x-model="newCustomer.building_number"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.plot_identification') }}</label>
                        <input type="text" x-model="newCustomer.plot_identification"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.postal_code') }}</label>
                        <input type="text" x-model="newCustomer.postal_code"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                </div>
                <div class="flex items-center gap-3 mt-6">
                    <button type="button" @click="createCustomer()"
                        class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                        {{ __('quotations.add') }}
                    </button>
                    <button type="button" @click="customerModalOpen = false"
                        class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        {{ __('quotations.cancel') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- مودال إضافة منتج سريع --}}
        <div x-show="productModalOpen" x-cloak
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto">
            <div class="bg-white rounded-xl p-6 w-full max-w-2xl my-8" @click.outside="productModalOpen = false">
                <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                    <h3 class="font-semibold text-lg text-gray-800">{{ __('quotations.quick_add_product') }}</h3>
                    <label class="flex items-center gap-2 text-sm font-medium text-red-600 cursor-pointer">
                        <input type="checkbox" x-model="translateEnabled"
                               @change="translateEnabled && newProduct.name ? translateProductName() : null"
                               class="rounded border-gray-300 text-[#1456E8] focus:ring-[#1456E8]">
                        تفعيل الترجمة
                    </label>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.product_name') }} *</label>
                        <input type="text" x-model="newProduct.name" @blur="translateEnabled ? translateProductName() : null"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.product_name_en') }}</label>
                        <input type="text" x-model="newProduct.name_en"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.code') }}</label>
                        <input type="text" x-model="newProduct.code"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.product_location') }}</label>
                        <input type="text" x-model="newProduct.location"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.unit') }}</label>
                        <input type="text" x-model="newProduct.unit"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.quantity') }}</label>
                        <input type="number" step="0.01" min="0" x-model.number="newProduct.stock_quantity"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.purchase_price') }}</label>
                        <input type="number" step="0.01" min="0" x-model.number="newProduct.purchase_price"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.sale_price') }}</label>
                        <input type="number" step="0.01" min="0" x-model.number="newProduct.sale_price"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.wholesale_price') }}</label>
                        <input type="number" step="0.01" min="0" x-model.number="newProduct.wholesale_price"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.low_stock_alert_quantity') }}</label>
                        <input type="number" step="1" min="0" x-model.number="newProduct.low_stock_alert_quantity"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.tax_value') }}</label>
                        <input type="number" step="0.01" min="0" x-model.number="newProduct.tax_value"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('quotations.notes') }}</label>
                        <input type="text" x-model="newProduct.notes"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                </div>
                <div class="flex items-center gap-3 mt-6">
                    <button type="button" @click="createProduct()"
                        class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                        {{ __('quotations.add') }}
                    </button>
                    <button type="button" @click="productModalOpen = false"
                        class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        {{ __('quotations.cancel') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- مودال اختيار منتج من قائمة كاملة (بحث + صفحات 20 منتج) --}}
        <div x-show="productPickerOpen" x-cloak
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl w-full max-w-7xl my-8 flex flex-col max-h-[90vh] shadow-2xl" @click.outside="productPickerOpen = false">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/10 bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] rounded-t-xl">
                    <h3 class="font-semibold text-white">{{ __('quotations.choose_product') }}</h3>
                    <button type="button" @click="productPickerOpen = false" class="text-white/60 hover:text-white transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-4 border-b border-gray-100">
                    <input type="text" x-model="pickerSearch" @input.debounce.300ms="loadProducts(1)"
                        placeholder="{{ __('quotations.search_product_placeholder') }}"
                        class="w-full max-w-md rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                </div>
                <div>
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0">
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">#</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.code') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.product') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.branch') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.product_location') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.quantity') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.purchase_price') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.unit_price') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.reference_number') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.notes') }}</th>
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
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200" x-text="p.branch_name || '-'"></span>
                                    </td>
                                    <td class="px-3 py-2 text-gray-500" x-text="p.location || '-'"></td>
                                    <td class="px-3 py-2">
                                        <span x-show="(p.stock_quantity ?? 0) <= 0" class="text-red-600 font-bold text-xs">{{ __('quotations.not_available') }}</span>
                                        <span x-show="(p.stock_quantity ?? 0) > 0" class="text-emerald-600 font-bold" x-text="p.stock_quantity"></span>
                                    </td>
                                    <td class="px-3 py-2 text-gray-500" x-text="(parseFloat(p.purchase_price) || 0).toFixed(2)"></td>
                                    <td class="px-3 py-2 text-gray-500" x-text="(parseFloat(p.sale_price) || 0).toFixed(2)"></td>
                                    <td class="px-3 py-2 text-gray-400 text-xs" x-text="p.reference_number ? p.reference_number.split('+').join(' - ') : '-'"></td>
                                    <td class="px-3 py-2 text-gray-400 text-xs" x-text="p.notes || '-'"></td>
                                    <td class="px-3 py-2">
                                        <button type="button" @click="addProduct(p); markAdded(p.id)"
                                            class="px-3 py-1.5 rounded-lg text-white text-xs font-medium transition whitespace-nowrap"
                                            :class="isAdded(p.id) ? 'bg-emerald-500' : 'bg-[#F5811E] hover:brightness-95'">
                                            <span x-show="!isAdded(p.id)">+ {{ __('quotations.add') }}</span>
                                            <span x-show="isAdded(p.id)">✓ {{ __('quotations.added') }}</span>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="!pickerLoading && pickerProducts.length === 0">
                                <td colspan="11" class="px-3 py-8 text-center text-gray-400">
                                    {{ __('quotations.no_products_found') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-between px-6 py-3 border-t border-gray-100 flex-wrap gap-2">
                    <span class="text-xs text-gray-400"
                        x-text="pickerTotal > 0 ? '{{ __('quotations.page_of_total') }}'.replace(':current', pickerPage).replace(':last', pickerLastPage).replace(':total', pickerTotal) : ''"></span>
                    <div class="flex gap-2">
                        <button type="button" @click="loadProducts(pickerPage - 1)" :disabled="pickerPage <= 1"
                            class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:hover:bg-white transition">
                            {{ __('quotations.previous') }}
                        </button>
                        <button type="button" @click="loadProducts(pickerPage + 1)" :disabled="pickerPage >= pickerLastPage"
                            class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:hover:bg-white transition">
                            {{ __('quotations.next') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        function quotationForm(maxDiscountPercent = 0) {
            return {
                maxDiscountPercent: parseFloat(maxDiscountPercent) || 0,
                isSubmitting: false,
                items: @json($existingItems),
                searchQuery: '',
                searchResults: [],
                customerModalOpen: false,
                productModalOpen: false,
                translateEnabled: false,
                productPickerOpen: false,
                pickerSearch: '',
                pickerProducts: [],
                pickerPage: 1,
                pickerLastPage: 1,
                pickerTotal: 0,
                pickerLoading: false,
                addedProductIds: [],
                selectedCustomerId: '{{ $quotation->customer_id }}',
                customerTomSelect: null,
                previousQuotations: [],
                loadingHistory: false,
                newCustomer: {
                    name: '',
                    phone: '',
                    email: '',
                    company_name: '',
                    tax_number: '',
                    commercial_registration_number: '',
                    credit_limit: 10000,
                    notes: '',
                    district: '',
                    street_name: '',
                    building_number: '',
                    plot_identification: '',
                    postal_code: '',
                },
                newProduct: {
                    name: '',
                    name_en: '',
                    code: '',
                    location: '',
                    unit: '',
                    stock_quantity: 0,
                    purchase_price: 0,
                    sale_price: 0,
                    wholesale_price: 0,
                    low_stock_alert_quantity: 0,
                    tax_value: 0,
                    notes: '',
                },
                paymentMethod: '{{ $quotation->payment_method }}',
                cashAmount: {{ (float) ($quotation->cash_amount ?? 0) }},
                bankAmount: {{ (float) ($quotation->bank_amount ?? 0) }},
                extraDiscount: {{ (float) ($quotation->invoice_level_discount ?? 0) }},
                defaultTaxRate: {{ $defaultTaxRate }},
                init() {
                    const el = document.getElementById('customer_select');
                    if (el && window.TomSelect) {
                        this.customerTomSelect = new TomSelect(el, {
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
                                fetch(`{{ route('customers.search') }}?q=` + encodeURIComponent(query))
                                    .then((res) => res.json())
                                    .then((json) => callback(json))
                                    .catch(() => callback());
                            },
                            onChange: (value) => {
                                this.selectedCustomerId = value;
                                this.loadCustomerHistory();
                            },
                        });
                    }
                    // نجيب التسعيرات السابقة للعميل الحالي على طول (من غير
                    // ما ننتظر المستخدم يغيّر العميل يدويًا) عشان اللوحة
                    // تظهر مليانة من أول ما الصفحة تفتح.
                    if (this.selectedCustomerId) {
                        this.loadCustomerHistory();
                    }
                },
                // بتتنفذ لما تختار عميل - بتجيب كل التسعيرات السابقة بتاعته
                // (مهما كان المنتج) وتعرضها في اللوحة اللي فوق جدول الأصناف.
                // بنستثني التسعيرة اللي بنعدلها دلوقتي نفسها من القايمة.
                async loadCustomerHistory() {
                    if (!this.selectedCustomerId) {
                        this.previousQuotations = [];
                        return;
                    }
                    this.loadingHistory = true;
                    try {
                        const res = await fetch(`/quotations/customer-history/${this.selectedCustomerId}?exclude={{ $quotation->id }}`);
                        this.previousQuotations = await res.json();
                    } finally {
                        this.loadingHistory = false;
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
                        unit_price: parseFloat(p.sale_price) || 0,
                        purchase_price: parseFloat(p.purchase_price) || 0,
                        discount_amount: 0,
                        tax_rate: this.defaultTaxRate,
                    });
                    this.searchQuery = '';
                    this.searchResults = [];
                },
                applyDefaultTaxRate() {
                    this.items.forEach(i => {
                        i.tax_rate = this.defaultTaxRate;
                    });
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
                linePriceWithTax(item) {
                    return (parseFloat(item.unit_price) || 0) * (1 + (parseFloat(item.tax_rate) || 0));
                },
                lineProfit(item) {
                    return (parseFloat(item.unit_price) || 0) - (parseFloat(item.purchase_price) || 0);
                },
                // بيرجع نسبة الضريبة كنص منسّق لمنزلتين عشريتين بعد التقريب
                // عشان نتجنب مشاكل الفاصلة العشرية في JavaScript (0.14 * 100
                // ممكن تطلع 14.000000000000002 بدل 14 بالظبط).
                formatTaxRate(rate) {
                    const value = Math.round(((parseFloat(rate) || 0) * 100) * 100) / 100;
                    return (Number.isInteger(value) ? value.toFixed(0) : value.toFixed(2)).replace(/\.00$/, '');
                },
                get subtotal() {
                    return this.items.reduce((sum, i) => sum + this.lineSubtotal(i), 0);
                },
                get totalProfit() {
                    // إجمالي الربح = مجموع (الربح على القطعة × الكمية) لكل الأصناف
                    return this.items.reduce((sum, i) => sum + (this.lineProfit(i) * (parseFloat(i.quantity) || 0)), 0);
                },
                get taxTotal() {
                    return this.items.reduce((sum, i) => sum + this.lineTax(i), 0);
                },
                get discountTotal() {
                    const itemsDiscount = this.items.reduce((sum, i) => sum + (parseFloat(i.discount_amount) || 0), 0);
                    return itemsDiscount + (parseFloat(this.extraDiscount) || 0);
                },
                get grandTotal() {
                    const total = this.subtotal + this.taxTotal - (parseFloat(this.extraDiscount) || 0);
                    return total > 0 ? total : 0;
                },
                checkDiscountLimit() {
                    const limit = this.subtotal + this.taxTotal;
                    let discount = parseFloat(this.extraDiscount) || 0;
                    if (discount > limit) {
                        this.extraDiscount = limit;
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('quotations.discount_exceeds_total')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('quotations.ok')),
                        });
                        return false;
                    }
                    if (this.maxDiscountPercent > 0 && limit > 0) {
                        const maxAllowedDiscount = limit * (this.maxDiscountPercent / 100);
                        if (discount > maxAllowedDiscount + 0.001) {
                            this.extraDiscount = Math.round(maxAllowedDiscount * 100) / 100;
                            Swal.fire({
                                icon: 'warning',
                                title: 'أقصى نسبة خصم مسموح بيها ليك ' + this.maxDiscountPercent + '%',
                                confirmButtonColor: '#0F1B4C',
                                confirmButtonText: @json(__('quotations.ok')),
                            });
                            return false;
                        }
                    }
                    return true;
                },
                submitQuotation() {
                    if (this.isSubmitting) {
                        return;
                    }
                    if (!this.checkDiscountLimit()) {
                        return;
                    }
                    if (!this.selectedCustomerId) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('quotations.select_customer_required')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('quotations.ok')),
                        });
                        return;
                    }
                    if (this.items.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('quotations.no_items_error')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('quotations.ok')),
                        });
                        return;
                    }
                    this.isSubmitting = true;
                    document.getElementById('items_json').value = JSON.stringify(this.items);
                    document.getElementById('quotation-form').submit();
                },
                async createCustomer() {
                    if (!this.newCustomer.name || !this.newCustomer.phone) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('quotations.enter_customer_name_phone')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('quotations.ok')),
                        });
                        return;
                    }
                    const res = await fetch(`{{ route('invoices.customers.quick') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(this.newCustomer),
                    });
                    const data = await res.json();
                    if (data.id) {
                        if (this.customerTomSelect) {
                            this.customerTomSelect.addOption({
                                id: String(data.id),
                                text: data.name
                            });
                            this.customerTomSelect.addItem(String(data.id));
                        } else {
                            const select = document.getElementById('customer_select');
                            const opt = document.createElement('option');
                            opt.value = data.id;
                            opt.text = data.name;
                            opt.selected = true;
                            select.add(opt);
                        }
                        this.selectedCustomerId = String(data.id);
                        this.customerModalOpen = false;
                        this.loadCustomerHistory();
                        this.newCustomer = {
                            name: '',
                            phone: '',
                            email: '',
                            company_name: '',
                            tax_number: '',
                            commercial_registration_number: '',
                            credit_limit: 10000,
                            notes: '',
                            district: '',
                            street_name: '',
                            building_number: '',
                            plot_identification: '',
                            postal_code: '',
                        };
                    }
                },
                async translateProductName() {
                    if (!this.newProduct.name) {
                        return;
                    }
                    try {
                        const res = await fetch(`{{ route('products.translate') }}?text=` + encodeURIComponent(this.newProduct.name));
                        const data = await res.json();
                        if (data && data.translated) {
                            this.newProduct.name_en = data.translated;
                        }
                    } catch (e) {
                        // نتجاهل أي خطأ شبكة/ترجمة بهدوء - الحقل هيفضل قابل للتعديل يدويًا
                    }
                },
                async createProduct() {
                    if (!this.newProduct.name) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('quotations.complete_product_data')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('quotations.ok')),
                        });
                        return;
                    }
                    const branchId = document.querySelector('input[name="branch_id"]').value;
                    const res = await fetch(`{{ route('invoices.products.quick') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            ...this.newProduct,
                            branch_id: branchId
                        }),
                    });
                    const data = await res.json();
                    if (data.id) {
                        this.addProduct(data);
                        this.productModalOpen = false;
                        this.newProduct = {
                            name: '',
                            name_en: '',
                            code: '',
                            location: '',
                            unit: '',
                            stock_quantity: 0,
                            purchase_price: 0,
                            sale_price: 0,
                            wholesale_price: 0,
                            low_stock_alert_quantity: 0,
                            tax_value: 0,
                            notes: '',
                        };
                    }
                },
            }
        }
    </script>
</x-app-layout>
