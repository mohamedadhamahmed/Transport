<x-app-layout>
    @include('transport.partials.styles')
    <style>
        .si-print-card { background:#fff; border:1px solid #e2e8f0; border-radius:1rem; padding:2rem; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); }
        .meta-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; background:#f8fafc; border:1px solid #e2e8f0; border-radius:.75rem; padding:1.25rem; margin:1.5rem 0; }
        .meta-item .lbl { font-size:.75rem; color:#64748b; font-weight:700; margin-bottom:.25rem; }
        .meta-item .val { font-size:.95rem; color:#0F1B4C; font-weight:800; }
        .oil-highlight-card { background:linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 100%); border:1px solid #7dd3fc; border-radius:.75rem; padding:1rem; margin-bottom:1.5rem; display:flex; justify-content:space-around; align-items:center; flex-wrap:wrap; gap:1rem; }
        .print-table { width:100%; border-collapse:collapse; margin-top:1.5rem; }
        .print-table th { background:#0F1B4C; color:#fff; padding:.75rem; font-size:.85rem; font-weight:700; text-align:start; border:1px solid #0F1B4C; }
        .print-table td { padding:.75rem; border:1px solid #cbd5e1; font-size:.85rem; vertical-align:middle; }
        .sig-box { display:grid; grid-template-columns:repeat(3, 1fr); gap:2rem; margin-top:3.5rem; padding-top:1.5rem; border-top:1px dashed #cbd5e1; text-align:center; }
        .sig-line { margin-top:3rem; border-top:1px solid #94a3b8; }
        @media print {
            body * { visibility: hidden; }
            .si-printable, .si-printable * { visibility: visible; }
            .si-printable { position: absolute; left: 0; top: 0; width: 100%; padding: 0 !important; margin: 0 !important; box-shadow: none !important; border: none !important; }
            .no-print { display: none !important; }
        }
    </style>

    <div class="py-6">
        <div class="max-w-[1100px] mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- أزرار التحكم العلوية --}}
            <div class="flex items-center justify-between flex-wrap gap-3 no-print">
                <div class="flex items-center gap-2">
                    <a href="{{ route('transport.store-issues.index') }}" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 transition">
                        ⬅️ عودة للقائمة
                    </a>
                    <a href="{{ route('transport.store-issues.create') }}" class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-semibold hover:bg-[#0F1B4C]/90 transition">
                        + إذن صرف جديد
                    </a>
                </div>
                <button type="button" onclick="window.print()" class="px-5 py-2 rounded-lg bg-emerald-600 text-white font-bold text-sm hover:bg-emerald-700 shadow-md transition flex items-center gap-2">
                    🖨️ طباعة إذن الصرف
                </button>
            </div>

            @include('transport.partials.flash')

            {{-- المستند القابل للطباعة --}}
            <div class="si-print-card si-printable">

                {{-- ترويسة المستند --}}
                <div class="flex items-center justify-between border-b-2 border-[#0F1B4C] pb-4">
                    <div>
                        <h1 class="text-2xl font-black text-[#0F1B4C]">إذن صرف قطع غيار وزيوت</h1>
                        <p class="text-xs text-gray-500 font-bold mt-1">قسم النقليات وإدارة الأسطول والمستودعات</p>
                    </div>
                    <div class="text-end">
                        <div class="text-lg font-black text-[#1456E8]">{{ $issue->issue_number }}</div>
                        <div class="text-xs text-gray-500 font-bold">التاريخ: {{ $issue->issue_date->format('Y-m-d') }}</div>
                    </div>
                </div>

                {{-- بطاقة بيانات العداد وغيار الزيت --}}
                @if ($issue->current_odometer || $issue->next_oil_change_odometer)
                    <div class="oil-highlight-card mt-4">
                        <div class="text-center">
                            <span class="text-xs text-gray-600 font-bold block">عداد الكيلومترات وقت الصرف</span>
                            <span class="text-xl font-black text-gray-900">{{ number_format($issue->current_odometer) }} كم</span>
                        </div>
                        @if ($issue->oil_change_interval_km)
                            <div class="text-center">
                                <span class="text-xs text-gray-600 font-bold block">صلاحية الزيت</span>
                                <span class="text-base font-bold text-blue-700">{{ number_format($issue->oil_change_interval_km) }} كم</span>
                            </div>
                        @endif
                        @if ($issue->next_oil_change_odometer)
                            <div class="text-center bg-white px-4 py-2 rounded-lg border border-blue-200 shadow-sm">
                                <span class="text-xs text-blue-700 font-bold block">🛢️ غيار الزيت القادم عند عداد</span>
                                <span class="text-2xl font-black text-[#1456E8]">{{ number_format($issue->next_oil_change_odometer) }} كم</span>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- تفاصيل الشاحنة والتوجيه --}}
                <div class="meta-grid">
                    <div class="meta-item">
                        <div class="lbl">الشاحنة المستفيدة</div>
                        <div class="val">{{ $issue->truck?->display_name ?? '-' }} ({{ $issue->truck?->plate_number }})</div>
                    </div>
                    <div class="meta-item">
                        <div class="lbl">السائق</div>
                        <div class="val">{{ $issue->driver?->name ?? 'غير محدد' }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="lbl">نوع الصيانة</div>
                        <div class="val">{{ $issue->categoryLabel() }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="lbl">مركز التكلفة</div>
                        <div class="val">{{ $issue->costCenter?->cost_center_ar ?? 'عام' }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="lbl">حساب المخزون (الدائن)</div>
                        <div class="val text-xs">{{ $issue->inventoryAccount?->name ?? 'المخزون السلعي' }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="lbl">القيد المحاسبي المرتبط</div>
                        <div class="val text-xs text-blue-700">
                            @if ($issue->journalEntry)
                                <a href="{{ route('journal-entries.show', $issue->journalEntry) }}" class="no-print hover:underline font-bold">{{ $issue->journalEntry->entry_number }}</a>
                                <span class="hidden print:inline font-bold">{{ $issue->journalEntry->entry_number }}</span>
                            @else
                                غير مرتبط
                            @endif
                        </div>
                    </div>
                </div>

                {{-- جدول الأصناف المنصرفة --}}
                <table class="print-table">
                    <thead>
                        <tr>
                            <th style="width:40px;text-align:center">#</th>
                            <th>اسم الصنف والبيان</th>
                            <th style="width:110px;text-align:center">كود الصنف</th>
                            <th style="width:110px;text-align:center">الكمية المنصرفة</th>
                            <th style="width:130px;text-align:end">تكلفة الوحدة</th>
                            <th style="width:140px;text-align:end">إجمالي التكلفة</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($issue->items as $idx => $item)
                            <tr>
                                <td style="text-align:center;font-weight:700">{{ $idx + 1 }}</td>
                                <td class="font-bold text-gray-900">{{ $item->product?->name }}</td>
                                <td style="text-align:center;color:#64748b;font-family:monospace">{{ $item->product?->code ?: '-' }}</td>
                                <td style="text-align:center;font-weight:800;color:#0F1B4C">
                                    {{ (float) $item->quantity }} {{ $item->product?->unit ?: 'حبة' }}
                                </td>
                                <td style="text-align:end;direction:ltr">{{ number_format($item->unit_cost, 2) }} ر.س</td>
                                <td style="text-align:end;direction:ltr;font-weight:800;color:#0F1B4C">{{ number_format($item->total_cost, 2) }} ر.س</td>
                                <td class="text-xs text-gray-600">{{ $item->notes ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8fafc">
                            <td colspan="5" style="text-align:end;font-weight:900;font-size:.95rem;padding:.9rem">إجمالي قيمة المواد والقطع المنصرفة:</td>
                            <td style="text-align:end;direction:ltr;font-weight:900;font-size:1.1rem;color:#0F1B4C;padding:.9rem">
                                {{ number_format($issue->total_amount, 2) }} ر.س
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                @if ($issue->notes)
                    <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-700">
                        <span class="font-bold">ملاحظات:</span> {{ $issue->notes }}
                    </div>
                @endif

                {{-- توقيعات الاعتماد --}}
                <div class="sig-box">
                    <div>
                        <span class="text-xs font-bold text-gray-700">أمين المستودع (القائم بالصرف)</span>
                        <div class="sig-line">الاسم والتوقيع</div>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-700">الفني / مستلم القطع</span>
                        <div class="sig-line">الاسم والتوقيع</div>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-700">المشرف / الإدارة المالية</span>
                        <div class="sig-line">الاعتماد والتوقيع</div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
