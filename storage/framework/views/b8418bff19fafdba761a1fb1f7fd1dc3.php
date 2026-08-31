
<?php
    $brand = [
        'name_ar' => defined('Namear') ? Namear : config('app.name', 'ERP'),
        'desc_ar' => defined('describtionar') ? describtionar : null,
        'st_ar' => defined('STar') ? STar : null,
        'tax_ar' => defined('Taxar') ? Taxar : null,
        'name_en' => defined('Nameen') ? Nameen : null,
        'desc_en' => defined('describtionen') ? describtionen : null,
        'st_en' => defined('STen') ? STen : null,
        'tax_en' => defined('Taxen') ? Taxen : null,
        'logo' => defined('camplogo') ? camplogo : null,
    ];
?>

<div class="doc-header" dir="rtl">
    <div class="company-block">
        <div class="name"><?php echo e($brand['name_ar']); ?></div>
        <?php if($brand['desc_ar']): ?>
            <p><?php echo e($brand['desc_ar']); ?></p>
        <?php endif; ?>
        <?php if($brand['st_ar']): ?>
            <p><?php echo e($brand['st_ar']); ?></p>
        <?php endif; ?>
        <?php if($brand['tax_ar']): ?>
            <p><?php echo e($brand['tax_ar']); ?></p>
        <?php endif; ?>
    </div>

    <?php if($brand['logo']): ?>
        <div class="logo-block">
            <img class="logo" src="<?php echo e(asset('assets/img/brand/' . $brand['logo'])); ?>" alt="logo">
        </div>
    <?php endif; ?>

    <?php if($brand['name_en']): ?>
        <div class="company-block">
            <div class="name"><?php echo e($brand['name_en']); ?></div>
            <?php if($brand['desc_en']): ?>
                <p><?php echo e($brand['desc_en']); ?></p>
            <?php endif; ?>
            <?php if($brand['st_en']): ?>
                <p><?php echo e($brand['st_en']); ?></p>
            <?php endif; ?>
            <?php if($brand['tax_en']): ?>
                <p><?php echo e($brand['tax_en']); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/partials/print-header.blade.php ENDPATH**/ ?>