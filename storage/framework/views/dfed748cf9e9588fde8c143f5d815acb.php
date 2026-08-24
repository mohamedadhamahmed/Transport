<?php
    // نفس التصنيفات المستخدمة في السايدبار - بس هنا شكل أزرار سريعة (Pills)
    // بدّلي href="#" بالراوت الحقيقي أول ما تعملي الصفحة.
    $quickLinks = [
        ['label' => __('messages.sales'), 'icon' => 'bag'],
        ['label' => __('messages.purchases'), 'icon' => 'cart'],
        ['label' => __('messages.accounting_invoices'), 'icon' => 'doc'],
        ['label' => __('messages.inventory'), 'icon' => 'box'],
    ];
    $icons = [
        'bag' => '<path d="M6 8h12l-1 12H7L6 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
        'cart' => '<circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/><path d="M3 4h2l2.4 12.4a1 1 0 0 0 1 .8h8.4a1 1 0 0 0 1-.8L20 8H6"/>',
        'doc' => '<rect x="6" y="3" width="12" height="18" rx="1"/><path d="M9 8h6M9 12h6M9 16h4"/>',
        'box' => '<path d="M3 8l9-5 9 5-9 5-9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/>',
    ];
?>
<header class="bg-white shadow-sm sticky top-0 z-20">
    <div class="flex items-center gap-3 px-4 sm:px-6 py-3">
        <!-- زرار فتح القائمة على الموبايل -->
        <button type="button" @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-600 hover:text-gray-900 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- أزرار وصول سريع -->
        <div class="hidden md:flex items-center gap-2 overflow-x-auto">
            <?php $__currentLoopData = $quickLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="#"
                   class="flex items-center gap-2 shrink-0 rounded-full border border-gray-200 bg-white px-4 py-1.5 text-sm font-medium text-gray-600 shadow-sm hover:shadow hover:text-[#1456E8] hover:border-[#1456E8]/30 transition">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <?php echo $icons[$link['icon']]; ?>

                    </svg>
                    <?php echo e($link['label']); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="flex-1"></div>
    </div>
</header>
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/layouts/main-header.blade.php ENDPATH**/ ?>