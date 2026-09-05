{{-- مودال "حاسبة الضريبة والخصم + آلة حاسبة عادية" - أداة سريعة مستقلة
     لموظف الحسابات، من غير ما تأثر على بيانات الفورم نفسه (الفاتورة/
     فاتورة الشراء/عرض السعر/سند التسليم). المودال ده مشترك (@include)
     بين شاشات المبيعات والمشتريات والتسعيرات والتسليمات وتسليم منتج،
     فلازم يتحط بره وسم <form> الصفحة الأساسي عشان زرار Enter جوه حقوله
     ميعملش submit للفورم الأساسي (وضفنا كمان حارس onkeydown على كل حقل
     رقمي فيه احتياطًا).

     ملحوظة عن مشكلة "select بيظهر فوق المودال": السبب الحقيقي إن كل شاشة
     من الشاشات دي (فواتير المبيعات/المشتريات/عروض الأسعار/سندات التسليم)
     فيها <style> خاص بيها بيحط z-index: 9999 على .ts-dropdown (قايمة
     TomSelect بتاعة اختيار العميل/المورد)، وده رقم أعلى بكتير من أي
     كلاس Tailwind عادي زي z-50 أو حتى z-[60]. عشان كده استخدمنا هنا
     inline style بقيمة z-index أعلى من 9999 (10000) عشان نضمن إن
     المودال ده يفضل دايمًا فوق أي قايمة TomSelect في الصفحة، من غير ما
     نعتمد على كلاس Tailwind ممكن يتأثر بمشاكل الـ build.

     ملحوظة تانية عن مشكلة "الآلة شكلها طالع بايظ" (الأزرار طلعت في عمود
     واحد بدل شبكة): السبب هنا نفس فكرة الملحوظة اللي فوق بالظبط - المشروع
     بيستخدم Vite build لملفات Tailwind (@vite في layouts/head.blade.php)،
     يعني ملف الـ CSS النهائي بيتولد مرة واحدة وبيحتوي بس على الكلاسات
     اللي كانت موجودة في الكود وقت آخر build. كلاسات زي grid-cols-3 و
     grid-cols-4 و col-span-2 (من غير md:) ماكانتش مستخدمة في أي حتة تانية
     في المشروع قبل كده، فمبتظهرش خالص. عشان كده هنا استخدمنا CSS عادي
     مكتوب باليد جوه <style> تحت (شغال دايمًا مهما كان وضع الـ build)
     بدل ما نعتمد على كلاسات Tailwind الجديدة دي. --}}
<style>
    #tax-calculator-modal .tax-calc-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.5rem;
    }
    #tax-calculator-modal .tax-calc-grid-4 {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.5rem;
    }
    #tax-calculator-modal .tax-calc-span-2 {
        grid-column: span 2 / span 2;
    }
    #tax-calculator-modal #tax-calc-plain-display {
        text-align: left;
    }
    #tax-calculator-modal #tax-calc-plain-section > * + * {
        margin-top: 0.75rem;
    }
    #tax-calculator-modal .calc-btn {
        transition: filter 0.15s ease;
    }
    #tax-calculator-modal .calc-btn:hover {
        filter: brightness(0.94);
    }
    #tax-calculator-modal #tax-calc-results-add > * + *,
    #tax-calculator-modal #tax-calc-results-extract > * + * {
        margin-top: 0.5rem;
    }
    @media (min-width: 640px) {
        #tax-calculator-modal .tax-calc-mode-btn {
            font-size: 0.875rem;
            line-height: 1.25rem;
        }
    }
