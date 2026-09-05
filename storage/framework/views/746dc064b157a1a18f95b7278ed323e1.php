
<?php
    $hasDateRange = $hasDateRange ?? false;
    $hasStatus = $hasStatus ?? false;
    $hasPostingSelect = $hasPostingSelect ?? false;
    $statusOptions = $statusOptions ?? [];
    $hasEntitySelect = $hasEntitySelect ?? false;
    $entityOptions = $entityOptions ?? [];
    $entityAjaxUrl = $entityAjaxUrl ?? null;
    $hasPrintDetails = $hasPrintDetails ?? false;
?>
<form method="GET" action="<?php echo e(url()->current()); ?>" class="dc-print-hide p-4 border-b border-gray-100 flex flex-wrap items-end gap-3">
    <?php if($hasEntitySelect): ?>
        <div class="w-full sm:w-56">
            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e($entityLabel ?? __('reports.filters')); ?></label>
            <?php if($entityAjaxUrl): ?>
                <select name="<?php echo e($entityParam); ?>" data-ajax-select data-ajax-url="<?php echo e($entityAjaxUrl); ?>"
                        data-ajax-placeholder="<?php echo e($entityAllLabel ?? __('reports.all')); ?>"
                        class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                    <option value=""><?php echo e($entityAllLabel ?? __('reports.all')); ?></option>
                    <?php if($entityId && isset($entityOptions[$entityId])): ?>
                        <option value="<?php echo e($entityId); ?>" selected><?php echo e($entityOptions[$entityId]); ?></option>
                    <?php endif; ?>
                </select>
            <?php else: ?>
                <select name="<?php echo e($entityParam); ?>" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                    <option value=""><?php echo e($entityAllLabel ?? __('reports.all')); ?></option>
                    <?php $__currentLoopData = $entityOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($id); ?>" <?php if((string) $entityId === (string) $id): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    
    <?php if(!$hasEntitySelect && isset($q)): ?>
        <div class="w-full sm:w-56">
            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('reports.filters')); ?></label>
            <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="<?php echo e($searchPlaceholder ?? __('reports.search_placeholder')); ?>"
                   class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
        </div>
    <?php elseif($hasEntitySelect && isset($q)): ?>
        <div class="w-full sm:w-48">
            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e($searchPlaceholder ?? __('reports.filters')); ?></label>
            <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="<?php echo e($searchPlaceholder ?? __('reports.search_placeholder')); ?>"
                   class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
        </div>
    <?php endif; ?>

    <?php if($hasPostingSelect): ?>
        <div class="w-full sm:w-64">
            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('reports.posting')); ?></label>
            <select name="posting_id" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                <?php $__empty_1 = true; $__currentLoopData = $postings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $posting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <option value="<?php echo e($posting->id); ?>" <?php if((int) $postingId === (int) $posting->id): echo 'selected'; endif; ?>>
                        <?php echo e($posting->document_number); ?> - <?php echo e(\Illuminate\Support\Carbon::parse($posting->month)->format('Y-m')); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <option value=""><?php echo e(__('reports.no_postings_found')); ?></option>
                <?php endif; ?>
            </select>
        </div>
    <?php endif; ?>

    <div class="w-full sm:w-48">
        <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('reports.branch')); ?></label>
        <select name="branch_id" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
            <option value=""><?php echo e(__('reports.all_branches')); ?></option>
            <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($branch->id); ?>" <?php if($branchId == $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    <?php if($hasDateRange): ?>
        <div class="w-full sm:w-40">
            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('reports.date_from')); ?></label>
            <input type="date" name="date_from" value="<?php echo e($dateFrom); ?>"
                   class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
        </div>
        <div class="w-full sm:w-40">
            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('reports.date_to')); ?></label>
            <input type="date" name="date_to" value="<?php echo e($dateTo); ?>"
                   class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
        </div>
    <?php endif; ?>

    <?php if($hasStatus): ?>
        <div class="w-full sm:w-40">
            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e($statusLabel ?? __('reports.loan_status')); ?></label>
            <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                <option value=""><?php echo e(__('reports.all_statuses')); ?></option>
                <?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>" <?php if($status === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    <?php endif; ?>

    <div class="flex items-center gap-2 flex-wrap">
        <button type="submit" class="px-4 py-2 rounded-lg dc-btn-primary text-sm font-medium transition">
            <?php echo e(__('reports.apply_filters')); ?>

        </button>
        <button type="button" onclick="window.print()" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm font-medium hover:bg-gray-200 transition">
            <?php echo e(__('reports.print')); ?>

        </button>
        <?php if($hasPrintDetails): ?>
            <button type="button" onclick="printWithDetails()" class="px-4 py-2 rounded-lg bg-indigo-50 text-indigo-700 text-sm font-medium hover:bg-indigo-100 transition">
                <?php echo e(__('reports.print_with_details')); ?>

            </button>
        <?php endif; ?>
        <button type="submit" name="export" value="excel" formtarget="_blank" class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium hover:bg-emerald-100 transition">
            <?php echo e(__('reports.export_excel')); ?>

        </button>
    </div>
</form>

<?php if($hasPrintDetails): ?>
    <script>
        function toggleRowDetails(id) {
            var el = document.getElementById('details-row-' + id);
            if (el) {
                el.classList.toggle('hidden');
            }
        }

        function printWithDetails() {
            document.querySelectorAll('.details-row').forEach(function (el) {
                el.classList.remove('hidden');
            });
            window.print();
        }

        window.addEventListener('afterprint', function () {
            document.querySelectorAll('.details-row').forEach(function (el) {
                el.classList.add('hidden');
            });
        });
    </script>
<?php endif; ?>

<?php if($hasEntitySelect && $entityAjaxUrl): ?>
    
    <?php echo $__env->make('partials.ajax-select-assets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/reports/_filters.blade.php ENDPATH**/ ?>