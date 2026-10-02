<x-app-layout>
    @include('transport.partials.styles')
    <style>
        .rep-stat { background:#fff; border:1px solid #e5e7eb; border-radius:.9rem; padding:1.2rem; display:flex; flex-direction:column; gap:.3rem; box-shadow:0 1px 3px rgba(0,0,0,0.04); }
        .rep-stat .v { font-size:1.6rem; font-weight:900; color:#0F1B4C; }
        .rep-stat .l { font-size:.8rem; color:#6b7280; font-weight:700; }
        .rep-grid-4 { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; }
    </style>

    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-[#0F1B4C]">📊 تقرير صرف قطع الغيار والزيوت للشاحنات</h2>
                    <p class="text-xs text-gray-500 mt-1">متابعة استهلاك المواد وقطع الغيار للشاحنات ومراقبة عدادات الزيت القادم</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('transport.store-issues.index') }}" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 transition">
                        📄 أذونات الصرف
                    </a>
                    @can('maintenance.create')
                        <a href="{{ route('transport.store-issues.create') }}" class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-bold hover:bg-[#0F1B4C]/90 transition">
                            + إذن صرف جديد
                        </a>
                    @endcan
                </div>
            </div>

            {{-- فلاتر البحث --}}
            <form method="GET" class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-4 flex flex-wrap gap-3 items-end">
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
                <div class="w-48">
                    <label class="tr-label">الصنف المنصرف</label>
                    <select name="product_id" class="tr-input">
                        <option value="">جميع الأصناف</option>
                        @foreach ($allProducts as $pr)
                            <option value="{{ $pr->id }}" @selected(request('product_id') == $pr->id)>{{ $pr->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-36">
                    <label class="tr-label">من تاريخ</label>
                    <input type="date" name="date_from" value="{{ $from }}" class="tr-input">
                </div>
                <div class="w-36">
                    <label class="tr-label">إلى تاريخ</label>
                    <input type="date" name="date_to" value="{{ $to }}" class="tr-input">
                </div>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#0F1B4C] text-white text-sm font-semibold hover:bg-[#0F1B4C]/90 transition">
                    تطبيق الفلترة
                </button>
            </form>

            {{-- كروت الإحصائيات --}}
            <div class="rep-grid-4">
                <div class="rep-stat" style="border-start-width:4px;border-start-color:#0F1B4C">
                    <div class="v" style="direction:ltr;text-align:start">{{ number_format($totalCost, 2) }} <span class="text-sm font-bold text-gray-500">ر.س</span></div>
                    <div class="l">إجمالي تكلفة المنصرف للفترة</div>
                </div>
                <div class="rep-stat" style="border-start-width:4px;border-start-color:#2563eb">
                    <div class="v" style="color:#2563eb;direction:ltr;text-align:start">{{ number_format($oilCost, 2) }} <span class="text-sm font-bold text-gray-500">ر.س</span></div>
                    <div class="l">🛢️ تكلفة الزيوت والفلاتر</div>
                </div>
                <div class="rep-stat" style="border-start-width:4px;border-start-color:#d97706">
                    <div class="v" style="color:#d97706;direction:ltr;text-align:start">{{ number_format($tiresCost, 2) }} <span class="text-sm font-bold text-gray-500">ر.س</span></div>
                    <div class="l">🛞 تكلفة الكفرات (الإطارات)</div>
                </div>
                <div class="rep-stat" style="border-start-width:4px;border-start-color:#059669">
                    <div class="v" style="color:#059669;direction:ltr;text-align:start">{{ number_format($sparePartsCost, 2) }} <span class="text-sm font-bold text-gray-500">ر.س</span></div>
                    <div class="l">⚙️ تكلفة قطع الغيار المتنوعة</div>
                </div>
            </div>

            {{-- 1. جدول متابعة عدادات ومواعيد غيار الزيت للشاحنات --}}
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-lg text-[#0F1B4C]">🛢️ متابعة عدادات ومواعيد غيار الزيت لأسطول الشاحنات</h3>
                        <p class="text-xs text-gray-500">مراقبة العداد الحالي للشاحنة مقارنة بعداد غيار الزيت القادم والتنبيه بموعد الاستحقاق</p>
                    </div>
                </div>

                <div style="overflow-x:auto">
                    <table class="w-full text-sm text-start">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold text-xs">
                                <th class="px-4 py-3 text-start">الشاحنة</th>
                                <th class="px-4 py-3 text-start">السائق الافتراضي</th>
                                <th class="px-4 py-3 text-start">العداد الحالي</th>
                                <th class="px-4 py-3 text-start">تاريخ آخر غيار</th>
                                <th class="px-4 py-3 text-start">الغيار القادم عند عداد</th>
                                <th class="px-4 py-3 text-start">المتبقي على الغيار</th>
                                <th class="px-4 py-3 text-center">حالة الزيت</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($oilStatusList as $row)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-4 py-3 font-bold text-gray-900">
                                        {{ $row['truck']->display_name }} ({{ $row['truck']->plate_number }})
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row['truck']->driver?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 font-bold text-gray-700" style="direction:ltr;text-align:start">
                                        {{ $row['current'] ? number_format($row['current']) . ' كم' : 'غير مسجل' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $row['last_date'] ? \Carbon\Carbon::parse($row['last_date'])->format('Y-m-d') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-black text-blue-700" style="direction:ltr;text-align:start">
                                        {{ $row['next'] ? number_format($row['next']) . ' كم' : '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-bold" style="direction:ltr;text-align:start">
                                        @if ($row['remaining'] !== null)
                                            @if ($row['remaining'] <= 0)
                                                <span class="text-rose-600 font-black">متأخر بـ {{ number_format(abs($row['remaining'])) }} كم</span>
                                            @else
                                                <span class="{{ $row['remaining'] <= 500 ? 'text-amber-600' : 'text-emerald-700' }}">
                                                    باقي {{ number_format($row['remaining']) }} كم
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($row['status'] === 'overdue')
                                            <span class="px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800 animate-pulse">
                                                🚨 حان موعد الغيار فوراً
                                            </span>
                                        @elseif ($row['status'] === 'soon')
                                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                                ⚠️ اقترب الموعد (&lt; 500 كم)
                                            </span>
                                        @elseif ($row['status'] === 'ok')
                                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                                🟢 سليم
                                            </span>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">
                                                ⚪ غير مسجل
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 2. جدول تفاصيل الأذونات والأصناف المنصرفة للفترة --}}
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-5">
                <div class="mb-4">
                    <h3 class="font-bold text-lg text-[#0F1B4C]">📦 سجل أذونات الصرف والأصناف المنصرفة بالتفصيل</h3>
                    <p class="text-xs text-gray-500">الأصناف والكميات المنصرفة من المستودع للشاحنات خلال الفترة المحددة</p>
                </div>

                <div style="overflow-x:auto">
                    <table class="w-full text-sm text-start">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold text-xs">
                                <th class="px-3 py-3 text-start">التاريخ</th>
                                <th class="px-3 py-3 text-start">رقم الإذن</th>
                                <th class="px-3 py-3 text-start">الشاحنة</th>
                                <th class="px-3 py-3 text-start">نوع الصرف</th>
                                <th class="px-3 py-3 text-start">الأصناف والكميات المنصرفة</th>
                                <th class="px-3 py-3 text-start">العداد وقت الصرف</th>
                                <th class="px-3 py-3 text-start">الغيار القادم</th>
                                <th class="px-3 py-3 text-start">إجمالي التكلفة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($issues as $is)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-3 py-3 text-gray-600">{{ $is->issue_date->format('Y-m-d') }}</td>
                                    <td class="px-3 py-3 font-bold text-blue-700">
                                        <a href="{{ route('transport.store-issues.show', $is) }}" class="hover:underline">
                                            {{ $is->issue_number }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-3 font-bold text-gray-900">{{ $is->truck?->display_name }}</td>
                                    <td class="px-3 py-3">
                                        <span class="text-xs font-bold text-gray-700">{{ $is->categoryLabel() }}</span>
                                    </td>
                                    <td class="px-3 py-3">
                                        <ul class="space-y-1">
                                            @foreach ($is->items as $it)
                                                <li class="text-xs text-gray-800">
                                                    • <span class="font-bold">{{ $it->product?->name }}</span>:
                                                    <span class="text-blue-700 font-extrabold">{{ (float) $it->quantity }} {{ $it->product?->unit ?: 'حبة' }}</span>
                                                    × {{ number_format($it->unit_cost, 2) }} = {{ number_format($it->total_cost, 2) }} ر.س
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="px-3 py-3 text-gray-700 font-semibold" style="direction:ltr;text-align:start">
                                        {{ $is->current_odometer ? number_format($is->current_odometer) . ' كم' : '-' }}
                                    </td>
                                    <td class="px-3 py-3 text-blue-700 font-bold" style="direction:ltr;text-align:start">
                                        {{ $is->next_oil_change_odometer ? number_format($is->next_oil_change_odometer) . ' كم' : '-' }}
                                    </td>
                                    <td class="px-3 py-3 font-black text-[#0F1B4C]" style="direction:ltr;text-align:start">
                                        {{ number_format($is->total_amount, 2) }} ر.س
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-gray-400">
                                        لا توجد بيانات صرف مسجلة في هذه الفترة.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
