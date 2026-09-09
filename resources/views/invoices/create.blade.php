<x-app-layout>
    @php
        // صلاحية عرض الربح: صاحب الشركة ممكن يمنع بعض الموظفين من شوفان
        // عمود/بطاقة الربح في شاشة إنشاء الفاتورة (نفس الصلاحية مستخدمة
        // في التسليم وعروض الأسعار كمان).
        $canViewProfit = auth()->user()?->hasPermission('sensitive_data.view_profit');
        // زرار "العمليات" في مودال اختيار منتج بيفتح صفحة فيها روابط
        // لتقارير المبيعات/المشتريات/حركة المخزون الخاصة بالمنتج ده -
        // بيظهر بس لو المستخدم عنده صلاحية على تقرير واحد على الأقل من
        // التلاتة دول (نفس صلاحيات مركز التقارير بالظبط، من غير ما نخترع
        // صلاحية جديدة).
        $canViewProductOperations = auth()->user()?->hasPermission('reports_sales.by_product')
            || auth()->user()?->hasPermission('reports_purchases.by_product')
            || auth()->user()?->hasPermission('reports_products.stock_transfers');
    @endphp
    {{-- TomSelect - لتحسين قايمة اختيار العميل (بحث + إضافة عنصر جديد ديناميكيًا).
         ⚠️ منحملش قالب Bootstrap 5 الجاهز (tom-select.bootstrap5.min.css) لإن
         المشروع كله Tailwind من غير Bootstrap - كان بيطلع شبه فاضي من غير
         حدود/خلفية. الأنماط تحت مستقلة بالكامل ومطابقة لشكل حقول الفورم. --}}
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

        /* خلفية صريحة (بيضا) + z-index عالي عشان الدروب داون يطلع واضح
           فوق أي عنصر تاني جنبه (زي حقل "ملاحظات" اللي كان بيظهر شفاف
           جواه قبل كده) من غير ما نحتاج ننقله لمكان تاني في الصفحة. */
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
    @php
    // توكن عشوائي بيتولد مرة واحدة بس لما الصفحة تتحمل، وبيفضل ثابت
    // في حقل مخفي جوه الفورم حتى لو المستخدم دوس زرار الحفظ أكتر من
    // مرة - السيرفر (store()) بيستخدمه عشان يرفض أي تكرار لنفس
    // التوكن، فمينفعش نفس الفاتورة تتسجل مرتين حتى لو حصل ضغط مزدوج.
    $submissionToken = (string) \Illuminate\Support\Str::uuid();

    // لو الصفحة اتفتحت من قايمة "المسودات السابقة" (?draft_id=xx)،
    // بنجهز بيانات المسودة عشان نمررها لـ Alpine (invoiceForm) تملى
    // بيها الفورم كامل: العميل، طريقة الدفع، الخصم، والبنود.
    $draftForJs = $draft ? [
    'id' => $draft->id,
    'customer_id' => $draft->customer_id,
    'payment_method' => $draft->payment_method,
    'cash_amount' => $draft->cash_amount,
    'bank_amount' => $draft->bank_amount,
    'extra_discount' => $draft->invoice_level_discount,
    'items' => collect($draft->items ?? [])->map(function ($item) {
    return [
    'product_id' => $item['product_id'] ?? null,
    'name' => $item['name'] ?? '',
    'code' => $item['code'] ?? null,
    'quantity' => $item['quantity'] ?? 1,
    'unit_price' => $item['unit_price'] ?? 0,
    'purchase_price' => $item['purchase_price'] ?? 0,
    'discount_amount' => $item['discount_amount'] ?? 0,
    'tax_rate' => $item['tax_rate'] ?? $defaultTaxRate,
    ];
    })->values(),
    ] : null;
    @endphp
    {{-- بيانات المسودة (لو موجودة) بتتحط هنا في <script type="application/json">
         مش جوه attribute الـ x-data مباشرة. السبب: @json() بيسيب علامات
         تنصيص (") عادية جوه الناتج بتاعه لأي نص فيه بيانات (زي اسم منتج
         أو عميل)، ولو حطينا الناتج ده جوه x-data="...@json(...)..." -
         أول علامة تنصيص جوه الـ JSON بتقفل الـ attribute بتاع الـ HTML
         قبل الأوان، وكل اللي بعدها بيتقطع - وده بالظبط اللي كان بيمنع
         العميل/الأصناف/طريقة الدفع من التعبية لما بتفتحي مسودة فيها
         بيانات حقيقية (وده كمان سبب إن المشكلة ماكانتش بتظهر لما مفيش
         مسودة، لإن @json(null) بيطلع "null" من غير أي علامات تنصيص). --}}
    <script type="application/json" id="draft-invoice-data">
        @json($draftForJs)
    </script>
    <script>
        window.__draftInvoiceData = (function() {
            var el = document.getElementById('draft-invoice-data');
            if (!el) return null;
            try {
                return JSON.parse(el.textContent);
            } catch (e) {
                return null;
            }
        })();
    </script>
    <div class="py-6" x-data="invoiceForm({{ $maxDiscountPercent ?? 0 }}, window.__draftInvoiceData)">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- هيدر الصفحة بلون البراند الكحلي --}}
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="3" width="14" height="18" rx="1.5" />
                            <path d="M8.5 8h7M8.5 12h7M8.5 16h4" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('invoices.new_invoice') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('invoices.title') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('invoices.drafts.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5V6a2 2 0 0 1 2-2h9l5 5v10.5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z" />
                            <path d="M14 4v4a1 1 0 0 0 1 1h4" />
                        </svg>
                        {{ __('invoices.previous_drafts') }}
                    </a>
                    <button type="button" @click="customerModalOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="8" r="3.2" />
                            <path d="M3.5 19c0-3 2.5-5.2 5.5-5.2s5.5 2.2 5.5 5.2" />
                            <path d="M18 8v5M15.5 10.5h5" />
                        </svg>
                        {{ __('invoices.add_new_customer') }}
                    </button>
                    <button type="button" @click="productModalOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                        {{ __('invoices.new_product') }}
                    </button>
                    <button type="button" id="open-tax-calculator-btn"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="2" width="16" height="20" rx="2" />
                            <path d="M8 6h8M8 10h.01M12 10h.01M16 10h.01M8 14h.01M12 14h.01M16 14h.01" />
                        </svg>
                        حاسبة الضريبة والخصم
                    </button>
                    <a href="{{ route('invoices.index') }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                        {{ __('invoices.back_to_list') }}
                    </a>
                </div>
            </div>

            @if ($draft)
            <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-2.5 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5V6a2 2 0 0 1 2-2h9l5 5v10.5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z" />
                    <path d="M14 4v4a1 1 0 0 0 1 1h4" />
                </svg>
                {{ __('invoices.draft_loaded_notice') }}
            </div>
            @endif

            <form id="invoice-form" method="POST" action="{{ route('invoices.store') }}">
                @csrf
                <input type="hidden" name="items_json" id="items_json">
                <input type="hidden" name="is_finalized" id="is_finalized" value="1">
                <input type="hidden" name="draft_id" :value="draftId">
                {{-- توكن ثابت لكل تحميل صفحة (مش بيتغيّر لو دوستي حفظ أكتر من
                     مرة على نفس الصفحة) - السيرفر بيرفض أي تكرار لنفس التوكن
                     ده عشان يمنع تكرار الفاتورة لو حصل ضغط مزدوج على الزرار. --}}
                <input type="hidden" name="submission_token" value="{{ $submissionToken }}">
                {{-- بيانات الفاتورة الأساسية --}}
                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                    {{-- الصف الأول: اسم العميل ياخد نص عرض الصفحة (لإنه فيه بحث/إضافة)
                         وطريقة الدفع جنبه في النص التاني. --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.customer') }} *</label>
                            <div class="flex gap-2">
                                <select name="customer_id" id="customer_select" x-model="selectedCustomerId"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                                    <option value="">-</option>
                                    @foreach ($customers as $id => $name)
                                    <option value="{{ $id }}" selected>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" @click="customerModalOpen = true"
                                    class="px-3 rounded-lg bg-[#0F1B4C]/5 text-[#0F1B4C] hover:bg-[#0F1B4C]/10 transition text-sm whitespace-nowrap font-medium">
                                    + {{ __('invoices.add_new_customer') }}
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.payment_method') }} *</label>
                            <select name="payment_method" x-model="paymentMethod"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                                <option value="cash">{{ __('invoices.cash') }}</option>
                                <option value="bank_transfer">{{ __('invoices.bank_transfer') }}</option>
                                <option value="card">{{ __('invoices.card') }}</option>
                                <option value="credit">{{ __('invoices.credit') }}</option>
                                <option value="split">{{ __('invoices.split') }}</option>
                            </select>
                        </div>
                    </div>
                    {{-- الدفع المقسّم (كاش/بنك) - بيظهر بس لو طريقة الدفع "split" --}}
                    <template x-if="paymentMethod === 'split'">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-[#0F1B4C]/5 p-4 rounded-lg border border-[#0F1B4C]/10 mt-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.cash_amount') }}</label>
                                <input type="number" step="0.01" min="0" name="cash_amount" x-model.number="cashAmount"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.bank_amount') }}</label>
                                <input type="number" step="0.01" min="0" name="bank_amount" x-model.number="bankAmount"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            </div>
                        </div>
                    </template>
                    {{-- الصف الثاني: الفرع + نسبة الضريبة + الملاحظات في صف لوحدهم --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.branch') }}</label>
                            <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-gray-600">
                                {{ auth()->user()->branch->name ?? '-' }}
                            </div>
                            <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.tax') }}</label>
                            <select x-model.number="defaultTaxRate" @change="applyDefaultTaxRate()"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                @foreach(\App\Models\Tax::orderBy('priority', 'asc')->where('is_active',1)->get() as $tax)
                                <option value="{{ $tax->rate / 100 }}" >
                                   ({{ $tax->rate }}%)
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.note') }}</label>
                            <input type="text" name="note" value="{{ old('note', $draft->note ?? '') }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>
                </div>
                {{-- إضافة الأصناف --}}
                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6 mt-6">
                    <div class="flex items-start gap-3 mb-4 flex-wrap">
                        <div class="relative">
                            <input type="text" x-model="searchQuery" @input.debounce.300ms="searchProducts()"
                                placeholder="{{ __('invoices.search_product_placeholder') }}"
                                class="w-full max-w-md rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <div x-show="searchResults.length > 0" x-cloak
                                class="absolute z-10 mt-1 w-full max-w-md bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-y-auto">
                                <template x-for="p in searchResults" :key="p.id">
                                    <button type="button" @click="addProduct(p)"
                                        class="w-full text-start px-4 py-2 hover:bg-[#1456E8]/5 flex items-center justify-between border-b border-gray-50 last:border-0">
                                        <span>
                                            <span class="font-medium text-gray-800" x-text="p.name"></span>
                                            <span class="text-xs text-gray-400" x-text="p.code ? ' (' + p.code + ')' : ''"></span>
                                            <span x-show="selectedCustomerId && lastPricesByProduct[p.id] !== undefined"
                                                  class="inline-flex items-center ms-1 px-1.5 py-0.5 rounded-md text-[10px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-300 whitespace-nowrap"
                                                  x-text="'{{ __('invoices.last_price_to_customer') }}'.replace(':price', (parseFloat(lastPricesByProduct[p.id]) || 0).toFixed(2))"></span>
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
                            {{ __('invoices.choose_product') }}
                        </button>
                    </div>
                    <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-[#0F1B4C] text-white/80">
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.code') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.product') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.quantity') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.unit_price') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.price_with_tax') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.discount') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.tax') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.total') }}</th>
                                    @if ($canViewProfit)
                                        <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.profit_per_unit') }}</th>
                                    @endif
                                    <th class="px-3 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="hover:bg-[#1456E8]/5 transition">
                                        <td class="px-3 py-2 text-gray-400 text-xs" x-text="item.code || '-'"></td>
                                        <td class="px-3 py-2 min-w-[600px]">
                                            {{-- اسم الصنف بيتحط بالاسم الحقيقي من الكتالوج وقت الإضافة،
                                                 لكن قابل للتعديل بعد كده (زي الكمية/السعر بالظبط -
                                                 x-model بيتزامن تلقائيًا مع items_json وقت الحفظ من غير
                                                 أي منطق مزامنة إضافي) - عشان الكاشير يقدر يضيف ملاحظة
                                                 أو وصف مختلف يتحفظ ويتطبع بالظبط زي ما كتبه. --}}
                                            <input type="text" x-model="item.name"
                                                class="w-full rounded-lg border-gray-300 shadow-sm text-sm font-medium text-gray-800 focus:border-[#1456E8] focus:ring-[#1456E8]">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0.01" x-model.number="item.quantity"
                                                class="w-20 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" x-model.number="item.unit_price"
                                                class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                            {{-- تلميح بسيط لآخر سعر بيع اتسجل بيه هذا الصنف لنفس العميل
                                                 المختار - مرجع سريع للموظف وهو بيكتب السعر الفعلي، مش
                                                 قيمة بتتفرض عليه. --}}
                                            <p x-show="selectedCustomerId && lastPricesByProduct[item.product_id] !== undefined"
                                               class="text-[10px] text-emerald-700 mt-0.5 whitespace-nowrap"
                                               x-text="'{{ __('invoices.last_price_to_customer') }}'.replace(':price', (parseFloat(lastPricesByProduct[item.product_id]) || 0).toFixed(2))"></p>
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
                                                {{ __('invoices.remove') }}
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="items.length === 0">
                                    <td colspan="{{ $canViewProfit ? 10 : 9 }}" class="px-3 py-8 text-center text-gray-400">
                                        {{ __('invoices.no_items_yet') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    {{-- خصم إضافي على الفاتورة + رقم أمر الشراء --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.invoice_discount') }}</label>
                            <input type="number" step="0.01" min="0" name="invoice_level_discount" x-model.number="extraDiscount"
                                @change="checkDiscountLimit()"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.po_number') }}</label>
                            <input type="text" name="purchase_order_number" value="{{ old('purchase_order_number', $draft->purchase_order_number ?? '') }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>
                    {{-- الإجماليات --}}
                    <div class="grid grid-cols-2 {{ $canViewProfit ? 'md:grid-cols-5' : 'md:grid-cols-4' }} gap-4 mt-6">
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">{{ __('invoices.subtotal') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="subtotal.toFixed(2)"></div>
                        </div>
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">{{ __('invoices.discount_total') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="discountTotal.toFixed(2)"></div>
                        </div>
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">{{ __('invoices.tax_total') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="taxTotal.toFixed(2)"></div>
                        </div>
                        @if ($canViewProfit)
                            <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4 text-center">
                                <div class="text-xs text-gray-500 mb-1">{{ __('invoices.total_profit') }}</div>
                                <div class="font-semibold text-emerald-700" x-text="totalProfit.toFixed(2)"></div>
                            </div>
                        @endif
                        <div class="rounded-lg p-4 text-center text-white bg-[#0F1B4C] relative overflow-hidden">
                            <span class="absolute inset-x-0 bottom-0 h-0.5 bg-[#F5811E]"></span>
                            <div class="text-xs text-white/50 mb-1">{{ __('invoices.grand_total') }}</div>
                            <div class="font-bold text-lg" x-text="grandTotal.toFixed(2)"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 mt-6">
                        {{-- disabled + :disabled جوه Alpine عشان لو المستخدم دوس على
                             الزرار أكتر من مرة بسرعة (double click أو الفورم بطئ
                             شوية) الضغطة التانية متعملش حاجة خالص - ده كان بيسبب
                             تكرار الفاتورة (والقيود المحاسبية والخصم من المخزون
                             معاها) بنفس عدد الضغطات. --}}
                        <button type="button" @click="submitInvoice(false)" :disabled="isSubmitting"
                            class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting">{{ __('invoices.save_invoice') }}</span>
                            <span x-show="isSubmitting" x-cloak>{{ __('invoices.saving_please_wait') }}</span>
                        </button>
                        <button type="button" @click="submitInvoice(true)" :disabled="isSubmitting"
                            class="px-5 py-2 rounded-lg font-medium text-white bg-[#F5811E] hover:brightness-95 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting">{{ __('invoices.save_as_draft') }}</span>
                            <span x-show="isSubmitting" x-cloak>{{ __('invoices.saving_please_wait') }}</span>
                        </button>
                        <a href="{{ route('invoices.index') }}"
                            class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                            {{ __('invoices.cancel') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
        @include('partials.tax-calculator-modal')
        {{-- مودال إضافة عميل سريع --}}
<div x-show="customerModalOpen" x-cloak
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto">
    <div class="bg-white rounded-xl p-6 w-full max-w-2xl my-8" @click.outside="customerModalOpen = false">
        <h3 class="font-semibold text-lg text-gray-800 mb-4">{{ __('invoices.add_new_customer') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.name') }} *</label>
                <input type="text" x-model="newCustomer.name"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.phone') }} *</label>
                <input type="text" x-model="newCustomer.phone"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.email') }}</label>
                <input type="email" x-model="newCustomer.email"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.company_name') }}</label>
                <input type="text" x-model="newCustomer.company_name"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.tax_number') }}</label>
                <input type="text" x-model="newCustomer.tax_number"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.crn') }}</label>
                <input type="text" x-model="newCustomer.commercial_registration_number"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.credit_limit') }}</label>
                <input type="number" step="0.01" min="0" x-model.number="newCustomer.credit_limit"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.grace_period_days') }}</label>
                <input type="number" step="1" min="0" x-model.number="newCustomer.grace_period_days"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.notes') }}</label>
                <input type="text" x-model="newCustomer.notes"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            </div>

            <div class="md:col-span-3">
                <hr class="my-2 border-gray-100">
                <p class="text-xs font-semibold text-gray-500 mb-2">{{ __('invoices.national_address') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.city') }}</label>
                <input type="text" x-model="newCustomer.city"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.district') }}</label>
                <input type="text" x-model="newCustomer.district"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.street_name') }}</label>
                <input type="text" x-model="newCustomer.street_name"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.building_number') }}</label>
                <input type="text" x-model="newCustomer.building_number"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.plot_identification') }}</label>
                <input type="text" x-model="newCustomer.plot_identification"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.postal_code') }}</label>
                <input type="text" x-model="newCustomer.postal_code"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            </div>
        </div>
        <div class="flex items-center gap-3 mt-6">
            <button type="button" @click="createCustomer()"
                class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                {{ __('invoices.add') }}
            </button>
            <button type="button" @click="customerModalOpen = false"
                class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                {{ __('invoices.cancel') }}
            </button>
        </div>
    </div>
</div>


        {{-- مودال إضافة منتج سريع --}}
        <div x-show="productModalOpen" x-cloak
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto">
            <div class="bg-white rounded-xl p-6 w-full max-w-2xl my-8" @click.outside="productModalOpen = false">
                <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                    <h3 class="font-semibold text-lg text-gray-800">{{ __('invoices.quick_add_product') }}</h3>
                    <label class="flex items-center gap-2 text-sm font-medium text-red-600 cursor-pointer">
                        <input type="checkbox" x-model="translateEnabled"
                               @change="translateEnabled && newProduct.name ? translateProductName() : null"
                               class="rounded border-gray-300 text-[#1456E8] focus:ring-[#1456E8]">
                        تفعيل الترجمة
                    </label>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.product_name') }} *</label>
                        <input type="text" x-model="newProduct.name" @blur="translateEnabled ? translateProductName() : null"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.product_name_en') }}</label>
                        <input type="text" x-model="newProduct.name_en"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.code') }}</label>
                        <input type="text" x-model="newProduct.code"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.product_location') }}</label>
                        <input type="text" x-model="newProduct.location"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.unit') }}</label>
                        <input type="text" x-model="newProduct.unit"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.quantity') }}</label>
                        <input type="number" step="0.01" min="0" x-model.number="newProduct.stock_quantity"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.purchase_price') }}</label>
                        <input type="number" step="0.01" min="0" x-model.number="newProduct.purchase_price"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.sale_price') }}</label>
                        <input type="number" step="0.01" min="0" x-model.number="newProduct.sale_price"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.wholesale_price') }}</label>
                        <input type="number" step="0.01" min="0" x-model.number="newProduct.wholesale_price"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.low_stock_alert_quantity') }}</label>
                        <input type="number" step="1" min="0" x-model.number="newProduct.low_stock_alert_quantity"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.tax_value') }}</label>
                        <input type="number" step="0.01" min="0" x-model.number="newProduct.tax_value"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.notes') }}</label>
                        <input type="text" x-model="newProduct.notes"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                </div>
                <div class="flex items-center gap-3 mt-6">
                    <button type="button" @click="createProduct()"
                        class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                        {{ __('invoices.add') }}
                    </button>
                    <button type="button" @click="productModalOpen = false"
                        class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        {{ __('invoices.cancel') }}
                    </button>
                </div>
            </div>
        </div>
        {{-- مودال اختيار منتج من قائمة كاملة (بحث + صفحات 20 منتج، زي النظام القديم) --}}
        <div x-show="productPickerOpen" x-cloak
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl w-full max-w-7xl my-8 flex flex-col max-h-[90vh] shadow-2xl" @click.outside="productPickerOpen = false">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/10 bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] rounded-t-xl">
                    <h3 class="font-semibold text-white">{{ __('invoices.choose_product') }}</h3>
                    <button type="button" @click="productPickerOpen = false" class="text-white/60 hover:text-white transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-4 border-b border-gray-100">
                    <input type="text" x-model="pickerSearch" @input.debounce.300ms="loadProducts(1)"
                        placeholder="{{ __('invoices.search_product_placeholder') }}"
                        class="w-full max-w-md rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                </div>
                <div>
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0">
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">#</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.code') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.product') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.branch') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.product_location') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.quantity') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.purchase_price') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.unit_price') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.reference_number') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.notes') }}</th>
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
                                    <td class="px-3 py-2 font-medium text-gray-800 min-w-[220px] whitespace-normal">
                                        <span x-text="p.name"></span>
                                        {{-- بادچ "آخر سعر لهذا العميل" - بيظهر بس لو فيه عميل مختار
                                             وباع له هذا المنتج قبل كده (lastPricesByProduct بتتحمّل من
                                             invoices.products.last-prices وقت اختيار/تغيير العميل). --}}
                                        <span x-show="selectedCustomerId && lastPricesByProduct[p.id] !== undefined"
                                              class="inline-flex items-center mt-1 px-1.5 py-0.5 rounded-md text-[10px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-300 whitespace-nowrap"
                                              x-text="'{{ __('invoices.last_price_to_customer') }}'.replace(':price', (parseFloat(lastPricesByProduct[p.id]) || 0).toFixed(2))"></span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200" x-text="p.branch_name || '-'"></span>
                                    </td>
                                    <td class="px-3 py-2 text-gray-500" x-text="p.location || '-'"></td>
                                    <td class="px-3 py-2">
                                        <span x-show="(p.stock_quantity ?? 0) <= 0" class="text-red-600 font-bold text-xs">{{ __('invoices.not_available') }}</span>
                                        <span x-show="(p.stock_quantity ?? 0) > 0" class="text-emerald-600 font-bold" x-text="p.stock_quantity"></span>
                                    </td>
                                    <td class="px-3 py-2 text-gray-500" x-text="(parseFloat(p.purchase_price) || 0).toFixed(2)"></td>
                                    <td class="px-3 py-2 text-gray-500" x-text="(parseFloat(p.sale_price) || 0).toFixed(2)"></td>
                                    <td class="px-3 py-2 text-gray-400 text-xs" x-text="p.reference_number ? p.reference_number.split('+').join(' - ') : '-'"></td>
                                    <td class="px-3 py-2 text-gray-400 text-xs" x-text="p.notes || '-'"></td>
                                    <td class="px-3 py-2">
                                        <div class="flex flex-col gap-1.5 items-stretch">
                                            <button type="button" @click="addProduct(p); markAdded(p.id)"
                                                class="px-3 py-1.5 rounded-lg text-white text-xs font-medium transition whitespace-nowrap"
                                                :class="isAdded(p.id) ? 'bg-emerald-500' : 'bg-[#F5811E] hover:brightness-95'">
                                                <span x-show="!isAdded(p.id)">+ {{ __('invoices.add') }}</span>
                                                <span x-show="isAdded(p.id)">✓ {{ __('invoices.added') }}</span>
                                            </button>
                                            @if($canViewProductOperations)
                                                <button type="button" @click="openOperations(p)"
                                                    class="px-3 py-1.5 rounded-lg text-white text-xs font-medium bg-[#1456E8] hover:brightness-95 transition whitespace-nowrap">
                                                    {{ __('invoices.operations') }}
                                                </button>
                                            @endif
                                            <button type="button" x-show="(p.alternates_count ?? 0) > 0" x-cloak
                                                @click="openAlternates(p)"
                                                class="px-3 py-1.5 rounded-lg text-white text-xs font-medium bg-[#0F1B4C] hover:bg-[#0F1B4C]/90 transition whitespace-nowrap">
                                                {{ __('invoices.alternates') }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="!pickerLoading && pickerProducts.length === 0">
                                <td colspan="11" class="px-3 py-8 text-center text-gray-400">
                                    {{ __('invoices.no_products_found') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-between px-6 py-3 border-t border-gray-100 flex-wrap gap-2">
                    <span class="text-xs text-gray-400"
                        x-text="pickerTotal > 0 ? '{{ __('invoices.page_of_total') }}'.replace(':current', pickerPage).replace(':last', pickerLastPage).replace(':total', pickerTotal) : ''"></span>
                    <div class="flex gap-2">
                        <button type="button" @click="loadProducts(pickerPage - 1)" :disabled="pickerPage <= 1"
                            class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:hover:bg-white transition">
                            {{ __('invoices.previous') }}
                        </button>
                        <button type="button" @click="loadProducts(pickerPage + 1)" :disabled="pickerPage >= pickerLastPage"
                            class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:hover:bg-white transition">
                            {{ __('invoices.next') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- مودال "العمليات" - مبيعات/مشتريات/تحويلات منتج واحد مجمّعة في
             جدول واحد، بيتفتح فوق مودال اختيار منتج من غير أي navigation
             (زي طلب العميل: "حاجة محترفة" مش لينك بيفتح تاب جديد). --}}
        <div x-show="operationsModalOpen" x-cloak
            class="fixed inset-0 bg-black/60 flex items-center justify-center z-[60] p-4">
            <div class="bg-white rounded-xl w-full max-w-5xl my-8 flex flex-col max-h-[90vh] shadow-2xl" @click.outside="operationsModalOpen = false">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/10 bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] rounded-t-xl">
                    <h3 class="font-semibold text-white">
                        {{ __('invoices.operations') }} - <span x-text="operationsProductName"></span>
                    </h3>
                    <button type="button" @click="operationsModalOpen = false" class="text-white/60 hover:text-white transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('invoices.operation_type') }}</label>
                        <select x-model="operationsType" @change="loadOperations()"
                            class="rounded-lg border-gray-300 shadow-sm text-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value="all">{{ __('invoices.operation_type_all') }}</option>
                            <option value="sales">{{ __('products.operations.type_sales') }}</option>
                            <option value="purchases">{{ __('products.operations.type_purchases') }}</option>
                            <option value="transfers">{{ __('products.operations.type_transfers') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.date_from') }}</label>
                        <input type="date" x-model="operationsDateFrom" @change="loadOperations()"
                            class="rounded-lg border-gray-300 shadow-sm text-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.date_to') }}</label>
                        <input type="date" x-model="operationsDateTo" @change="loadOperations()"
                            class="rounded-lg border-gray-300 shadow-sm text-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                </div>
                <div class="overflow-y-auto">
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0">
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">#</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.invoice_number') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.product') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.date') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.operation_type') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.operation_entity') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.quantity') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.unit_price') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.operations') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <template x-for="(row, idx) in operationsRows" :key="idx">
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-3 py-2 text-gray-400" x-text="idx + 1"></td>
                                    <td class="px-3 py-2 text-gray-700" x-text="row.document_number || '-'"></td>
                                    <td class="px-3 py-2 font-medium text-gray-800" x-text="row.product_name"></td>
                                    <td class="px-3 py-2 text-gray-500" x-text="row.date || '-'"></td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                              :class="{
                                                  'bg-emerald-50 text-emerald-700 border border-emerald-100': row.type_key === 'sales',
                                                  'bg-[#F5811E]/10 text-[#F5811E] border border-[#F5811E]': row.type_key === 'purchases',
                                                  'bg-[#1456E8]/10 text-[#1456E8]': row.type_key === 'transfers',
                                              }" x-text="row.type_label"></span>
                                    </td>
                                    <td class="px-3 py-2 text-gray-600" x-text="row.entity_name || '-'"></td>
                                    <td class="px-3 py-2 text-gray-600" x-text="row.quantity"></td>
                                    <td class="px-3 py-2 text-gray-600" x-text="(parseFloat(row.price) || 0).toFixed(2)"></td>
                                    <td class="px-3 py-2 text-gray-300">-</td>
                                </tr>
                            </template>
                            <tr x-show="!operationsLoading && operationsRows.length === 0">
                                <td colspan="9" class="px-3 py-8 text-center text-gray-400">
                                    {{ __('invoices.no_operations_found') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-end px-6 py-3 border-t border-gray-100">
                    <button type="button" @click="operationsModalOpen = false"
                        class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        {{ __('invoices.cancel') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- مودال "البدائل" - منفصل تمامًا عن مودال العمليات - منتجات بديلة
             للمنتج المختار، كل واحد بزرار "إضافة" خاص بيه يضيفه هو نفسه
             للفاتورة بدل المنتج الأصلي. --}}
        <div x-show="alternatesModalOpen" x-cloak
            class="fixed inset-0 bg-black/60 flex items-center justify-center z-[60] p-4">
            <div class="bg-white rounded-xl w-full max-w-5xl my-8 flex flex-col max-h-[90vh] shadow-2xl" @click.outside="alternatesModalOpen = false">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/10 bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] rounded-t-xl">
                    <h3 class="font-semibold text-white">
                        {{ __('invoices.alternates') }} - <span x-text="alternatesProductName"></span>
                    </h3>
                    <button type="button" @click="alternatesModalOpen = false" class="text-white/60 hover:text-white transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="overflow-y-auto">
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0">
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.code') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.product') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.branch') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.quantity') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.unit_price') }}</th>
                                <th class="px-3 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <template x-for="p in alternatesRows" :key="p.id">
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100" x-text="p.code || '-'"></span>
                                    </td>
                                    <td class="px-3 py-2 font-medium text-gray-800 min-w-[220px] whitespace-normal" x-text="p.name"></td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200" x-text="p.branch_name || '-'"></span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span x-show="(p.stock_quantity ?? 0) <= 0" class="text-red-600 font-bold text-xs">{{ __('invoices.not_available') }}</span>
                                        <span x-show="(p.stock_quantity ?? 0) > 0" class="text-emerald-600 font-bold" x-text="p.stock_quantity"></span>
                                    </td>
                                    <td class="px-3 py-2 text-gray-500" x-text="(parseFloat(p.sale_price) || 0).toFixed(2)"></td>
                                    <td class="px-3 py-2">
                                        <button type="button" @click="addAlternateProduct(p)"
                                            class="px-3 py-1.5 rounded-lg text-white text-xs font-medium transition whitespace-nowrap"
                                            :class="isAdded(p.id) ? 'bg-emerald-500' : 'bg-[#F5811E] hover:brightness-95'">
                                            <span x-show="!isAdded(p.id)">+ {{ __('invoices.add') }}</span>
                                            <span x-show="isAdded(p.id)">✓ {{ __('invoices.added') }}</span>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="!alternatesLoading && alternatesRows.length === 0">
                                <td colspan="6" class="px-3 py-8 text-center text-gray-400">
                                    {{ __('invoices.no_alternates_found') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-end px-6 py-3 border-t border-gray-100">
                    <button type="button" @click="alternatesModalOpen = false"
                        class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        {{ __('invoices.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        function invoiceForm(maxDiscountPercent = 0, draftData = null) {
            return {
                // أقصى نسبة خصم مسموح بيها للموظف الحالي على الفرع بتاعه -
                // جاية من جدول employee_discount_settings (عمود max_discount)
                // ومتمررة من الكنترولر لصفحة الفاتورة.
                maxDiscountPercent: parseFloat(maxDiscountPercent) || 0,
                // بيانات المسودة (لو الصفحة اتفتحت من "المسودات السابقة") -
                // بتتقرا في init() تحت وتملى بيها كل حقول الفورم.
                draftData: draftData,
                draftId: draftData ? draftData.id : null,
                // true من أول ما submitInvoice() تتنفذ لحد ما الصفحة تتنقل
                // (submit عادي بيعمل full page reload) - أي ضغطة تانية على
                // الزرار وهو true بترجع فورًا من غير ما تعمل حاجة (خط دفاع
                // أول، والتوكن اللي في السيرفر خط الدفاع التاني للحالات
                // النادرة اللي الضغطتين بيوصلوا قبل ما الزرار يتعطل بصريًا).
                isSubmitting: false,
                items: [],
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
                selectedCustomerId: '',
                customerTomSelect: null,
                // آخر سعر بيع اتسجل بيه كل منتج للعميل المختار حاليًا -
                // {product_id: price}. بتتحمّل من invoices.products.last-prices
                // وقت اختيار/تغيير العميل ووقت تحميل صفحة منتجات جديدة في
                // مودال الاختيار (شوف refreshLastPrices تحت).
                lastPricesByProduct: {},
                // مودال "العمليات" (مبيعات/مشتريات/تحويلات منتج واحد) - مودال
                // ثاني بيتفتح فوق مودال اختيار منتج من غير أي navigation.
                operationsModalOpen: false,
                operationsProductId: null,
                operationsProductName: '',
                operationsRows: [],
                operationsLoading: false,
                operationsType: 'all',
                operationsDateFrom: '',
                operationsDateTo: '',
                // مودال "البدائل" - منفصل تمامًا عن مودال العمليات.
                alternatesModalOpen: false,
                alternatesProductName: '',
                alternatesRows: [],
                alternatesLoading: false,
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
                paymentMethod: 'cash',
                cashAmount: 0,
                bankAmount: 0,
                extraDiscount: 0,
                defaultTaxRate: {{ $defaultTaxRate }},
                // بتتنفذ أوتوماتيك لما الكومبوننت يتحمل - هنا بنركّب TomSelect
                // على قايمة العميل عشان يبقى فيه بحث، وبنحتفظ بالـ instance
                // في this.customerTomSelect عشان createCustomer() تقدر تضيفله
                // عنصر جديد بطريقة صحيحة (مش عن طريق التلاعب في الـ <select>
                // الأصلي مباشرة، لإن TomSelect بيغلفه ويخفيه).
                init() {
                    const el = document.getElementById('customer_select');
                    if (el && window.TomSelect) {
                        this.customerTomSelect = new TomSelect(el, {
                            create: false,
                            allowEmptyOption: true,
                            placeholder: '-',
                            // بحث Ajax حي بدل تحميل كل الـ 20 ألف عميل مرة
                            // واحدة في الصفحة - بيبعت الطلب لـ customers.search
                            // بعد أول حرفين، والعنصر المختار مسبقًا (لو فيه
                            // مسودة) بييجي جاهز كـ <option> واحدة من السيرفر.
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
                            // ملحوظة: مسيباش الدروب داون يتحط في الـ body
                            // (dropdownParent) لإن ده كان بيسبب مشكلة إن
                            // السكرول/الماوس بيفضل "عالق" في نفس مكان خانة
                            // العميل وميرجعش يسكرول الصفحة عادي. بدل كده
                            // بنسيبه جوه مكانه الطبيعي، وبنظبط الشكل
                            // بخلفية صريحة + z-index عالي (تحت في CSS)
                            // عشان يطلع فوق أي حاجة تانية جنبه من غير ما
                            // يعمل مشاكل في السكرول.
                            onChange: (value) => {
                                this.selectedCustomerId = value;
                                // العميل اتغيّر - نمسح الكاش القديم (كان لعميل
                                // تاني) ونجيب آخر أسعار العميل الجديد لكل
                                // المنتجات الظاهرة دلوقتي (مودال الاختيار +
                                // نتائج البحث السريع + الأصناف المضافة بالفعل).
                                this.lastPricesByProduct = {};
                                this.refreshLastPrices(this.visibleProductIds());
                            },
                        });
                    }

                    // لو فيه مسودة اتحملت (من قايمة "المسودات السابقة")،
                    // نملى بيها كل حقول الفورم: العميل، طريقة الدفع،
                    // مبالغ الدفع المقسّم، الخصم الإضافي، وكل البنود.
                    if (this.draftData) {
                        this.paymentMethod = this.draftData.payment_method || 'cash';
                        this.cashAmount = parseFloat(this.draftData.cash_amount) || 0;
                        this.bankAmount = parseFloat(this.draftData.bank_amount) || 0;
                        this.extraDiscount = parseFloat(this.draftData.extra_discount) || 0;
                        this.items = (this.draftData.items || []).map(i => ({
                            product_id: i.product_id,
                            name: i.name,
                            code: i.code,
                            quantity: parseFloat(i.quantity) || 1,
                            unit_price: parseFloat(i.unit_price) || 0,
                            purchase_price: parseFloat(i.purchase_price) || 0,
                            discount_amount: parseFloat(i.discount_amount) || 0,
                            tax_rate: parseFloat(i.tax_rate) || 0,
                        }));
                        if (this.draftData.customer_id) {
                            this.selectedCustomerId = String(this.draftData.customer_id);
                            if (this.customerTomSelect) {
                                this.customerTomSelect.setValue(String(this.draftData.customer_id));
                            }
                        }
                    }
                },
                async searchProducts() {
                    if (this.searchQuery.trim().length < 1) {
                        this.searchResults = [];
                        return;
                    }
                    const res = await fetch(`{{ route('invoices.products.search') }}?q=` + encodeURIComponent(this.searchQuery));
                    this.searchResults = await res.json();
                    this.refreshLastPrices(this.searchResults.map(p => p.id));
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
                    this.refreshLastPrices([p.id]);
                },
                // بيجمع كل الـ product_id الظاهرة دلوقتي على الشاشة (مودال
                // الاختيار + نتائج البحث السريع + الأصناف المضافة بالفعل)
                // عشان نجيب آخر سعر بيع لكل واحد منهم للعميل المختار.
                visibleProductIds() {
                    return [
                        ...this.pickerProducts.map(p => p.id),
                        ...this.searchResults.map(p => p.id),
                        ...this.items.map(i => i.product_id),
                    ];
                },
                // بيجيب آخر سعر بيع اتسجل بيه كل منتج من productIds للعميل
                // المختار حاليًا (selectedCustomerId) ويدمجه في lastPricesByProduct
                // - بتتجاهل أي id متخزّن بالفعل عشان منكررش نفس الطلب. لو مفيش
                // عميل مختار، مفيش داعي نطلب حاجة (مفيش بادچ يظهر أصلًا).
                async refreshLastPrices(productIds) {
                    if (!this.selectedCustomerId) return;
                    const ids = [...new Set((productIds || []).filter(Boolean))]
                        .filter(id => !(id in this.lastPricesByProduct));
                    if (ids.length === 0) return;

                    const params = new URLSearchParams();
                    params.set('customer_id', this.selectedCustomerId);
                    ids.forEach(id => params.append('product_ids[]', id));

                    try {
                        const res = await fetch(`{{ route('invoices.products.last-prices') }}?` + params.toString());
                        const data = await res.json();
                        Object.assign(this.lastPricesByProduct, data);
                    } catch (e) {
                        // فشل الطلب مش لازم يوقف حاجة تانية في الشاشة - البادچ
                        // هيفضل مخفي وبس.
                    }
                },
                // بتتنفذ لما نسبة الضريبة "من فوق" تتغيّر - بتطبقها على كل
                // الأصناف اللي في الجدول أوتوماتيك عشان الجدول يفضل بس عارض
                // للنسبة مش فيه تعديل لكل صنف لوحده.
                applyDefaultTaxRate() {
                    this.items.forEach(i => {
                        i.tax_rate = this.defaultTaxRate;
                    });
                },
                removeItem(index) {
                    const removed = this.items[index];
                    this.items.splice(index, 1);
                    // نشيله من قايمة "المضافين" في المودال عشان لو حبت تضيفه تاني يرجع زرار "إضافة" يظهر
                    if (removed && removed.product_id) {
                        this.addedProductIds = this.addedProductIds.filter(id => id !== removed.product_id);
                    }
                },
                // مودال اختيار منتج من قائمة كاملة (زي النظام القديم: بحث + صفحات 20 منتج تتحمل بالـ ajax)
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
                        this.refreshLastPrices(this.pickerProducts.map(p => p.id));
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
                // مودال "العمليات" (مبيعات/مشتريات/تحويلات منتج واحد) - مودال
                // ثاني بيتفتح فوق مودال اختيار منتج من غير أي navigation، وبيتحمّل
                // بيانات جدول واحد موحّد بالـ ajax (زي شكل مودال الاختيار نفسه).
                openOperations(p) {
                    this.operationsProductId = p.id;
                    this.operationsProductName = p.name;
                    this.operationsType = 'all';
                    this.operationsDateFrom = '';
                    this.operationsDateTo = '';
                    this.operationsModalOpen = true;
                    this.loadOperations();
                },
                async loadOperations() {
                    if (!this.operationsProductId) return;
                    this.operationsLoading = true;
                    try {
                        const params = new URLSearchParams({ type: this.operationsType });
                        if (this.operationsDateFrom) params.set('date_from', this.operationsDateFrom);
                        if (this.operationsDateTo) params.set('date_to', this.operationsDateTo);
                        const url = `{{ route('products.operations.data', ['product' => '__PID__']) }}`.replace('__PID__', this.operationsProductId);
                        const res = await fetch(url + '?' + params.toString());
                        const data = await res.json();
                        this.operationsRows = data.rows || [];
                    } finally {
                        this.operationsLoading = false;
                    }
                },
                // مودال "البدائل" - منفصل تمامًا عن مودال العمليات، بيعرض
                // منتجات بديلة للمنتج المختار وكل واحد بزرار "إضافة" خاص بيه.
                openAlternates(p) {
                    this.alternatesProductName = p.name;
                    this.alternatesModalOpen = true;
                    this.alternatesLoading = true;
                    const url = `{{ route('products.alternates', ['product' => '__PID__']) }}`.replace('__PID__', p.id);
                    fetch(url)
                        .then(res => res.json())
                        .then(rows => { this.alternatesRows = rows; })
                        .finally(() => { this.alternatesLoading = false; });
                },
                addAlternateProduct(p) {
                    this.addProduct(p);
                    this.markAdded(p.id);
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
                // (مش المضاعفة المباشرة في * 100) عشان نتجنب مشاكل الفاصلة
                // العشرية في JavaScript (0.14 * 100 ممكن تطلع
                // 14.000000000000002 بدل 14 بالظبط).
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
                    // إجمالي الخصم = مجموع خصومات الأصناف + الخصم الإضافي على
                    // مستوى الفاتورة نفسها (كان ناقص قبل كده وده اللي كان
                    // بيخلي الرقم المعروض في بطاقة "إجمالي الخصم" غلط).
                    const itemsDiscount = this.items.reduce((sum, i) => sum + (parseFloat(i.discount_amount) || 0), 0);
                    return itemsDiscount + (parseFloat(this.extraDiscount) || 0);
                },
                get grandTotal() {
                    const total = this.subtotal + this.taxTotal - (parseFloat(this.extraDiscount) || 0);
                    return total > 0 ? total : 0;
                },
                // بتتحقق من حدين للخصم اللي الموظف داخله على الفاتورة:
                // 1) إنه مش أكبر من إجمالي الفاتورة نفسه (قبل الخصم).
                // 2) إنه كنسبة مئوية من الإجمالي مش متعدي أقصى نسبة خصم
                //    مسموح بيها للموظف ده (maxDiscountPercent، جاية من
                //    جدول employee_discount_settings حسب user_id + الفرع).
                // لو اتعدى أي حد منهم، بترجع الخصم لأقصى قيمة مسموح بيها
                // وتطلع رسالة تنبيه.
                checkDiscountLimit() {
                    const limit = this.subtotal + this.taxTotal;
                    let discount = parseFloat(this.extraDiscount) || 0;
                    if (discount > limit) {
                        this.extraDiscount = limit;
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('invoices.discount_exceeds_total')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('invoices.ok')),
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
                                confirmButtonText: @json(__('invoices.ok')),
                            });
                            return false;
                        }
                    }
                    return true;
                },
                submitInvoice(isDraft) {
                    // خط الدفاع الأول ضد الضغط المتكرر على الزرار - لو فيه
                    // إرسال شغال بالفعل (isSubmitting = true)، أي ضغطة
                    // تانية بترجع فورًا من غير ما تعمل أي حاجة.
                    if (this.isSubmitting) {
                        return;
                    }
                    if (!this.checkDiscountLimit()) {
                        return;
                    }
                    if (!this.selectedCustomerId) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('invoices.select_customer_required')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('invoices.ok')),
                        });
                        return;
                    }
                    if (this.items.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('invoices.no_items_error')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('invoices.ok')),
                        });
                        return;
                    }
                    this.isSubmitting = true;
                    document.getElementById('is_finalized').value = isDraft ? '0' : '1';
                    document.getElementById('items_json').value = JSON.stringify(this.items);
                    document.getElementById('invoice-form').submit();
                },
                async createCustomer() {
                    if (!this.newCustomer.name || !this.newCustomer.phone) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('invoices.enter_customer_name_phone')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('invoices.ok')),
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
                        // بنضيف العميل الجديد لـ TomSelect باستخدام الـ API
                        // بتاعه (addOption + addItem) مش بالتلاعب المباشر في
                        // الـ <select> الأصلي، لإن TomSelect بيغلفه ومخفيه -
                        // فأي إضافة عن طريق select.add() منكنش بتظهر في القايمة.
                        if (this.customerTomSelect) {
                            this.customerTomSelect.addOption({
                                id: String(data.id),
                                text: data.name
                            });
                            this.customerTomSelect.addItem(String(data.id));
                        } else {
                            // fallback لو TomSelect مش متحمل لأي سبب
                            const select = document.getElementById('customer_select');
                            const opt = document.createElement('option');
                            opt.value = data.id;
                            opt.text = data.name;
                            opt.selected = true;
                            select.add(opt);
                        }
                        this.selectedCustomerId = String(data.id);
                        this.customerModalOpen = false;
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
                            title: @json(__('invoices.complete_product_data')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('invoices.ok')),
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
                        // نضيف المنتج الجديد مباشرة لأصناف الفاتورة زي ما لو
                        // اخترناه من مودال البحث/الاختيار
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