</style>
<div id="tax-calculator-modal" class="fixed inset-0 bg-black/50 z-50 p-4 hidden items-center justify-center" style="z-index: 10000;">
    <div class="bg-white rounded-xl w-full max-w-md my-8 shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-white/10 bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] rounded-t-xl">
            <h3 class="font-semibold text-white flex items-center gap-2">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="2" width="16" height="20" rx="2" />
                    <path d="M8 6h8M8 10h.01M12 10h.01M16 10h.01M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01" />
                </svg>
                الآلة الحاسبة
            </h3>
            <button type="button" id="tax-calc-close-btn" class="text-white/60 hover:text-white transition">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M18 6 6 18M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="p-6 space-y-4">
            <div class="tax-calc-grid-3">
                <button type="button" id="tax-calc-mode-plain"
                        class="tax-calc-mode-btn px-2 py-2 rounded-lg text-xs font-medium transition bg-[#0F1B4C] text-white">
                    آلة حاسبة عادية
                </button>
                <button type="button" id="tax-calc-mode-add"
                        class="tax-calc-mode-btn px-2 py-2 rounded-lg text-xs font-medium transition bg-gray-100 text-gray-600">
                    إضافة الضريبة
                </button>
                <button type="button" id="tax-calc-mode-extract"
                        class="tax-calc-mode-btn px-2 py-2 rounded-lg text-xs font-medium transition bg-gray-100 text-gray-600">
                    استخراج الضريبة
                </button>
            </div>

            {{-- ============ الآلة الحاسبة العادية ============ --}}
            <div id="tax-calc-plain-section">
                <div id="tax-calc-plain-display"
                     class="w-full rounded-lg border border-gray-200 bg-[#0F1B4C]/5 px-4 py-4 text-2xl font-semibold text-[#0F1B4C] overflow-x-auto whitespace-nowrap"
                     dir="ltr">0</div>
                <div class="tax-calc-grid-4" id="tax-calc-keypad">
                    <button type="button" data-calc="C" class="calc-btn tax-calc-span-2 py-3 rounded-lg bg-red-50 text-red-600 font-semibold transition">C</button>
                    <button type="button" data-calc="back" class="calc-btn py-3 rounded-lg bg-gray-100 text-gray-700 font-semibold transition">⌫</button>
                    <button type="button" data-calc="/" class="calc-btn py-3 rounded-lg bg-[#0F1B4C]/5 text-[#0F1B4C] font-semibold transition">÷</button>

                    <button type="button" data-calc="7" class="calc-btn py-3 rounded-lg bg-gray-50 text-gray-800 font-semibold transition">7</button>
                    <button type="button" data-calc="8" class="calc-btn py-3 rounded-lg bg-gray-50 text-gray-800 font-semibold transition">8</button>
                    <button type="button" data-calc="9" class="calc-btn py-3 rounded-lg bg-gray-50 text-gray-800 font-semibold transition">9</button>
                    <button type="button" data-calc="*" class="calc-btn py-3 rounded-lg bg-[#0F1B4C]/5 text-[#0F1B4C] font-semibold transition">×</button>

                    <button type="button" data-calc="4" class="calc-btn py-3 rounded-lg bg-gray-50 text-gray-800 font-semibold transition">4</button>
                    <button type="button" data-calc="5" class="calc-btn py-3 rounded-lg bg-gray-50 text-gray-800 font-semibold transition">5</button>
                    <button type="button" data-calc="6" class="calc-btn py-3 rounded-lg bg-gray-50 text-gray-800 font-semibold transition">6</button>
                    <button type="button" data-calc="-" class="calc-btn py-3 rounded-lg bg-[#0F1B4C]/5 text-[#0F1B4C] font-semibold transition">−</button>

                    <button type="button" data-calc="1" class="calc-btn py-3 rounded-lg bg-gray-50 text-gray-800 font-semibold transition">1</button>
                    <button type="button" data-calc="2" class="calc-btn py-3 rounded-lg bg-gray-50 text-gray-800 font-semibold transition">2</button>
                    <button type="button" data-calc="3" class="calc-btn py-3 rounded-lg bg-gray-50 text-gray-800 font-semibold transition">3</button>
                    <button type="button" data-calc="+" class="calc-btn py-3 rounded-lg bg-[#0F1B4C]/5 text-[#0F1B4C] font-semibold transition">+</button>

                    <button type="button" data-calc="0" class="calc-btn tax-calc-span-2 py-3 rounded-lg bg-gray-50 text-gray-800 font-semibold transition">0</button>
                    <button type="button" data-calc="." class="calc-btn py-3 rounded-lg bg-gray-50 text-gray-800 font-semibold transition">.</button>
                    <button type="button" data-calc="=" class="calc-btn py-3 rounded-lg bg-[#F5811E] text-white font-semibold transition">=</button>
                </div>
            </div>

            {{-- ============ حاسبة الضريبة والخصم (إضافة/استخراج) ============ --}}
            <div id="tax-calc-tax-section" class="hidden space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" id="tax-calc-amount-label">المبلغ (قبل الضريبة)</label>
                    <input type="number" id="tax-calc-amount" step="0.01" min="0" value="0" inputmode="decimal"
                           onkeydown="if(event.key==='Enter'){event.preventDefault();}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">نسبة الضريبة %</label>
                        <input type="number" id="tax-calc-tax-rate" step="0.01" min="0" value="15" inputmode="decimal"
                               onkeydown="if(event.key==='Enter'){event.preventDefault();}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div id="tax-calc-discount-field">
                        <label class="block text-sm font-medium text-gray-700 mb-1">نسبة الخصم %</label>
                        <input type="number" id="tax-calc-discount-rate" step="0.01" min="0" value="0" inputmode="decimal"
                               onkeydown="if(event.key==='Enter'){event.preventDefault();}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                </div>

                {{-- نتائج وضع "إضافة الضريبة" --}}
                <div id="tax-calc-results-add" class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">المبلغ قبل الخصم</span><span id="tax-calc-out-before-discount" class="font-medium text-gray-800">0.00</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">قيمة الخصم</span><span id="tax-calc-out-discount" class="font-medium text-red-600">0.00</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">المبلغ بعد الخصم</span><span id="tax-calc-out-after-discount" class="font-medium text-gray-800">0.00</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">قيمة الضريبة</span><span id="tax-calc-out-tax-add" class="font-medium text-gray-800">0.00</span></div>
                    <div class="flex justify-between border-t border-[#0F1B4C]/10" style="padding-top: 0.5rem;">
                        <span class="font-semibold text-[#0F1B4C]">الإجمالي النهائي</span>
                        <span id="tax-calc-out-total-add" class="font-bold text-[#0F1B4C] text-base">0.00</span>
                    </div>
                </div>

                {{-- نتائج وضع "استخراج الضريبة" --}}
                <div id="tax-calc-results-extract" class="hidden bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">المبلغ قبل الضريبة</span><span id="tax-calc-out-before-tax" class="font-medium text-gray-800">0.00</span></div>
                    <div class="flex justify-between border-t border-[#0F1B4C]/10" style="padding-top: 0.5rem;">
                        <span class="font-semibold text-[#0F1B4C]">قيمة الضريبة</span>
                        <span id="tax-calc-out-tax-extract" class="font-bold text-[#0F1B4C] text-base">0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('tax-calculator-modal');
    if (!modal) {
        return;
    }

    var openBtn = document.getElementById('open-tax-calculator-btn');
    var closeBtn = document.getElementById('tax-calc-close-btn');
    var modePlainBtn = document.getElementById('tax-calc-mode-plain');
    var modeAddBtn = document.getElementById('tax-calc-mode-add');
    var modeExtractBtn = document.getElementById('tax-calc-mode-extract');
    var plainSection = document.getElementById('tax-calc-plain-section');
    var taxSection = document.getElementById('tax-calc-tax-section');
    var amountInput = document.getElementById('tax-calc-amount');
    var amountLabel = document.getElementById('tax-calc-amount-label');
    var taxRateInput = document.getElementById('tax-calc-tax-rate');
    var discountRateInput = document.getElementById('tax-calc-discount-rate');
    var discountField = document.getElementById('tax-calc-discount-field');
    var resultsAdd = document.getElementById('tax-calc-results-add');
    var resultsExtract = document.getElementById('tax-calc-results-extract');

    var outBeforeDiscount = document.getElementById('tax-calc-out-before-discount');
    var outDiscount = document.getElementById('tax-calc-out-discount');
    var outAfterDiscount = document.getElementById('tax-calc-out-after-discount');
    var outTaxAdd = document.getElementById('tax-calc-out-tax-add');
    var outTotalAdd = document.getElementById('tax-calc-out-total-add');
    var outBeforeTax = document.getElementById('tax-calc-out-before-tax');
    var outTaxExtract = document.getElementById('tax-calc-out-tax-extract');

    var mode = 'plain';

    // ============ آلة حاسبة عادية ============
    var plainDisplay = document.getElementById('tax-calc-plain-display');
    var plainExpr = '';
    var plainJustEvaluated = false;

    function plainRender() {
        plainDisplay.textContent = plainExpr === '' ? '0' : plainExpr;
    }

    function plainInput(key) {
        if (key === 'C') {
            plainExpr = '';
            plainJustEvaluated = false;
            plainRender();
            return;
        }
        if (key === 'back') {
            plainExpr = plainExpr.slice(0, -1);
            plainRender();
            return;
        }
        if (key === '=') {
            try {
                var sanitized = plainExpr.replace(/[^0-9+\-*/.() ]/g, '');
                if (sanitized === '') {
                    return;
                }
                var result = Function('"use strict"; return (' + sanitized + ')')();
                if (typeof result !== 'number' || !isFinite(result)) {
                    plainExpr = 'خطأ';
                } else {
                    plainExpr = String(Math.round(result * 1e10) / 1e10);
                }
                plainJustEvaluated = true;
            } catch (e) {
                plainExpr = 'خطأ';
                plainJustEvaluated = true;
            }
            plainRender();
            return;
        }

        var isOperator = ['+', '-', '*', '/'].indexOf(key) !== -1;
        if (plainJustEvaluated) {
            plainExpr = (isOperator && plainExpr !== 'خطأ') ? plainExpr : '';
            plainJustEvaluated = false;
        }
        if (plainExpr === 'خطأ') {
            plainExpr = '';
        }
        plainExpr += key;
        plainRender();
    }

    document.querySelectorAll('#tax-calc-keypad .calc-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            plainInput(btn.dataset.calc);
        });
    });

    // ============ حاسبة الضريبة والخصم ============
    function fmt(n) {
        n = isFinite(n) ? n : 0;
        return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function setMode(newMode) {
        mode = newMode;

        [modePlainBtn, modeAddBtn, modeExtractBtn].forEach(function (btn) {
            btn.classList.remove('bg-[#0F1B4C]', 'text-white');
            btn.classList.add('bg-gray-100', 'text-gray-600');
        });
        var activeBtn = mode === 'plain' ? modePlainBtn : (mode === 'add' ? modeAddBtn : modeExtractBtn);
        activeBtn.classList.remove('bg-gray-100', 'text-gray-600');
        activeBtn.classList.add('bg-[#0F1B4C]', 'text-white');

        plainSection.classList.toggle('hidden', mode !== 'plain');
        taxSection.classList.toggle('hidden', mode === 'plain');

        if (mode !== 'plain') {
            var isAdd = mode === 'add';
            discountField.classList.toggle('hidden', !isAdd);
            resultsAdd.classList.toggle('hidden', !isAdd);
            resultsExtract.classList.toggle('hidden', isAdd);
            amountLabel.textContent = isAdd ? 'المبلغ (قبل الضريبة)' : 'المبلغ (شامل الضريبة)';
            calculateTax();
        }
    }

    function calculateTax() {
        var amount = parseFloat(amountInput.value) || 0;
        var taxRate = parseFloat(taxRateInput.value) || 0;

        if (mode === 'add') {
            var discountRate = parseFloat(discountRateInput.value) || 0;
            var beforeDiscount = amount;
            var discount = beforeDiscount * (discountRate / 100);
            var afterDiscount = beforeDiscount - discount;
            var tax = afterDiscount * (taxRate / 100);
            var total = afterDiscount + tax;

            outBeforeDiscount.textContent = fmt(beforeDiscount);
            outDiscount.textContent = fmt(discount);
            outAfterDiscount.textContent = fmt(afterDiscount);
            outTaxAdd.textContent = fmt(tax);
            outTotalAdd.textContent = fmt(total);
        } else if (mode === 'extract') {
            var totalInclusive = amount;
            var beforeTax = taxRate > 0 ? (totalInclusive / (1 + (taxRate / 100))) : totalInclusive;
            var taxAmount = totalInclusive - beforeTax;

            outBeforeTax.textContent = fmt(beforeTax);
            outTaxExtract.textContent = fmt(taxAmount);
        }
    }

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setMode('plain');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    if (openBtn) {
        openBtn.addEventListener('click', openModal);
    }
    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeModal();
        }
    });
    modePlainBtn.addEventListener('click', function () { setMode('plain'); });
    modeAddBtn.addEventListener('click', function () { setMode('add'); });
    modeExtractBtn.addEventListener('click', function () { setMode('extract'); });
    amountInput.addEventListener('input', calculateTax);
    taxRateInput.addEventListener('input', calculateTax);
    discountRateInput.addEventListener('input', calculateTax);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
})();
</script>
