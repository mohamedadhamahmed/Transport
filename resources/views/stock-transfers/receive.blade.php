<x-app-layout>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 8l9-5 9 5-9 5-9-5Z" />
                            <path d="M3 8v8l9 5 9-5V8" />
                            <path d="M12 13v8" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('stock_transfers.receive_title') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('stock_transfers.to_branch') }}: {{ $toBranch->name }}</p>
                    </div>
                </div>
                <a href="{{ route('stock-transfers.index', ['box' => 'received']) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('stock_transfers.back_to_list') }}
                </a>
            </div>

            @include('partials.sweet-alert-flash')

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('stock_transfers.transfer_number') }}</label>
                    <select id="transfer-select" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        <option value="">{{ __('stock_transfers.select_transfer_number') }}</option>
                        @foreach ($pendingTransfers as $transfer)
                            <option value="{{ $transfer->id }}">#{{ $transfer->transfer_number }}</option>
                        @endforeach
                    </select>
                    @if ($pendingTransfers->isEmpty())
                        <p class="text-sm text-gray-400 mt-2">{{ __('stock_transfers.no_pending_transfers') }}</p>
                    @endif
                </div>

                <div id="details-box" class="hidden space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="text-xs text-gray-400 mb-1">{{ __('stock_transfers.from_branch') }}</div>
                            <div id="detail-from-branch" class="font-medium text-gray-800">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 mb-1">{{ __('stock_transfers.sender_employee') }}</div>
                            <div id="detail-sender" class="font-medium text-gray-800">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 mb-1">{{ __('stock_transfers.transfer_date') }}</div>
                            <div id="detail-date" class="font-medium text-gray-800">-</div>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-[#0F1B4C] text-white/80">
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('stock_transfers.product') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('stock_transfers.quantity') }}</th>
                                </tr>
                            </thead>
                            <tbody id="detail-items" class="divide-y divide-gray-100 bg-white"></tbody>
                        </table>
                    </div>

                    <form method="POST" id="receive-form" class="flex items-center justify-end gap-3">
                        @csrf
                        <button type="submit" id="confirm-btn"
                                class="px-6 py-2.5 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                            {{ __('stock_transfers.confirm_receive') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const transferSelect = document.getElementById('transfer-select');
        const detailsBox = document.getElementById('details-box');
        const detailFromBranch = document.getElementById('detail-from-branch');
        const detailSender = document.getElementById('detail-sender');
        const detailDate = document.getElementById('detail-date');
        const detailItems = document.getElementById('detail-items');
        const receiveForm = document.getElementById('receive-form');

        function detailsUrl(id) {
            return `{{ route('stock-transfers.details', ['stockTransfer' => '__ID__']) }}`.replace('__ID__', id);
        }

        function confirmUrl(id) {
            return `{{ route('stock-transfers.confirm-receive', ['stockTransfer' => '__ID__']) }}`.replace('__ID__', id);
        }

        transferSelect.addEventListener('change', async () => {
            const id = transferSelect.value;
            if (!id) {
                detailsBox.classList.add('hidden');
                return;
            }

            const res = await fetch(detailsUrl(id), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();

            detailFromBranch.textContent = data.from_branch ?? '-';
            detailSender.textContent = data.sender ?? '-';
            detailDate.textContent = data.transfer_date ?? '-';

            detailItems.innerHTML = '';
            data.items.forEach((item) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-3 py-2 font-medium text-gray-800">${item.name}${item.code ? ' (#' + item.code + ')' : ''}</td>
                    <td class="px-3 py-2 text-gray-600">${item.quantity} ${item.unit ?? ''}</td>
                `;
                detailItems.appendChild(tr);
            });

            receiveForm.action = confirmUrl(id);
            detailsBox.classList.remove('hidden');
        });
    })();
    </script>
</x-app-layout>
