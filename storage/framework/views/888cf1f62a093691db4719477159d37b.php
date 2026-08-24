
<?php if(session('success') || session('error') || $errors->any()): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php if(session('success')): ?>
                Swal.fire({
                    icon: 'success',
                    title: <?php echo json_encode(session('success'), 15, 512) ?>,
                    confirmButtonColor: '#0F1B4C',
                    confirmButtonText: '<?php echo e(app()->getLocale() === 'ar' ? 'تم' : 'OK'); ?>',
                });
            <?php endif; ?>

            <?php if(session('error')): ?>
                Swal.fire({
                    icon: 'error',
                    title: <?php echo json_encode(session('error'), 15, 512) ?>,
                    confirmButtonColor: '#0F1B4C',
                    confirmButtonText: '<?php echo e(app()->getLocale() === 'ar' ? 'حسنًا' : 'OK'); ?>',
                });
            <?php endif; ?>

            <?php if($errors->any()): ?>
                Swal.fire({
                    icon: 'error',
                    title: <?php echo json_encode($errors->first(), 15, 512) ?>,
                    confirmButtonColor: '#0F1B4C',
                    confirmButtonText: '<?php echo e(app()->getLocale() === 'ar' ? 'حسنًا' : 'OK'); ?>',
                });
            <?php endif; ?>
        });
    </script>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/partials/sweet-alert-flash.blade.php ENDPATH**/ ?>