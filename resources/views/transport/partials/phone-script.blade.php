{{-- فحص رقم الجوال السعودي وانت بتكتب. أي input عليه data-sa-phone:
     - بيحوّل الأرقام العربية لإنجليزي، يشيل المسافات، ويحوّل +966/00966/966 لـ 05
     - يظهر تحته "✓ رقم صحيح" + لينك "جرّب واتساب" أو "✗ رقم غلط"
     - لو الرقم غلط الفورم ميتبعتش (والسيرفر كمان بيرفضه) --}}
@once
<style>
    .sa-phone-hint { font-size:.75rem; margin-top:.25rem; min-height:1rem; }
    .sa-phone-hint.ok { color:#15803d; }
    .sa-phone-hint.bad { color:#be123c; }
    .sa-phone-hint a { color:#15803d; font-weight:700; margin-inline-start:.5rem; text-decoration:underline; }
    input.sa-bad { border-color:#e11d48 !important; box-shadow:0 0 0 1px #e11d48 !important; }
</style>
<script>
window.SaPhone = (function () {
    const map = {'٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9','۰':'0','۱':'1','۲':'2','۳':'3','۴':'4','۵':'5','۶':'6','۷':'7','۸':'8','۹':'9'};
    function normalize(v) {
        v = String(v || '').replace(/[٠-٩۰-۹]/g, d => map[d]).replace(/[^\d+]/g, '');
        if (v.startsWith('+966')) v = '0' + v.slice(4);
        else if (v.startsWith('00966')) v = '0' + v.slice(5);
        else if (v.startsWith('966') && v.length === 12) v = '0' + v.slice(3);
        else if (v.startsWith('5') && v.length === 9) v = '0' + v;
        return v.replace(/\+/g, '');
    }
    const isValid = v => /^05\d{8}$/.test(normalize(v));
    function check(input) {
        let hint = input.parentElement.querySelector('.sa-phone-hint');
        if (!hint) { hint = document.createElement('div'); hint.className = 'sa-phone-hint'; input.insertAdjacentElement('afterend', hint); }
        const v = normalize(input.value);
        if (!v) { hint.className = 'sa-phone-hint bad'; hint.textContent = input.required ? '{{ __('transport.phone_required') }}' : ''; input.classList.toggle('sa-bad', input.required); return !input.required; }
        if (isValid(v)) {
            hint.className = 'sa-phone-hint ok';
            hint.innerHTML = '✓ {{ __('transport.phone_ok') }} <a target="_blank" rel="noopener" href="https://wa.me/966' + v.slice(1) + '">{{ __('transport.try_whatsapp') }}</a>';
            input.classList.remove('sa-bad');
            return true;
        }
        hint.className = 'sa-phone-hint bad';
        hint.textContent = '✗ {{ __('transport.phone_bad') }}';
        input.classList.add('sa-bad');
        return false;
    }
    function bind(input) {
        if (input.dataset.saBound) return; input.dataset.saBound = 1;
        input.setAttribute('inputmode', 'tel'); input.setAttribute('dir', 'ltr'); input.setAttribute('maxlength', '16');
        input.addEventListener('input', () => { const n = normalize(input.value); if (n !== input.value && /^0?5?\d*$/.test(n)) input.value = n; check(input); });
        input.addEventListener('blur', () => { input.value = normalize(input.value); check(input); });
        const form = input.form;
        if (form && !form.dataset.saBound) {
            form.dataset.saBound = 1;
            form.addEventListener('submit', e => {
                let ok = true;
                form.querySelectorAll('[data-sa-phone]').forEach(i => { i.value = normalize(i.value); if (!check(i)) ok = false; });
                if (!ok) { e.preventDefault(); e.stopImmediatePropagation(); form.querySelector('.sa-bad')?.focus(); }
            }, true);
        }
        if (input.value) check(input);
    }
    function init(root) { (root || document).querySelectorAll('[data-sa-phone]').forEach(bind); }
    document.addEventListener('DOMContentLoaded', () => init());
    return { normalize, isValid, check, init };
})();
</script>
@endonce
