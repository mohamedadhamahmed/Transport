<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg"><?php echo e(__('accounts.tree_title')); ?></h2>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?php echo e(route('accounts.index')); ?>"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                        <?php echo e(__('accounts.tree_view_as_list')); ?>

                    </a>
                    <a href="<?php echo e(route('accounts.create')); ?>"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        + <?php echo e(__('accounts.new_account')); ?>

                    </a>
                </div>
            </div>

            <?php echo $__env->make('partials.sweet-alert-flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl">

                <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <input type="text" id="tree-search" autocomplete="off" placeholder="<?php echo e(__('accounts.search_placeholder')); ?>"
                           class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8] text-sm">
                    <div class="flex items-center gap-2">
                        <button type="button" id="expand-all-btn" class="px-3 py-2 rounded-lg bg-gray-100 text-gray-600 text-xs font-medium hover:bg-gray-200 transition whitespace-nowrap">
                            <?php echo e(__('accounts.expand_all')); ?>

                        </button>
                        <button type="button" id="collapse-all-btn" class="px-3 py-2 rounded-lg bg-gray-100 text-gray-600 text-xs font-medium hover:bg-gray-200 transition whitespace-nowrap">
                            <?php echo e(__('accounts.collapse_all')); ?>

                        </button>
                    </div>
                </div>

                <div class="px-2 py-2 border-b border-gray-100 hidden sm:flex items-center gap-2 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">
                    <span class="w-5 shrink-0"></span>
                    <span class="w-16 shrink-0"><?php echo e(__('accounts.account_number')); ?></span>
                    <span class="flex-1"><?php echo e(__('accounts.name')); ?></span>
                    <span class="w-24 text-end shrink-0"><?php echo e(__('accounts.debtor')); ?></span>
                    <span class="w-24 text-end shrink-0"><?php echo e(__('accounts.creditor')); ?></span>
                    <span class="w-24 text-end shrink-0"><?php echo e(__('accounts.current_balance')); ?></span>
                    <span class="w-[68px] shrink-0"></span>
                </div>

                <div id="tree-root" class="p-2">
                    <?php $__empty_1 = true; $__currentLoopData = $roots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $node): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php echo $__env->make('accounts.tree-node', ['node' => $node, 'depth' => 0], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="px-4 py-10 text-center text-gray-400">
                            <?php echo e(__('accounts.no_accounts_found')); ?>

                        </div>
                    <?php endif; ?>
                </div>

                <div id="tree-empty-search" class="px-4 py-10 text-center text-gray-400 hidden">
                    <?php echo e(__('accounts.no_accounts_found')); ?>

                </div>
            </div>
        </div>
    </div>

    <style>
        .toggle-icon { transition: transform .15s ease; transform: rotate(90deg); }
        .toggle-btn[aria-expanded="false"] .toggle-icon { transform: rotate(0deg); }
    </style>

    <script>
    (function () {
        const treeRoot = document.getElementById('tree-root');
        const searchInput = document.getElementById('tree-search');
        const emptySearchBox = document.getElementById('tree-empty-search');

        function setExpanded(node, expanded) {
            const btn = node.querySelector(':scope > .tree-row > .toggle-btn');
            const childrenWrap = node.querySelector(':scope > .children-wrap');
            if (btn) btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            if (childrenWrap) childrenWrap.classList.toggle('hidden', !expanded);
        }

        treeRoot.addEventListener('click', (e) => {
            const btn = e.target.closest('.toggle-btn');
            if (!btn) return;
            const node = btn.closest('.tree-node');
            const expanded = btn.getAttribute('aria-expanded') === 'true';
            setExpanded(node, !expanded);
        });

        document.getElementById('expand-all-btn').addEventListener('click', () => {
            treeRoot.querySelectorAll('.tree-node').forEach((node) => setExpanded(node, true));
        });
        document.getElementById('collapse-all-btn').addEventListener('click', () => {
            treeRoot.querySelectorAll('.tree-node').forEach((node) => setExpanded(node, false));
        });

        // فلترة الشجرة بالبحث: أي حساب اسمه أو رقمه يطابق النص بيفضل
        // ظاهر هو وكل أجداده (اتوسّعوا تلقائيًا)، والباقي بيتخفي.
        function filterNode(node, query) {
            const ownMatch = node.dataset.name.includes(query) || node.dataset.number.includes(query);
            const childrenWrap = node.querySelector(':scope > .children-wrap');
            let childMatch = false;

            if (childrenWrap) {
                childrenWrap.querySelectorAll(':scope > .tree-node').forEach((child) => {
                    if (filterNode(child, query)) childMatch = true;
                });
            }

            const visible = query === '' || ownMatch || childMatch;
            node.classList.toggle('hidden', !visible);

            if (query !== '' && childMatch) {
                setExpanded(node, true);
            }

            return visible;
        }

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLowerCase();
            let anyVisible = false;

            treeRoot.querySelectorAll(':scope > .tree-node').forEach((node) => {
                if (filterNode(node, query)) anyVisible = true;
            });

            emptySearchBox.classList.toggle('hidden', anyVisible || query === '');
        });
    })();
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/accounts/tree.blade.php ENDPATH**/ ?>