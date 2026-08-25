<x-app-layout>

    <div class="py-6" x-data="zatcaScreen()">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- هيدر الصفحة --}}
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2 3 6v6c0 5 4 9 9 10 5-1 9-5 9-10V6l-9-4Z"/><path d="m9 12 2 2 4-4"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('zatca.title') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('zatca.subtitle') }}</p>
                    </div>
                </div>
            </div>

            {{-- التابات --}}
            <div class="flex items-center gap-2 border-b border-gray-200">
                <a href="{{ route('zatca.index', ['sent' => 0]) }}"
                   class="px-5 py-2.5 -mb-px border-b-2 font-medium text-sm transition {{ !$sent ? 'border-[#F5811E] text-[#0F1B4C]' : 'border-transparent text-gray-400 hover:text-gray-600' }}">
                    {{ __('zatca.not_sent') }}
                    <span class="ms-1 inline-flex items-center justify-center min-w-[22px] h-[22px] px-1 rounded-full text-xs font-bold {{ !$sent ? 'bg-[#F5811E] text-white' : 'bg-gray-100 text-gray-500' }}">{{ $notSentCount }}</span>
                </a>
                <a href="{{ route('zatca.index', ['sent' => 1]) }}"
                   class="px-5 py-2.5 -mb-px border-b-2 font-medium text-sm transition {{ $sent ? 'border-[#F5811E] text-[#0F1B4C]' : 'border-transparent text-gray-400 hover:text-gray-600' }}">
                    {{ __('zatca.sent') }}
                    <span class="ms-1 inline-flex items-center justify-center min-w-[22px] h-[22px] px-1 rounded-full text-xs font-bold {{ $sent ? 'bg-[#F5811E] text-white' : 'bg-gray-100 text-gray-500' }}">{{ $sentCount }}</span>
                </a>
            </div>

            {{-- فلاتر --}}
            <form method="GET" action="{{ route('zatca.index') }}" class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <input type="hidden" name="sent" value="{{ $sent ? 1 : 0 }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('zatca.from_date') }}</label>
                        <input type="date" name="start_at" value="{{ request('start_at') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('zatca.to_date') }}</label>
                        <input type="date" name="end_at" value="{{ request('end_at') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('zatca.branch') }}</label>
                        <select name="branch_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value="">{{ __('zatca.all_branches') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                            class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition shadow-sm">
                        {{ __('zatca.search') }}
                    </button>
                </div>
            </form>

            {{-- الجدول --}}
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('zatca.invoice_no') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('zatca.seller') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('zatca.customer') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('zatca.date') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('zatca.branch') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('zatca.total') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('zatca.payment_method') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('zatca.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($invoices as $invoice)
                                @php
                                    $total = (float) $invoice->cash_amount + (float) $invoice->bank_amount
                                        + (float) $invoice->wire_transfer_amount + (float) $invoice->credit_amount;
                                @endphp
                                <tr class="hover:bg-[#1456E8]/5 transition" id="invoice-row-{{ $invoice->id }}">
                                    <td class="px-3 py-2.5 font-medium text-gray-800">{{ $invoice->invoice_number ?? $invoice->id }}</td>
                                    <td class="px-3 py-2.5 text-gray-600">{{ $invoice->creator->name ?? '-' }}</td>
                                    <td class="px-3 py-2.5 text-gray-600">{{ $invoice->customer->name ?? '-' }}</td>
                                    <td class="px-3 py-2.5 text-gray-500 whitespace-nowrap">{{ optional($invoice->issue_date)->format('Y-m-d') ?? $invoice->issue_date }}</td>
                                    <td class="px-3 py-2.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">{{ $invoice->branch->name ?? '-' }}</span>
                                    </td>
                                    <td class="px-3 py-2.5 font-semibold text-[#0F1B4C]">{{ number_format($total, 2) }}</td>
                                    <td class="px-3 py-2.5 text-gray-500">{{ __('zatca.payment_' . $invoice->payment_method) }}</td>
                                    <td class="px-3 py-2.5">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('invoices.show', $invoice) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-[#0F1B4C] bg-[#0F1B4C]/5 hover:bg-[#0F1B4C]/10 transition whitespace-nowrap">
                                                {{ __('zatca.view') }}
                                            </a>

                                            @if (!$invoice->is_sent_to_zatca)
                                                <button type="button" @click="sendToZatca({{ $invoice->id }})"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-[#F5811E] hover:brightness-95 transition whitespace-nowrap">
                                                    {{ __('zatca.send') }}
                                                </button>
                                            @elseif ($invoice->zatca_status === 'PASS')
                                                <a href="{{ route('zatca.download-xml', $invoice) }}"
                                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 transition whitespace-nowrap">
                                                    {{ __('zatca.download_xml') }}
                                                </a>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium text-red-600 bg-red-50 border border-red-100">
                                                    {{ __('zatca.failed') }}
                                                </span>
                                                <button type="button" @click="sendToZatca({{ $invoice->id }})"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-[#F5811E] hover:brightness-95 transition whitespace-nowrap">
                                                    {{ __('zatca.retry') }}
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-3 py-10 text-center text-gray-400">
                                        {{ __('zatca.no_invoices') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- شاشة تحميل أثناء الإرسال - نفس فكرة loading-screen في النظام القديم --}}
        <div x-show="sending" x-cloak
             class="fixed inset-0 bg-black/70 flex items-center justify-center z-[9999] text-white gap-3">
            <svg class="w-8 h-8 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="9" stroke-opacity="0.25"/>
                <path d="M21 12a9 9 0 0 0-9-9"/>
            </svg>
            <p>{{ __('zatca.sending_wait') }}</p>
        </div>
    </div>

    <script>
        function zatcaScreen() {
            return {
                sending: false,

                async sendToZatca(invoiceId) {
                    this.sending = true;
                    try {
                        const res = await fetch(`{{ url('zatca') }}/${invoiceId}/send`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                        });
                        const data = await res.json();

                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: @json(__('zatca.sent_successfully')),
                                confirmButtonColor: '#0F1B4C',
                                confirmButtonText: @json(__('zatca.ok')),
                            }).then(() => window.location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: @json(__('zatca.send_failed')),
                                text: data.message || '',
                                confirmButtonColor: '#0F1B4C',
                                confirmButtonText: @json(__('zatca.ok')),
                            });
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: @json(__('zatca.send_failed')),
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: @json(__('zatca.ok')),
                        });
                    } finally {
                        this.sending = false;
                    }
                },
            }
        }
    </script>
</x-app-layout>
