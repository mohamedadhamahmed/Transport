<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['date']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['date']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $color = 'bg-gray-100 text-gray-400';
    $label = '-';

    if ($date) {
        $label = $date->format('Y-m-d');
        $daysLeft = now()->startOfDay()->diffInDays($date, false);

        if ($daysLeft <= 15) {
            $color = 'bg-red-100 text-red-700';
        } elseif ($daysLeft <= 45) {
            $color = 'bg-amber-100 text-amber-700';
        } else {
            $color = 'bg-emerald-100 text-emerald-700';
        }
    }
?>

<span class="inline-block px-2.5 py-1 rounded-md text-xs font-semibold <?php echo e($color); ?>"><?php echo e($label); ?></span><?php /**PATH C:\xampp\htdocs\factory\resources\views/components/date-badge.blade.php ENDPATH**/ ?>