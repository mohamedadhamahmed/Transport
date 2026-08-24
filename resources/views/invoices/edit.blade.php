

<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- هيدر الصفحة --}}
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">تعديل الفاتورة رقم: #{{ $invoice->invoice_number ?? $invoice->id }}</h2>
                </div>
                <a href="{{ route('invoices.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                    العودة للقائمة
                </a>
            </div>

            {{-- نموذج التعديل --}}
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form action="{{ route('invoices.update', $invoice) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- العميل --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">العميل</label>
                            <select name="customer_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id', $invoice->customer_id) == $customer->id)>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- طريقة الدفع --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">طريقة الدفع</label>
                            <select name="payment_method" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                                <option value="cash" @selected(old('payment_method', $invoice->payment_method) == 'cash')>كاش</option>
                                <option value="bank_transfer" @selected(old('payment_method', $invoice->payment_method) == 'bank_transfer')>تحويل بنكي</option>
                                <option value="credit" @selected(old('payment_method', $invoice->payment_method) == 'credit')>أجل</option>
                            </select>
                        </div>
                    </div>

                    {{-- زر الحفظ --}}
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('invoices.index') }}" class="px-5 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
                            إلغاء
                        </a>
                        <button type="submit" class="px-6 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                            حفظ التعديلات
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>