<x-app-layout>
    @include('transport.partials.styles')

    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-[#0F1B4C]">📄 أذونات صرف قطع الغيار والزيوت</h2>
                    <p class="text-xs text-gray-500 mt-1">سجل الصرف المخزني للشاحنات مع قيد المخزون ومتابعة عدادات الزيت</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('transport.store-issues.report') }}" class="px-4 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition">
                        📊 تقرير صرف قطع الغيار والزيوت
                    </a>
                    @can('maintenance.create')
                        <a href="{{ route('transport.store-issues.create') }}" class="px-4 py-2.5 rounded-lg bg-[#0F1B4C] text-white text-sm font-bold hover:bg-[#0F1B4C]/90 shadow-md transition">
                            + إذن صرف جديد
                        </a>
                    @endcan
                </div>
            </div>

            @include('transport.partials.flash')

            {{-- فلاتر البحث --}}
            <form method="GET" class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-4 flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="tr-label">بحث</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="رقم الإذن أو الشاحنة..." class="tr-input">
                </div>
                <div class="w-48">
                    <label class="tr-label">الشاحنة</label>
                    <select name="truck_id" class="tr-input">
                        <option value="">جميع الشاحنات</option>
                        @foreach ($trucks as $t)
                            <option value="{{ $t->id }}" @selected(request('truck_id') == $t->id)>{{ $t->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-44">
                    <label class="tr-label">النوع</label>
                    <select name="category" class="tr-input">
                        <option value="">الكل</option>
                        <option value="oil" @selected(request('category') === 'oil')>🛢️ زيوت وفلاتر</option>
                        <option value="tires" @selected(request('category') === 'tires')>🛞 كفرات</option>
                        <option value="spare_parts" @selected(request('category') === 'spare_parts')>⚙️ قطع غيار</option>
                        <option value="maintenance" @selected(request('category') === 'maintenance')>🔧 صيانة عامة</option>
                    </select>
                </div>
                <div class="w-36">
                    <label class="tr-label">من تاريخ</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="tr-input">
                </div>
                <div class="w-36">
                    <label class="tr-label">إلى تاريخ</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="tr-input">
                </div>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#0F1B4C] text-white text-sm font-semibold hover:bg-[#0F1B4C]/90 transition">
                    بحث
                </button>
            </form>

            {{-- جدول الأذونات --}}
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl overflow-hidden">
                <div style="overflow-x:auto">
                    <table class="w-full text-sm text-start">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold text-xs">
                                <th class="px-4 py-3 text-start">رقم الإذن</th>
                                <th class="px-4 py-3 text-start">التاريخ</th>
                                <th class="px-4 py-3 text-start">الشاحنة</th>
                                <th class="px-4 py-3 text-start">السائق</th>
                                <th class="px-4 py-3 text-start">نوع الصرف</th>
                                <th class="px-4 py-3 text-start">العداد الحالي</th>
                                <th class="px-4 py-3 text-start">الغيار القادم</th>
                                <th class="px-4 py-3 text-start">عدد الأصناف</th>
                                <th class="px-4 py-3 text-start">إجمالي التكلفة</th>
                                <th class="px-4 py-3 text-start">القيد اليومي</th>
                                <th class="px-4 py-3 text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($issues as $issue)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-4 py-3 font-bold text-[#0F1B4C]">
                                        <a href="{{ route('transport.store-issues.show', $issue) }}" class="hover:underline text-[#1456E8]">
                                            {{ $issue->issue_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $issue->issue_date->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">
                                        {{ $issue->truck?->display_name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $issue->driver?->name ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @if ($issue->expense_category === 'oil')
                                            <span class="tr-badge tr-badge-blue">🛢️ زيوت وفلاتر</span>
                                        @elseif ($issue->expense_category === 'tires')
                                            <span class="tr-badge tr-badge-amber">🛞 كفرات</span>
                                        @elseif ($issue->expense_category === 'spare_parts')
                                            <span class="tr-badge tr-badge-gray">⚙️ قطع غيار</span>
                                        @else
                                            <span class="tr-badge tr-badge-gray">{{ $issue->categoryLabel() }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 font-semibold" style="direction:ltr;text-align:start">
                                        {{ $issue->current_odometer ? number_format($issue->current_odometer) . ' كم' : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-blue-700 font-bold" style="direction:ltr;text-align:start">
                                        {{ $issue->next_oil_change_odometer ? number_format($issue->next_oil_change_odometer) . ' كم' : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-gray-600">{{ $issue->items_count }}</td>
                                    <td class="px-4 py-3 font-bold text-[#0F1B4C]" style="direction:ltr;text-align:start">
                                        {{ number_format($issue->total_amount, 2) }} ر.س
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($issue->journalEntry)
                                            <a href="{{ route('journal-entries.show', $issue->journalEntry) }}" class="text-xs text-blue-600 font-bold hover:underline">
                                                {{ $issue->journalEntry->entry_number }}
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('transport.store-issues.show', $issue) }}" class="tr-btn tr-btn-blue text-xs" title="عرض وطباعة">
                                                🖨️ عرض وطباعة
                                            </a>
                                            @can('maintenance.create')
                                                <form method="POST" action="{{ route('transport.store-issues.destroy', $issue) }}" onsubmit="return confirm('هل أنت متأكد من إلغاء إذن الصرف؟ سيتم إرجاع الكميات للمخزن وإلغاء القيد.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-rose-600 hover:text-rose-800 text-xs font-bold" title="إلغاء">
                                                        🗑️
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-4 py-12 text-center text-gray-400">
                                        لا توجد أذونات صرف مسجلة حتى الآن.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($issues->hasPages())
                    <div class="p-4 border-t border-gray-100">{{ $issues->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
