<x-app-layout>

    <div class="py-6">
        <div class="dc-max-w-page mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- هيدر الصفحة بلون البراند الكحلي --}}
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4v5h5M20 20v-5h-5"/>
                            <path d="M4.6 15a8 8 0 0 0 14.9 2M19.4 9A8 8 0 0 0 4.5 7"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('invoices.title') }}</h2>
                </div>
                <a href="{{ route('invoices.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                    + {{ __('invoices.new_invoice') }}
                </a>
            </div>

            @include('partials.sweet-alert-flash')

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl">

                {{-- الفلاتر --}}
                <div class="p-4 border-b border-gray-100">
                    <form method="GET" action="{{ route('invoices.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('invoices.invoice_no') }}</label>
                            <input type="text" name="invoice_number" value="{{ request('invoice_number') }}"
                                   placeholder="{{ __('invoices.invoice_no_placeholder') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('invoices.customer') }}</label>
                            <select name="customer_id" data-ajax-select data-ajax-url="{{ route('customers.search') }}"
                                    data-ajax-placeholder="{{ __('invoices.all_customers') }}"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">{{ __('invoices.all_customers') }}</option>
                                @foreach ($customers as $id => $name)
                                    @if ($name)
                                        <option value="{{ $id }}" selected>{{ $name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('invoices.filter_by_date') }}</label>
                            <input type="date" name="date" value="{{ request('date') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                                {{ __('messages.search') }}
                            </button>
                            @if (request()->hasAny(['invoice_number', 'customer_id', 'date']))
                                <a href="{{ route('invoices.index') }}"
                                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
                                    {{ __('invoices.cancel') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-start">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.invoice_no') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.seller') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.customer') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.date') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.branch') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.grand_total') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.payment_method') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.status') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($Invoice as $invoice)
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-4 py-3 font-medium text-gray-800">#{{ $invoice->invoice_number ?? $invoice->id }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $invoice->creator?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $invoice->customer?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $invoice->issue_date?->format('Y-m-d') ?? $invoice->created_at->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $invoice->branch?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 font-semibold text-[#0F1B4C]">{{ number_format($invoice->subtotal + $invoice->tax_amount - ($invoice->invoice_level_discount ?? 0), 2) }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ __('invoices.' . $invoice->payment_method) }}</td>
                                    <td class="px-4 py-3">
                                        @if ($invoice->is_finalized)
                                            <span class="px-2 py-1 rounded-full text-xs bg-green-50 text-green-700">{{ __('invoices.final') }}</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-amber-50 text-amber-700">
                                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                                                {{ __('invoices.draft') }}
                                            </span>
                                        @endif
                                    </td>
                           <td class="px-4 py-3">
    <div class="flex items-center gap-2">
        <a href="{{ route('invoices.show', $invoice) }}" title="{{ __('invoices.view') }}"
           class="w-7 h-7 flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#11111] hover:bg-[#1456E8]/20 transition">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
        </a>

        {{-- PDF: تحميل مباشر لملف الفاتورة --}}
        <a href="{{ route('invoices.pdf', $invoice) }}" title="{{ __('invoices.download_pdf') }}"
           class="w-7 h-7 flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#1456E8] hover:bg-[#1456E8]/20 transition">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/>
            </svg>
        </a>

        {{-- واتساب: رابط لعرض الفاتورة (PDF أونلاين) - عبر رابط موقّع
             (signed) شغال 30 يوم من غير ما العميل يحتاج يسجل دخول -
             ومحتاج رقم موبايل العميل مسجل في بياناته. --}}
        @php
            $rawPhone = preg_replace('/\D/', '', $invoice->customer->phone ?? '');
            $waPhone = null;
            if ($rawPhone !== '') {
                if (str_starts_with($rawPhone, '966')) {
                    $waPhone = $rawPhone;
                } elseif (str_starts_with($rawPhone, '0')) {
                    $waPhone = '966' . substr($rawPhone, 1);
                } else {
                    $waPhone = '966' . $rawPhone;
                }
            }
        @endphp
        @if ($waPhone)
            @php
                $publicPdfUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                    'invoices.public-pdf',
                    now()->addDays(30),
                    ['invoice' => $invoice->id]
                );
                $waText = rawurlencode(__('invoices.whatsapp_message', [
                    'number' => $invoice->invoice_number ?? $invoice->id,
                    'link' => $publicPdfUrl,
                ]));
            @endphp
            <a href="https://wa.me/{{ $waPhone }}?text={{ $waText }}" target="_blank" rel="noopener"
               title="{{ __('invoices.send_whatsapp') }}"
               class="w-7 h-7 flex items-center justify-center rounded-md bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z"/>
                </svg>
            </a>
        @else
            <span title="{{ __('invoices.no_customer_phone') }}"
                  class="w-7 h-7 flex items-center justify-center rounded-md bg-gray-100 text-gray-400 cursor-not-allowed">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z"/>
                </svg>
            </span>
        @endif

        {{-- تعديل: بيظهر بس لو المستخدم عنده صلاحية invoices.edit والفاتورة
             نفسها قابلة للتعديل (isEditable() في موديل Invoice) --}}
        @can('invoices.edit')
            @if ($invoice->isEditable())
                <a href="{{ route('invoices.edit', $invoice) }}" title="{{ __('invoices.edit') }}"
                   class="w-7 h-7 flex items-center justify-center rounded-md bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                    </svg>
                </a>
            @else
                <span title="{{ __('invoices.not_editable') }}"
                      class="w-7 h-7 flex items-center justify-center rounded-md bg-gray-100 text-gray-400 cursor-not-allowed">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                    </svg>
                </span>
            @endif
        @endcan

        @php
            $comingSoonIcons = [
                'print' => '<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8" rx="1"/>',
            ];
        @endphp

        @foreach ($comingSoonIcons as $soon => $iconPath)
            <span title="{{ __('invoices.' . $soon) }} - {{ __('invoices.coming_soon') }}"
                  class="w-7 h-7 flex items-center justify-center rounded-md bg-gray-100 text-gray-500 cursor-not-allowed">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $iconPath !!}</svg>
            </span>
        @endforeach
    </div>
</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-10 text-center text-gray-400">
                                        {{ __('invoices.no_invoices') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

       <div class="p-4 border-t border-gray-100">
    @if ($Invoice->hasPages())
        <div class="flex items-center justify-center gap-1 flex-wrap">
            @if ($Invoice->onFirstPage())
                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed">{{ __('invoices.previous') }}</span>
            @else
                <a href="{{ $Invoice->previousPageUrl() }}"
                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ __('invoices.previous') }}</a>
            @endif

            @foreach (range(1, $Invoice->lastPage()) as $page)
                @if ($page == $Invoice->currentPage())
                    <span class="px-3 py-1.5 rounded-lg text-sm font-semibold text-white bg-[#0F1B4C]">{{ $page }}</span>
                @else
                    <a href="{{ $Invoice->url($page) }}"
                       class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ $page }}</a>
                @endif
            @endforeach

            @if ($Invoice->hasMorePages())
                <a href="{{ $Invoice->nextPageUrl() }}"
                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ __('invoices.next') }}</a>
            @else
                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed">{{ __('invoices.next') }}</span>
            @endif
        </div>
    @endif
</div>
            </div>
        </div>
    </div>

    @include('partials.ajax-select-assets')
</x-app-layout>
