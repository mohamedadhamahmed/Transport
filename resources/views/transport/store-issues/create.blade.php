<x-app-layout>
    @include('transport.partials.styles')
    <style>
        .si-box { background:#fff; border:1px solid #e5e7eb; border-radius:1rem; padding:1.25rem; margin-bottom:1.25rem; box-shadow:0 1px 3px rgba(0,0,0,0.04); }
        .si-title { font-size:1rem; font-weight:800; color:#0F1B4C; margin-bottom:1rem; display:flex; align-items:center; gap:.5rem; }
        .si-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; }
        .odo-highlight { background:linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 100%); border:1px solid #bae6fd; border-radius:.75rem; padding:1rem; }
        .odo-result { font-size:1.35rem; font-weight:900; color:#0369a1; direction:ltr; text-align:center; margin-top:.5rem; background:#fff; padding:.5rem; border-radius:.5rem; border:1px dashed #7dd3fc; }
        .tbl-items { width:100%; border-collapse:collapse; margin-top:.75rem; }
        .tbl-items th { background:#f8fafc; color:#475569; font-weight:700; font-size:.8rem; padding:.6rem .75rem; border-bottom:1px solid #e2e8f0; text-align:start; }
        .tbl-items td { padding:.5rem .75rem; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
        .btn-pill { background:#f1f5f9; border:1px solid #cbd5e1; border-radius:2rem; padding:.25rem .75rem; font-size:.75rem; font-weight:700; cursor:pointer; transition:.15s; }
        .btn-pill:hover { background:#0F1B4C; color:#fff; border-color:#0F1B4C; }
    </style>

    <div class="py-6">
        <div class="max-w-[1500px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-[#0F1B4C]">📦 إذن صرف قطع غيار وزيوت للشاحنات</h2>
                    <p class="text-xs text-gray-500 mt-1">صرف الإطارات والزيوت وقطع الغيار من المخزن، مع قيد المخزون ومتابعة عداد غيار الزيت القادم</p>
                </div>
                <a href="{{ route('transport.store-issues.index') }}" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 transition">
                    ⬅️ قائمة الأذونات السابقة
                </a>
            </div>

            @include('transport.partials.flash')

            @if ($errors->any())
                <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-800 p-4 text-sm space-y-1">
                    <div class="font-bold">⚠️ يرجى تصحيح الأخطاء التالية:</div>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('transport.store-issues.store') }}" id="si-form">
                @csrf

                {{-- 1. البيانات الأساسية للشاحنة والصرف --}}
                <div class="si-box">
                    <div class="si-title">🚚 1. بيانات الشاحنة والصيانة</div>
                    <div class="si-grid">
                        <div>
                            <label class="tr-label">الشاحنة المستفيدة *</label>
                            <select name="truck_id" id="sel-truck" class="tr-input" required>
                                <option value="">-- اختر الشاحنة --</option>
                                @foreach ($trucks as $t)
                                    <option value="{{ $t->id }}"
                                            data-driver="{{ $t->driver_id }}"
                                            data-odometer="{{ $t->current_odometer ?? '' }}"
                                            data-next-oil="{{ $t->next_oil_change_odometer ?? '' }}"
                                            @selected(old('truck_id', request('truck_id')) == $t->id)>
                                        {{ $t->display_name }} ({{ $t->plate_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="tr-label">تاريخ الصرف *</label>
                            <input type="date" name="issue_date" value="{{ old('issue_date', now()->toDateString()) }}" class="tr-input" required>
                        </div>

                        <div>
                            <label class="tr-label">السائق (اختياري)</label>
                            <select name="driver_id" id="sel-driver" class="tr-input">
                                <option value="">-- اختياري --</option>
                                @foreach ($drivers as $d)
                                    <option value="{{ $d->id }}" @selected(old('driver_id') == $d->id)>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="tr-label">نوع الصيانة / المصروف *</label>
                            <select name="expense_category" id="sel-cat" class="tr-input" required>
                                <option value="oil" @selected(old('expense_category') === 'oil')>🛢️ زيوت وفلاتر</option>
                                <option value="tires" @selected(old('expense_category') === 'tires')>🛞 كفرات (إطارات)</option>
                                <option value="spare_parts" @selected(old('expense_category', 'spare_parts') === 'spare_parts')>⚙️ قطع غيار</option>
                                <option value="maintenance" @selected(old('expense_category') === 'maintenance')>🔧 صيانة عامة</option>
                                <option value="other" @selected(old('expense_category') === 'other')>📝 أخرى</option>
                            </select>
                        </div>

                        <div>
                            <label class="tr-label">مركز التكلفة (اختياري)</label>
                            <select name="cost_center_id" class="tr-input">
                                <option value="">-- بدون مركز تكلفة --</option>
                                @foreach ($costCenters as $cc)
                                    <option value="{{ $cc->id }}" @selected(old('cost_center_id') == $cc->id)>{{ $cc->cost_center_ar }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 2. عداد الكيلومترات ومتابعة غيار الزيت القادم --}}
                <div class="si-box odo-highlight">
                    <div class="si-title" style="color:#0369a1">⏱️ 2. عداد الكيلومترات وغيار الزيت القادم</div>
                    <div class="si-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); align-items:start;">
                        <div>
                            <label class="tr-label">قراءة العداد الحالية (كم)</label>
                            <input type="number" name="current_odometer" id="inp-current-odo" value="{{ old('current_odometer') }}" class="tr-input" placeholder="مثال: 125000" min="0">
                            <span class="text-[11px] text-gray-500">رقم الكيلومترات الحالي المسجل على طبلون الشاحنة</span>
                        </div>

                        <div id="oil-interval-box">
                            <label class="tr-label">صلاحية الزيت (مسافة الغيار كم)</label>
                            <div class="flex items-center gap-1.5 mb-1.5 flex-wrap">
                                <button type="button" class="btn-pill" onclick="setIntervalKm(4000)">4,000 كم</button>
                                <button type="button" class="btn-pill" onclick="setIntervalKm(5000)">5,000 كم</button>
                                <button type="button" class="btn-pill" onclick="setIntervalKm(10000)">10,000 كم</button>
                            </div>
                            <input type="number" name="oil_change_interval_km" id="inp-interval-km" value="{{ old('oil_change_interval_km', 5000) }}" class="tr-input" placeholder="5000" min="0">
                        </div>

                        <div>
                            <label class="tr-label">الغيار القادم عند عداد (كم)</label>
                            <input type="number" name="next_oil_change_odometer" id="inp-next-odo" value="{{ old('next_oil_change_odometer') }}" class="tr-input" placeholder="تلقائي: العداد + المسافة" min="0">
                            <div class="odo-result" id="disp-next-odo">
                                الغيار القادم: <span id="val-next-odo">-</span> كم
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. جدول الأصناف والكميات المنصرفة من المخزن --}}
                <div class="si-box">
                    <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
                        <div class="si-title" style="margin-bottom:0">📦 3. الأصناف والكميات المنصرفة من المخزن</div>
                        <button type="button" id="btn-add-item" class="px-3 py-1.5 rounded-lg bg-[#0F1B4C] text-white text-xs font-bold hover:bg-[#0F1B4C]/90 transition">
                            + إضافة صنف آخر
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mb-3">سيتم خصم الكميات المدخلة فوراً من رصيد المنتج بالمخزن واحتساب التكلفة تلقائياً</p>

                    <div style="overflow-x:auto">
                        <table class="tbl-items" id="items-table">
                            <thead>
                                <tr>
                                    <th style="min-width:280px">الصنف من المخزن</th>
                                    <th style="width:120px">الرصيد المتاح</th>
                                    <th style="width:130px">الكمية المنصرفة</th>
                                    <th style="width:140px">تكلفة الوحدة</th>
                                    <th style="width:140px">الإجمالي</th>
                                    <th style="min-width:180px">ملاحظات</th>
                                    <th style="width:50px"></th>
                                </tr>
                            </thead>
                            <tbody id="items-tbody">
                                {{-- سطر البند الافتراضي --}}
                                <tr class="item-row">
                                    <td>
                                        <select name="items[0][product_id]" class="tr-input js-prod-select" required onchange="onProductChange(this)">
                                            <option value="">-- اختر الصنف --</option>
                                            @foreach ($products as $p)
                                                <option value="{{ $p->id }}"
                                                        data-cost="{{ $p->average_cost ?: ($p->purchase_price ?: 0) }}"
                                                        data-stock="{{ $p->stock_quantity }}"
                                                        data-unit="{{ $p->unit ?: 'حبة' }}">
                                                    {{ $p->name }} (رصيد: {{ $p->stock_quantity }} {{ $p->unit ?: 'حبة' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <span class="js-stock-badge text-xs font-bold text-gray-500">-</span>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0.01" name="items[0][quantity]" value="1" class="tr-input js-qty" required oninput="calcLine(this)">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="items[0][unit_cost]" value="0" class="tr-input js-cost" required oninput="calcLine(this)">
                                    </td>
                                    <td>
                                        <input type="text" readonly class="tr-input js-line-total bg-gray-50 font-bold" value="0.00" style="direction:ltr;text-align:end">
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][notes]" class="tr-input" placeholder="مثال: أمامي يمين">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="text-rose-500 font-bold text-lg hover:text-rose-700" onclick="removeLine(this)">×</button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr style="background:#f8fafc; font-weight:800">
                                    <td colspan="4" class="text-end px-4 py-3 text-sm">إجمالي تكلفة الصرف المخزني:</td>
                                    <td class="px-4 py-3 text-sm text-[#0F1B4C]" style="direction:ltr;text-align:end">
                                        <span id="grand-total">0.00</span> ر.س
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- 4. القيد المحاسبي المخزني (مدين صيانة / دائن مخزون) --}}
                <div class="si-box">
                    <div class="si-title">⚖️ 4. توجيه القيد المحاسبي (قيد استهلاك مخزني)</div>
                    <p class="text-xs text-gray-500 mb-3">القيد لا يلمس الخزينة أو البنك؛ بل ينقص رصيد المخزون (دائن) ويثبت تكلفة الصيانة على الشاحنة (مدين)</p>
                    <div class="si-grid">
                        <div>
                            <label class="tr-label">حساب المخزون (الطرف الدائن) *</label>
                            <select name="inventory_account_id" class="tr-input">
                                @foreach ($inventoryAccounts as $acc)
                                    <option value="{{ $acc->id }}" @selected(old('inventory_account_id', $defaultInventoryId) == $acc->id)>
                                        {{ $acc->name }} ({{ $acc->account_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="tr-label">حساب مصروف الصيانة (الطرف المدين) *</label>
                            <select name="expense_account_id" class="tr-input">
                                @foreach ($expenseAccounts as $acc)
                                    <option value="{{ $acc->id }}" @selected(old('expense_account_id', $defaultExpenseId) == $acc->id)>
                                        {{ $acc->name }} ({{ $acc->account_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="tr-span-full">
                            <label class="tr-label">ملاحظات عامة على إذن الصرف</label>
                            <textarea name="notes" rows="2" class="tr-input" placeholder="أي ملاحظات فنية أو تفاصيل إضافية...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-6">
                    <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#0F1B4C] text-white font-bold text-sm hover:bg-[#0F1B4C]/90 shadow-md transition">
                        💾 حفظ إذن الصرف وخصم المخزون والقيد
                    </button>
                    <a href="{{ route('transport.store-issues.index') }}" class="px-5 py-2.5 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 transition">
                        إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- المنتجات للـ JavaScript لإنشاء أسطر جديدة --}}
    <template id="tmpl-product-options">
        <option value="">-- اختر الصنف --</option>
        @foreach ($products as $p)
            <option value="{{ $p->id }}"
                    data-cost="{{ $p->average_cost ?: ($p->purchase_price ?: 0) }}"
                    data-stock="{{ $p->stock_quantity }}"
                    data-unit="{{ $p->unit ?: 'حبة' }}">
                {{ $p->name }} (رصيد: {{ $p->stock_quantity }} {{ $p->unit ?: 'حبة' }})
            </option>
        @endforeach
    </template>

    <script>
    let rowIndex = 1;

    function setIntervalKm(km) {
        document.getElementById('inp-interval-km').value = km;
        calcNextOdo();
    }

    function calcNextOdo() {
        const cur = parseFloat(document.getElementById('inp-current-odo').value) || 0;
        const interval = parseFloat(document.getElementById('inp-interval-km').value) || 0;
        const nextInp = document.getElementById('inp-next-odo');
        const nextValDisp = document.getElementById('val-next-odo');

        if (cur > 0 && interval > 0) {
            const next = cur + interval;
            nextInp.value = next;
            nextValDisp.textContent = Number(next).toLocaleString();
        } else if (parseFloat(nextInp.value) > 0) {
            nextValDisp.textContent = Number(nextInp.value).toLocaleString();
        } else {
            nextValDisp.textContent = '-';
        }
    }

    document.getElementById('inp-current-odo').addEventListener('input', calcNextOdo);
    document.getElementById('inp-interval-km').addEventListener('input', calcNextOdo);
    document.getElementById('inp-next-odo').addEventListener('input', function() {
        const v = parseFloat(this.value) || 0;
        document.getElementById('val-next-odo').textContent = v > 0 ? Number(v).toLocaleString() : '-';
    });

    document.getElementById('sel-truck').addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (!opt || !opt.value) return;

        const driverId = opt.getAttribute('data-driver');
        const odo = opt.getAttribute('data-odometer');
        const nextOil = opt.getAttribute('data-next-oil');

        if (driverId) {
            document.getElementById('sel-driver').value = driverId;
        }
        if (odo && !document.getElementById('inp-current-odo').value) {
            document.getElementById('inp-current-odo').value = odo;
        }
        calcNextOdo();
    });

    function onProductChange(select) {
        const row = select.closest('tr');
        const opt = select.options[select.selectedIndex];
        const costInp = row.querySelector('.js-cost');
        const badge = row.querySelector('.js-stock-badge');

        if (opt && opt.value) {
            const cost = opt.getAttribute('data-cost') || 0;
            const stock = opt.getAttribute('data-stock') || 0;
            const unit = opt.getAttribute('data-unit') || '';
            costInp.value = parseFloat(cost).toFixed(2);
            badge.textContent = `${stock} ${unit}`;
            badge.className = 'js-stock-badge text-xs font-bold ' + (parseFloat(stock) <= 0 ? 'text-rose-600' : 'text-emerald-700');
        } else {
            costInp.value = '0.00';
            badge.textContent = '-';
            badge.className = 'js-stock-badge text-xs font-bold text-gray-500';
        }
        calcLine(costInp);
    }

    function calcLine(input) {
        const row = input.closest('tr');
        const qty = parseFloat(row.querySelector('.js-qty').value) || 0;
        const cost = parseFloat(row.querySelector('.js-cost').value) || 0;
        const total = (qty * cost).toFixed(2);
        row.querySelector('.js-line-total').value = total;
        calcGrandTotal();
    }

    function calcGrandTotal() {
        let total = 0;
        document.querySelectorAll('.js-line-total').forEach(inp => {
            total += parseFloat(inp.value) || 0;
        });
        document.getElementById('grand-total').textContent = total.toFixed(2);
    }

    function removeLine(btn) {
        const tbody = document.getElementById('items-tbody');
        if (tbody.querySelectorAll('tr').length <= 1) {
            alert('يجب أن يحتوي إذن الصرف على صنف واحد على الأقل.');
            return;
        }
        btn.closest('tr').remove();
        calcGrandTotal();
    }

    document.getElementById('btn-add-item').addEventListener('click', function() {
        const tbody = document.getElementById('items-tbody');
        const optionsHtml = document.getElementById('tmpl-product-options').innerHTML;
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td>
                <select name="items[${rowIndex}][product_id]" class="tr-input js-prod-select" required onchange="onProductChange(this)">
                    ${optionsHtml}
                </select>
            </td>
            <td>
                <span class="js-stock-badge text-xs font-bold text-gray-500">-</span>
            </td>
            <td>
                <input type="number" step="0.01" min="0.01" name="items[${rowIndex}][quantity]" value="1" class="tr-input js-qty" required oninput="calcLine(this)">
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rowIndex}][unit_cost]" value="0" class="tr-input js-cost" required oninput="calcLine(this)">
            </td>
            <td>
                <input type="text" readonly class="tr-input js-line-total bg-gray-50 font-bold" value="0.00" style="direction:ltr;text-align:end">
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][notes]" class="tr-input" placeholder="ملاحظة للبند">
            </td>
            <td class="text-center">
                <button type="button" class="text-rose-500 font-bold text-lg hover:text-rose-700" onclick="removeLine(this)">×</button>
            </td>
        `;
        tbody.appendChild(tr);
        rowIndex++;
    });

    // تشغيل الحساب الأولي
    calcNextOdo();
    calcGrandTotal();
    </script>
</x-app-layout>
