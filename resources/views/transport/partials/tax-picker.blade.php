{{-- اختيار الضريبة:
     - داخل المملكة 15%
     - شحنة خارج المملكة معفاة 0%
     - نسبة أخرى: بتتجاب من الإعدادات (شاشة الضرائب) - المفعّلة بترتيب الأولوية، والأولى هي اللي بتظهر أول واحدة
     @include('transport.partials.tax-picker', ['inputId' => 'tax_rate', 'taxType' => ..., 'taxPercent' => ...]) --}}
@php
    $taxType = $taxType ?: 'standard';
    $settingsTaxes = \App\Models\Tax::where('is_active', true)->orderBy('priority')->get(['id', 'name', 'rate']);
    $selectedTaxId = old('tax_id');
    if (!$selectedTaxId && $taxType === 'custom') {
        $selectedTaxId = optional($settingsTaxes->first(fn ($t) => abs((float) $t->rate - (float) $taxPercent) < 0.001))->id;
    }
    $selectedTaxId = $selectedTaxId ?: optional($settingsTaxes->first())->id;
@endphp
<div class="sum-row" style="align-items:center;gap:.5rem;flex-wrap:wrap">
    <span>{{ __('transport.tax_type') }}</span>
    <div style="display:flex;gap:.4rem;align-items:center;flex-wrap:wrap">
        <select name="tax_type" id="{{ $inputId }}_type" class="tr-input" style="max-width:230px">
            <option value="standard" @selected($taxType === 'standard')>{{ __('transport.tax_standard') }}</option>
            <option value="international" @selected($taxType === 'international')>{{ __('transport.tax_international') }}</option>
            @if ($settingsTaxes->isNotEmpty())
                <option value="custom" @selected($taxType === 'custom')>{{ __('transport.tax_custom') }}</option>
            @endif
        </select>
        @if ($settingsTaxes->isNotEmpty())
            <select name="tax_id" id="{{ $inputId }}_tax" class="tr-input" style="max-width:200px">
                @foreach ($settingsTaxes as $t)
                    <option value="{{ $t->id }}" data-rate="{{ (float) $t->rate }}" @selected((string) $selectedTaxId === (string) $t->id)>{{ $t->name }} - {{ rtrim(rtrim(number_format((float) $t->rate, 2), '0'), '.') }}%</option>
                @endforeach
            </select>
        @endif
        <input type="number" step="0.01" min="0" max="100" name="tax_rate" id="{{ $inputId }}" value="{{ $taxPercent }}" class="tr-input" style="max-width:80px;background:#f9fafb" readonly title="%">
        <span>%</span>
    </div>
</div>
<script>
(function () {
    const sel = document.getElementById(@json($inputId . '_type'));
    const taxSel = document.getElementById(@json($inputId . '_tax'));
    const inp = document.getElementById(@json($inputId));
    function apply() {
        if (sel.value === 'international') inp.value = 0;
        else if (sel.value === 'custom' && taxSel) inp.value = taxSel.selectedOptions[0]?.dataset.rate ?? 0;
        else inp.value = 15;
        if (taxSel) taxSel.style.display = sel.value === 'custom' ? '' : 'none';
        inp.dispatchEvent(new Event('input', { bubbles: true }));
    }
    sel.addEventListener('change', apply);
    if (taxSel) taxSel.addEventListener('change', apply);
    apply();
})();
</script>
