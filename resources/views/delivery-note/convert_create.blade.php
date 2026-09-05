<x-app-layout>
    @php
        $itemsForJs = $items->map(function ($item) use ($defaultTaxRate) {
            $available = $item->quantity - $item->quantityreturn - $item->invoiced_quantity;
            return [
                'sales_item_id' => $item->id,
                'name' => $item->product->name ?? '-',
                'delivery_note_id' => $item->invoice_id,
                'quantity' => $item->quantity,
                'quantityreturn' => $item->quantityreturn,
                'invoiced_quantity' => $item->invoiced_quantity,
                'available' => $available,
                'unit_price' => (float) $item->Unit_Price,
                'selected' => false,
                'invoice_qty' => $available,
                'tax_rate' => $defaultTaxRate,
            ];
        })->values();
    @endphp
    <script type="application/json" id="convert-items-data">@json($itemsForJs)</script>

    <div class="py-6" x-data="convertForm()">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 12l2 2 4-4" /><circle cx="12" cy="12" r="9" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('deliverynote.convert_title') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ $customer->name }}</p>
                    </div>
                </div>
                <a href="{{ route('deliverynote.convert.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                    {{ __('deliverynote.back') }}
                </a>
            </div>

            @if($items->isEmpty())
                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-10 text-center text-gray-400">
                    {{ __('deliverynote.no_pending_items') }}
                </div>
            @else
            <form id="convert-form" method="POST" action="{{ route('deliverynote.convert.store', $customer->id) }}">
                @csrf
                <input type="hidden" name="items_json" id="convert_items_json">

                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                    <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-[#0F1B4C] text-white/80">
                                    <th class="px-3 py-2.5"></th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.decoumentNo') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.product_name') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.remaining') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.invoice_now_qty') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.unit_price') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.tax') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <template x-for="item in items" :key="item.sales_item_id">
                                    <tr class="hover:bg-[#1456E8]/5 transition" :class="item.selected ? 'bg-emerald-50/40' : ''">
                                        <td class="px-3 py-2 text-center">
                                            <input type="checkbox" x-model="item.selected" class="rounded border-gray-300 text-[#1456E8] focus:ring-[#1456E8]">
                                        </td>
                                        <td class="px-3 py-2 text-gray-500">#<span x-text="item.delivery_note_id"></span></td>
                                        <td class="px-3 py-2 font-medium text-gray-800 min-w-[200px] whitespace-normal" x-text="item.name"></td>
                                        <td class="px-3 py-2 font-semibold text-[#0F1B4C]" x-text="item.available"></td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0.01" :max="item.available"
                                                   x-model.number="item.invoice_qty" :disabled="!item.selected"
                                                   class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8] disabled:bg-gray-100">
                                        </td>
                                        <td class="px-3 py-2 text-gray-600" x-text="item.unit_price.toFixed(2)"></td>
                                        <td class="px-3 py-2 text-gray-500" x-text="formatTaxRate(item.tax_rate) + '%'"></td>
                                        <td class="px-3 py-2 font-semibold text-[#0F1B4C]" x-text="lineTotal(item).toFixed(2)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- طريقة الدفع للفاتورة الناتجة --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('deliverynote.payment_method') }} *</label>
                            <select name="payment_method" x-model="paymentMethod"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                                <option value="cash">{{ __('deliverynote.cash') }}</option>
                                <option value="bank_transfer">{{ __('deliverynote.bank_transfer') }}</option>
                                <option value="card">{{ __('deliverynote.card') }}</option>
                                <option value="credit">{{ __('deliverynote.credit') }}</option>
                                <option value="split">{{ __('deliverynote.split') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('deliverynote.tax') }}</label>
                            <select x-model.number="defaultTaxRate" @change="applyDefaultTaxRate()"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                @foreach ($taxes as $tax)
                                    <option value="{{ $tax->rate / 100 }}">({{ $tax->rate }}%) {{ $tax->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('deliverynote.notesClient') }}</label>
                            <input type="text" name="note" x-model="note"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>

                    <template x-if="paymentMethod === 'split'">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-[#0F1B4C]/5 p-4 rounded-lg border border-[#0F1B4C]/10 mt-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('deliverynote.cash_amount') }}</label>
                                <input type="number" step="0.01" min="0" name="cash_amount" x-model.number="cashAmount"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('deliverynote.bank_amount') }}</label>
                                <input type="number" step="0.01" min="0" name="bank_amount" x-model.number="bankAmount"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            </div>
                        </div>
                    </template>

                    {{-- الإجماليات --}}
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-6">
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">{{ __('deliverynote.subtotal') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="subtotal.toFixed(2)"></div>
                        </div>
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">{{ __('deliverynote.tax_total') }}</div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="taxTotal.toFixed(2)"></div>
                        </div>
                        <div class="rounded-lg p-4 text-center text-white bg-[#0F1B4C] relative overflow-hidden">
                            <span class="absolute inset-x-0 bottom-0 h-0.5 bg-[#F5811E]"></span>
                            <div class="text-xs text-white/50 mb-1">{{ __('deliverynote.grand_total') }}</div>
                            <div class="font-bold text-lg" x-text="grandTotal.toFixed(2)"></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-6">
                        <button type="button" @click="submitConvert()" :disabled="isSubmitting"
                                class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting">{{ __('deliverynote.approve_and_invoice') }}</span>
                            <span x-show="isSubmitting" x-cloak>{{ __('deliverynote.saving_please_wait') }}</span>
                        </button>
                        <a href="{{ route('deliverynote.convert.index') }}"
                           class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                            {{ __('deliverynote.cancel') }}
                        </a>
                    </div>
                </div>
            </form>
            @endif
        </div>
    </div>

    <script>
        function convertForm() {
            return {
                isSubmitting: false,
                items: [],
                paymentMethod: 'cash',
                cashAmount: 0,
                bankAmount: 0,
                note: '',
                defaultTaxRate: {{ $defaultTaxRate }},

                init() {
                    const el = document.getElementById('convert-items-data');
                    this.items = el ? JSON.parse(el.textContent) : [];
                },

                applyDefaultTaxRate() {
                    this.items.forEach(i => {
                        i.tax_rate = this.defaultTaxRate;
                    });
                },

                lineTotal(item) {
                    if (!item.selected) return 0;
                    const qty = parseFloat(item.invoice_qty) || 0;
                    const price = parseFloat(item.unit_price) || 0;
                    const sub = qty * price;
                    return sub + (sub * (parseFloat(item.tax_rate) || 0));
                },

                // بيرجع نسبة الضريبة كنص منسّق لمنزلتين عشريتين بعد التقريب
                // عشان نتجنب مشاكل الفاصلة العشرية في JavaScript (0.14 * 100
                // ممكن تطلع 14.000000000000002 بدل 14 بالظبط).
                formatTaxRate(rate) {
                    const value = Math.round(((parseFloat(rate) || 0) * 100) * 100) / 100;
                    return (Number.isInteger(value) ? value.toFixed(0) : value.toFixed(2)).replace(/\.00$/, '');
                },

                get selectedItems() {
                    return this.items.filter(i => i.selected && (parseFloat(i.invoice_qty) || 0) > 0);
                },

                get subtotal() {
                    return this.selectedItems.reduce((sum, i) => sum + ((parseFloat(i.invoice_qty) || 0) * (parseFloat(i.unit_price) || 0)), 0);
                },

                get taxTotal() {
                    return this.selectedItems.reduce((sum, i) => {
                        const sub = (parseFloat(i.invoice_qty) || 0) * (parseFloat(i.unit_price) || 0);
                        return sum + (sub * (parseFloat(i.tax_rate) || 0));
                    }, 0);
                },

                get grandTotal() {
                    return this.subtotal + this.taxTotal;
                },

                submitConvert() {
                    if (this.isSubmitting) return;

                    if (this.selectedItems.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('deliverynote.select_items_required')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('deliverynote.ok')),
                        });
                        return;
                    }

                    const invalid = this.selectedItems.find(i => (parseFloat(i.invoice_qty) || 0) > (parseFloat(i.available) || 0));
                    if (invalid) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('deliverynote.convert_error_exceed')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('deliverynote.ok')),
                        });
                        return;
                    }

                    Swal.fire({
                        icon: 'question',
                        title: @json(__('deliverynote.confirm_convert_title')),
                        text: @json(__('deliverynote.confirm_convert_text')),
                        showCancelButton: true,
                        confirmButtonText: @json(__('deliverynote.confirm')),
                        cancelButtonText: @json(__('deliverynote.cancel')),
                        reverseButtons: true,
                        confirmButtonColor: '#1456E8',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.isSubmitting = true;
                            const payload = this.selectedItems.map(i => ({
                                sales_item_id: i.sales_item_id,
                                invoice_qty: i.invoice_qty,
                                tax_rate: i.tax_rate,
                            }));
                            document.getElementById('convert_items_json').value = JSON.stringify(payload);
                            document.getElementById('convert-form').submit();
                        }
                    });
                },
            }
        }
    </script>
</x-app-layout>
