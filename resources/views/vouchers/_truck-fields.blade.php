{{-- سند الصيانة: نفس سند الصرف + الشاحنة ونوع المصروف (بيظهر في تقرير الشاحنات) --}}
@if (!empty($isMaintenance))
    <input type="hidden" name="is_maintenance" value="1">
    <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6" style="border-inline-start:4px solid #F5811E">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">🚚 {{ __('transport.truck') }} *</label>
                <select name="truck_id" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    <option value="">{{ __('transport.choose_truck') }}</option>
                    @foreach ($trucks as $t)
                        <option value="{{ $t->id }}" @selected((string) old('truck_id', $selectedTruckId ?? '') === (string) $t->id)>
                            {{ $t->display_name }}{{ $t->status === 'inactive' ? ' (' . __('transport.status_inactive') . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">🔧 {{ __('transport.expense_category') }} *</label>
                <select name="expense_category" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    @foreach (\App\Models\AccountVoucher::EXPENSE_CATEGORIES as $k => $label)
                        <option value="{{ $k }}" @selected(old('expense_category', $voucher->expense_category ?? 'maintenance') === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
@endif
