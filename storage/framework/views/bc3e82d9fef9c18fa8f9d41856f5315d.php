<div class="flex items-center justify-center gap-2 py-2 text-sm">
    <a href="<?php echo e(route('lang.switch', 'ar')); ?>"
       class="px-3 py-1 rounded <?php echo e(app()->getLocale() === 'ar' ? 'font-bold text-indigo-600 underline' : 'text-gray-500 hover:text-indigo-500'); ?>">
        العربية
    </a>
    <span class="text-gray-300">|</span>
    <a href="<?php echo e(route('lang.switch', 'en')); ?>"
       class="px-3 py-1 rounded <?php echo e(app()->getLocale() === 'en' ? 'font-bold text-indigo-600 underline' : 'text-gray-500 hover:text-indigo-500'); ?>">
        English
    </a>
</div>
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/partials/language-switcher.blade.php ENDPATH**/ ?>