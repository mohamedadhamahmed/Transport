<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
<?php echo $__env->make('layouts.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<!-- يمكنك وضع الـ CSS هنا مباشرة أو داخل ملف layouts.head -->
<link href="<?php echo e(asset('assets/libs/tom-select/css/tom-select.css')); ?>" rel="stylesheet">

<body class="font-sans antialiased bg-gray-100" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">

        <?php echo $__env->make('layouts.main-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="flex-1 flex flex-col min-w-0">

            <?php echo $__env->make('layouts.main-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <!-- عنوان الصفحة -->
            <?php if(isset($header)): ?>
                <div class="bg-white border-b px-4 sm:px-6 lg:px-8 py-4">
                    <?php echo e($header); ?>

                </div>
            <?php endif; ?>

            <!-- محتوى الصفحة -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <?php echo e($slot); ?>

            </main>

            <?php echo $__env->make('layouts.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>

    <!-- جميع السكربتات يجب أن تكون هنا داخل الـ body -->
    <?php echo $__env->make('layouts.footer-scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    
    <script src="<?php echo e(asset('assets/libs/tom-select/js/tom-select.complete.min.js')); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    
    <?php echo $__env->make('partials.assistant-widget', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html><?php /**PATH C:\xampp\htdocs\factory\resources\views/layouts/app.blade.php ENDPATH**/ ?>