<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name')); ?></title>

    <!-- أيقونة التاب (Favicon) -->
    <link rel="icon" href="<?php echo e(asset('favicon.ico')); ?>" sizes="any">
    <link rel="icon" type="image/png" href="<?php echo e(asset('favicon.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('favicon.png')); ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <!-- الاستايل الخاص بيك - ملف عادي، أي تعديل فيه يظهر فورًا من غير npm -->
    <link rel="stylesheet" href="<?php echo e(asset('css/custom.css')); ?>?v=<?php echo e(filemtime(public_path('css/custom.css'))); ?>">
</head>
<?php /**PATH C:\xampp\htdocs\factory\resources\views/layouts/head.blade.php ENDPATH**/ ?>