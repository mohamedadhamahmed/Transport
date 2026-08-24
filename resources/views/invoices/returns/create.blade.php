<x-app-layout>

    <div class="py-6" x-data="invoiceReturnForm()">
        <div class="max-w-[1280px] mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- هيدر الصفحة بنفس ستايل صفحة الفاتورة --}}
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 14l-4-4 4-4"/><path d="M5 10h9a5 5 0 0 1 5 5v1"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('invoices.sales_return') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('invoices.sales_return_subtitle') }}</p>
                    </div>
                </div>
                <a href="{{ route('invoices.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('invoices.back_to_list') }}
                </a>
            </div>

            <form id="invoice-return-form" method="POST" action="{{ route('invoices.returns.store') }}">
                @csrf
                <input type="hidden" name="invoice_id" :value="selectedInvoice ? selectedInvoice.id : ''">
                <input type="hidden" name="refund_method" :value="refundMethod">
                <input type="hidden" name="items_json" id="return_items_json">

                {{-- خطوة 1: البحث عن الفاتورة --}}
                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.search_invoice_label') }}</label>
                    <div class="flex gap-2 max-w-md">
                        <div class="relative flex-1">
                            <input type="text" x-model="searchQuery"
                                   @input.debounce.300ms="searchInvoices()"
                                   @keydown.enter.prevent="searchInvoices()"
                                   placeholder="{{ __('invoices.search_invoice_placeholder') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">

                            <div x-show="searchResults.length > 0" x-cloak
                                 class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-72 overflow-y-auto">
                                <template x-for="inv in searchResults" :key="inv.id">
                                    <button type="button" @click="selectInvoice(inv)"
                                            class="w-full text-start px-4 py-2 hover:bg-[#1456E8]/5 flex items-center justify-between border-b border-gray-50 last:border-0">
                                        <span>
                                            <span class="font-medium text-gray-800" x-text="'#' + inv.invoice_number"></span>
                                            <span class="text-xs text-gray-400" x-text="' - ' + inv.customer_name"></span>
                                        </span>
                                        <span class="text-sm text-gray-500" x-text="inv.grand_total.toFixed(2)"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- زرار البحث الصريح - البحث أصلاً بيشتغل أوتوماتيك أثناء الكتابة
                             (debounce) أو بالـ Enter، والزرار ده لإتاحة نفس الإجراء بالضغط
                             المباشر لمن يفضّله. --}}
                        <button type="button" @click="searchInvoices()"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white text-sm font-medium bg-[#0F1B4C] hover:bg-[#0F1B4C]/90 transition whitespace-nowrap">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                            {{ __('invoices.search') }}
                        </button>
                    </div>

                    {{-- بيانات الفاتورة المختارة --}}
                    <div x-show="selectedInvoice" x-cloak class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-3">
                            <div class="text-xs text-gray-500 mb-1">{{ __('invoices.invoice_number') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="selectedInvoice ? '#' + selectedInvoice.invoice_number : ''"></div>
                        </div>
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-3">
                            <div class="text-xs text-gray-500 mb-1">{{ __('invoices.customer') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="selectedInvoice ? selectedInvoice.customer_name : ''"></div>
                        </div>
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-3">
                            <div class="text-xs text-gray-500 mb-1">{{ __('invoices.payment_method') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="selectedInvoice ? selectedInvoice.payment_method : ''"></div>
                        </div>
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-3">
                            <div class="text-xs text-gray-500 mb-1">{{ __('invoices.grand_total') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="grandTotal.toFixed(2)"></div>
                        </div>
                    </div>
                </div>

                {{-- خطوة 2: الأصناف القابلة للإرجاع --}}
                <div x-show="selectedInvoice" x-cloak class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6 mt-6">
                    <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-[#0F1B4C] text-white/80">
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.product') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.unit_price') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.original_quantity') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.remaining_quantity') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.return_quantity') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.tax') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <template x-for="item in items" :key="item.id">
                                    <tr class="hover:bg-[#1456E8]/5 transition">
                                        <td class="px-3 py-2 font-medium text-gray-800 min-w-[280px] whitespace-normal" x-text="item.name"></td>
                                        <td class="px-3 py-2 text-gray-500" x-text="item.unit_price.toFixed(2)"></td>
                                        <td class="px-3 py-2 text-gray-500" x-text="item.quantity"></td>
                                        <td class="px-3 py-2 text-emerald-600 font-semibold" x-text="item.remaining_quantity"></td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" :max="item.remaining_quantity"
                                                   x-model.number="item.return_qty"
                                                   @input="if (item.return_qty > item.remaining_quantity) item.return_qty = item.remaining_quantity; if (item.return_qty < 0) item.return_qty = 0;"
                                                   class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                        </td>
                                        <td class="px-3 py-2 text-gray-500" x-text="((item.tax_rate || 0) * 100) + '%'"></td>
                                        <td class="px-3 py-2 font-semibold text-[#0F1B4C]" x-text="lineNetRefund(item).toFixed(2)"></td>
                                    </tr>
                                </template>
                                <tr x-show="items.length === 0">
                                    <td colspan="7" class="px-3 py-8 text-center text-gray-400">
                                        {{ __('invoices.no_returnable_items') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- طريقة الاسترداد: بتظهر بس لو فيه مبلغ محتاج يترد فلوس فعلية (مش آجل بالكامل) --}}
                    <div x-show="cashRefundAmount > 0.009" x-cloak class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.refund_method') }} *</label>
                        <select x-model="refundMethod" class="w-full max-w-xs rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value="">-</option>
                            <option value="cash">{{ __('invoices.cash') }}</option>
                            <option value="bank_transfer">{{ __('invoices.bank_transfer') }}</option>
                            <option value="card">{{ __('invoices.card') }}</option>
                        </select>
                    </div>

                    {{-- ملاحظة توضيحية لو فيه جزء آجل بيترد أوتوماتيك --}}
                    <div x-show="creditRefundAmount > 0.009" x-cloak
                         class="mt-4 text-sm text-[#0F1B4C] bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-3">
                        <span x-text="'{{ __('invoices.credit_refund_note') }}'.replace(':amount', creditRefundAmount.toFixed(2))"></span>
                    </div>

                    {{-- الإجماليات --}}
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-6">
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">{{ __('invoices.credit_refund_amount') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="creditRefundAmount.toFixed(2)"></div>
                        </div>
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">{{ __('invoices.cash_refund_amount') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="cashRefundAmount.toFixed(2)"></div>
                        </div>
                        <div class="rounded-lg p-4 text-center text-white bg-[#0F1B4C] relative overflow-hidden">
                            <span class="absolute inset-x-0 bottom-0 h-0.5 bg-[#F5811E]"></span>
                            <div class="text-xs text-white/50 mb-1">{{ __('invoices.net_refund_total') }}</div>
                            <div class="font-bold text-lg" x-text="netTotalAmount.toFixed(2)"></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-6">
                        <button type="button" @click="submitReturn()"
                                class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition shadow-sm">
                            {{ __('invoices.save_return') }}
                        </button>
                        <a href="{{ route('invoices.index') }}"
                           class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                            {{ __('invoices.cancel') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function invoiceReturnForm() {
            return {
                searchQuery: '',
                searchResults: [],
                selectedInvoice: null,
                items: [],
                refundMethod: '',

                async searchInvoices() {
                    if (this.searchQuery.trim().length < 1) {
                        this.searchResults = [];
                        return;
                    }
                    const res = await fetch(`{{ route('invoices.returns.search') }}?q=` + encodeURIComponent(this.searchQuery));
                    this.searchResults = await res.json();
                },

                async selectInvoice(inv) {
                    this.searchQuery = '';
                    this.searchResults = [];
                    this.refundMethod = '';

                    const res = await fetch(`/invoices/returns/${inv.id}/items`);
                    const data = await res.json();

                    this.selectedInvoice = data.invoice;
                    this.items = data.items.map(item => ({ ...item, return_qty: 0 }));
                },

                // نفس منطق حساب المرتجع اللي في الكنترولر - ده بس لعرض تقريبي
                // للمستخدم قبل الحفظ، السيرفر بيعيد حسابها بدقة وقت submitReturn().
                lineNetRefund(item) {
                    if (!this.selectedInvoice || !item.return_qty) return 0;

                    const itemQty = item.quantity || 0;
                    const unitNet = itemQty > 0
                        ? ((item.unit_price * itemQty) - item.discount_amount) / itemQty
                        : 0;

                    const lineNet = unitNet * item.return_qty;
                    const lineTax = lineNet * (item.tax_rate || 0);
                    const lineGross = lineNet + lineTax;

                    const invoiceGrossTotal = this.selectedInvoice.subtotal + this.selectedInvoice.tax_amount;
                    const discountShare = invoiceGrossTotal > 0
                        ? this.selectedInvoice.invoice_level_discount * (lineGross / invoiceGrossTotal)
                        : 0;

                    return lineGross - discountShare;
                },

                get grandTotal() {
                    if (!this.selectedInvoice) return 0;
                    return this.selectedInvoice.subtotal + this.selectedInvoice.tax_amount - this.selectedInvoice.invoice_level_discount;
                },

                get netTotalAmount() {
                    return this.items.reduce((sum, item) => sum + this.lineNetRefund(item), 0);
                },

                get creditRatio() {
                    if (!this.selectedInvoice || this.grandTotal <= 0) return 0;
                    return this.selectedInvoice.credit_amount / this.grandTotal;
                },

                get creditRefundAmount() {
                    return this.netTotalAmount * this.creditRatio;
                },

                get cashRefundAmount() {
                    return this.netTotalAmount - this.creditRefundAmount;
                },

                submitReturn() {
                    const returnItems = this.items
                        .filter(item => item.return_qty && item.return_qty > 0)
                        .map(item => ({ invoice_item_id: item.id, quantity: item.return_qty }));

                    if (!this.selectedInvoice || returnItems.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('invoices.select_return_items_required')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('invoices.ok')),
                        });
                        return;
                    }

                    if (this.cashRefundAmount > 0.009 && !this.refundMethod) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('invoices.refund_method_required')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('invoices.ok')),
                        });
                        return;
                    }

                    document.getElementById('return_items_json').value = JSON.stringify(returnItems);
                    document.getElementById('invoice-return-form').submit();
                },
            }
        }
    </script>
</x-app-layout>
