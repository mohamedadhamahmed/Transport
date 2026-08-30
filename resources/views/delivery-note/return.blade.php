<x-app-layout>
    @php
        $itemsForJs = $items->map(function ($item) {
            $available = $item->quantity - $item->quantityreturn;
            return [
                'id' => $item->id,
                'name' => $item->product->name ?? '-',
                'quantity' => $item->quantity,
                'quantityreturn' => $item->quantityreturn,
                'available' => $available,
                'unit_price' => (float) $item->Unit_Price,
                'return_qty' => 0,
            ];
        })->values();
    @endphp
    <script type="application/json" id="return-items-data">@json($itemsForJs)</script>

    <div class="py-6" x-data="returnForm()">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- هيدر الصفحة بلون تحذيري (برتقالي) --}}
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 7v6h6" /><path d="M3 13a9 9 0 1 0 3-6.7L3 9" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('deliverynote.delivery_return') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('deliverynote.decoumentNo') }} #{{ $invoice->id }} — {{ $invoice->customer->name ?? '-' }}</p>
                    </div>
                </div>
                <a href="{{ route('deliverynote.show', $invoice->id) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                    {{ __('deliverynote.view') }}
                </a>
            </div>

            <form id="return-form" method="POST" action="{{ route('deliverynote.return.store', $invoice->id) }}">
                @csrf
                <input type="hidden" name="items_json" id="return_items_json">

                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                    <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-[#0F1B4C] text-white/80">
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.product_name') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.quantity') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.already_returned') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.remaining') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.return_quantity') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.unit_price') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.return_total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <template x-for="(item, index) in items" :key="item.id">
                                    <tr class="hover:bg-[#F5811E]/5 transition">
                                        <td class="px-3 py-2 font-medium text-gray-800 min-w-[220px] whitespace-normal" x-text="item.name"></td>
                                        <td class="px-3 py-2 text-gray-600" x-text="item.quantity"></td>
                                        <td class="px-3 py-2 text-gray-600" x-text="item.quantityreturn"></td>
                                        <td class="px-3 py-2 font-semibold text-[#0F1B4C]" x-text="item.available"></td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" :max="item.available"
                                                   x-model.number="item.return_qty" :disabled="item.available <= 0"
                                                   class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-[#F5811E] focus:ring-[#F5811E] disabled:bg-gray-100 disabled:text-gray-400">
                                        </td>
                                        <td class="px-3 py-2 text-gray-600" x-text="item.unit_price.toFixed(2)"></td>
                                        <td class="px-3 py-2 font-semibold text-[#F5811E]" x-text="lineReturnTotal(item).toFixed(2)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('deliverynote.return_reason') }}</label>
                            <input type="text" name="return_note" x-model="returnNote"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#F5811E] focus:ring-[#F5811E]">
                        </div>
                        <div class="rounded-lg p-4 text-center text-white bg-[#0F1B4C] relative overflow-hidden">
                            <span class="absolute inset-x-0 bottom-0 h-0.5 bg-[#F5811E]"></span>
                            <div class="text-xs text-white/50 mb-1">{{ __('deliverynote.total_return') }}</div>
                            <div class="font-bold text-lg" x-text="grandReturnTotal.toFixed(2)"></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-6">
                        <button type="button" @click="submitReturn()" :disabled="isSubmitting"
                                class="px-5 py-2 rounded-lg font-medium text-white bg-[#F5811E] hover:brightness-95 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting">{{ __('deliverynote.confirm_return') }}</span>
                            <span x-show="isSubmitting" x-cloak>{{ __('deliverynote.saving_please_wait') }}</span>
                        </button>
                        <a href="{{ route('deliverynote.history') }}"
                           class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                            {{ __('deliverynote.cancel') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function returnForm() {
            return {
                isSubmitting: false,
                items: [],
                returnNote: '',

                init() {
                    const el = document.getElementById('return-items-data');
                    this.items = el ? JSON.parse(el.textContent) : [];
                },

                lineReturnTotal(item) {
                    return (parseFloat(item.return_qty) || 0) * (parseFloat(item.unit_price) || 0);
                },

                get grandReturnTotal() {
                    return this.items.reduce((sum, i) => sum + this.lineReturnTotal(i), 0);
                },

                submitReturn() {
                    if (this.isSubmitting) {
                        return;
                    }

                    const hasReturn = this.items.some(i => (parseFloat(i.return_qty) || 0) > 0);
                    if (!hasReturn) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('deliverynote.return_error_none')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('deliverynote.ok')),
                        });
                        return;
                    }

                    // تحقق إن أي كمية مرتجعة مش متخطية المتاح فعليًا (حماية إضافية جانب العميل)
                    const invalid = this.items.find(i => (parseFloat(i.return_qty) || 0) > (parseFloat(i.available) || 0));
                    if (invalid) {
                        Swal.fire({
                            icon: 'warning',
                            title: @json(__('deliverynote.return_error_exceed')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('deliverynote.ok')),
                        });
                        return;
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: @json(__('deliverynote.confirm_return_title')),
                        text: @json(__('deliverynote.confirm_return_text')),
                        showCancelButton: true,
                        confirmButtonText: @json(__('deliverynote.confirm')),
                        cancelButtonText: @json(__('deliverynote.cancel')),
                        reverseButtons: true,
                        confirmButtonColor: '#F5811E',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.isSubmitting = true;
                            document.getElementById('return_items_json').value = JSON.stringify(this.items);
                            document.getElementById('return-form').submit();
                        }
                    });
                },
            }
        }
    </script>
</x-app-layout>
