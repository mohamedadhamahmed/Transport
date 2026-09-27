{{-- قوائم بحث (tom-select) لأي select عليه class="tv-select" --}}
@once
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.TomSelect) return;
        document.querySelectorAll('select.tv-select').forEach(function (el) {
            if (el.tomselect) return;
            new TomSelect(el, { allowEmptyOption: true, maxOptions: 500, dropdownParent: 'body' });
        });
    });
</script>
@endonce
