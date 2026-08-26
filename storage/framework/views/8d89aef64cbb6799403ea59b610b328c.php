
<aside
    :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
    class="fixed lg:static inset-y-0 right-0 z-40 w-64 shrink-0 bg-[#0F1B4C] text-white flex flex-col transition-transform duration-200 ease-in-out">
    <!-- الشعار -->
    <div class="flex items-center justify-center gap-2 py-5 border-b border-white/10">
        <img src="<?php echo e(asset('images/sidebar-icon.png')); ?>" alt="<?php echo e(config('app.name', 'دفتركوم')); ?>" class="h-9 w-9 object-contain">
        <span class="text-white font-bold text-lg">دفتركم</span>
    </div>

    <!-- بطاقة المستخدم -->
    <div class="px-4 py-4 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="relative shrink-0">
                <div class="w-11 h-11 rounded-full bg-gradient-to-br from-[#1456E8] to-[#6B2FD6] flex items-center justify-center text-base font-bold ring-2 ring-white/10">
                    <?php echo e(strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1))); ?>

                </div>
                <span class="absolute -bottom-0.5 -end-0.5 w-3 h-3 rounded-full bg-emerald-400 ring-2 ring-[#0F1B4C]"></span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold truncate"><?php echo e(auth()->user()->name ?? ''); ?></p>
                <p class="text-xs text-white/50 truncate"><?php echo e(auth()->user()->email ?? ''); ?></p>
            </div>
        </div>

        <!-- زرار تبديل اللغة (Pill Toggle) -->
        <div class="mt-4 grid grid-cols-2 gap-1 bg-white/5 rounded-full p-1">
            <a href="<?php echo e(route('lang.switch', 'ar')); ?>"
                class="text-center text-xs font-medium py-1.5 rounded-full transition
                      <?php echo e(app()->getLocale() === 'ar' ? 'bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white shadow' : 'text-white/60 hover:text-white'); ?>">
                العربية
            </a>
            <a href="<?php echo e(route('lang.switch', 'en')); ?>"
                class="text-center text-xs font-medium py-1.5 rounded-full transition
                      <?php echo e(app()->getLocale() === 'en' ? 'bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white shadow' : 'text-white/60 hover:text-white'); ?>">
                English
            </a>
        </div>
    </div>

    <!-- عناصر القائمة -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-5">

        <!-- الرئيسية -->
        <div>
            <a href="<?php echo e(route('dashboard')); ?>"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                      <?php echo e(request()->routeIs('dashboard')
                            ? 'bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white shadow-md shadow-black/20'
                            : 'text-gray-300 hover:bg-white/5 hover:text-white'); ?>">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 10.5 12 3l9 7.5" />
                    <path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5" />
                </svg>
                <span class="text-sm font-medium"><?php echo e(__('messages.dashboard')); ?></span>
            </a>
        </div>

        <?php
        // كل عنصر فيه 'url' حقيقي بيروح لصفحته الفعلية، وأي عنصر لسه
        // 'url' => '#' يبقى Placeholder لحد ما نبني صفحته.
        $sections = [
        [
        'label' => __('messages.sales'),
        'groups' => [
        [
        'key' => 'invoices',
        'label' => __('messages.sales'),
        'icon' => 'bag',
        'items' => [
        ['label' => __('invoices.title'), 'url' => route('invoices.index')],
        ['label' => __('invoices.new_invoice'), 'url' => route('invoices.create')],
        ['label' => __('invoices.previous_drafts'), 'url' => route('invoices.drafts.index')],
        ['label' => __('invoices.sales_return'), 'url' => route('invoices.returns.create')],
        ['label' => __('invoices.previous_returns'), 'url' => route('invoices.returns.index')],


        ],
        ],
        [
        'key' => 'quotations',
        'label' => __('quotations.title'),
        'icon' => 'tag',
        'items' => [
        ['label' => __('quotations.title'), 'url' => route('quotations.index')],
        ['label' => __('quotations.new_quotation'), 'url' => route('quotations.create')],
        ],
        ],
        [
        'key' => 'zatca',
        'label' => __('zatca.title'),
        'icon' => 'doc',
        'items' => [
        ['label' => __('zatca.not_sent'), 'url' => route('zatca.index', ['sent' => 0])],
        ['label' => __('zatca.sent'), 'url' => route('zatca.index', ['sent' => 1])],
        ],
        ],
        ],
        ],
        [
        'label' => __('settings.title'),
        'groups' => [
        [
        'key' => 'settings',
        'label' => __('settings.title'),
        'icon' => 'gear',
        'items' => [
        ['label' => __('settings.title'), 'url' => route('settings.index')],
        ['label' => __('settings.employee_discounts_title'), 'url' => route('employee-discounts.index')],
        ],
        ],
        ],
        ],
        ];

        $icons = [
        'bag' => '
        <path d="M6 8h12l-1 12H7L6 8Z" />
        <path d="M9 8V6a3 3 0 0 1 6 0v2" />',
        'cart' => '
        <circle cx="9" cy="20" r="1" />
        <circle cx="17" cy="20" r="1" />
        <path d="M3 4h2l2.4 12.4a1 1 0 0 0 1 .8h8.4a1 1 0 0 0 1-.8L20 8H6" />',
        'doc' => '
        <rect x="6" y="3" width="12" height="18" rx="1" />
        <path d="M9 8h6M9 12h6M9 16h4" />',
        'box' => '
        <path d="M3 8l9-5 9 5-9 5-9-5Z" />
        <path d="M3 8v8l9 5 9-5V8" />
        <path d="M12 13v8" />',
        'store' => '
        <path d="M4 21V10l8-6 8 6v11" />
        <path d="M9 21v-6h6v6" />',
        'gear' => '
        <circle cx="12" cy="12" r="3" />
        <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z" />',
        'return' => '
        <path d="M3 7v6h6" />
        <path d="M3 13a9 9 0 1 0 3-6.7L3 9" />',
        'tag' => '
        <path d="M20.6 12.6 12.6 20.6a2 2 0 0 1-2.83 0l-6.37-6.37a2 2 0 0 1 0-2.83L11.4 3.4A2 2 0 0 1 12.8 2.8H19a2 2 0 0 1 2 2v6.2a2 2 0 0 1-.4 1.2Z" />
        <circle cx="16.5" cy="7.5" r="1.5" />',
        ];
        ?>

        <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div>
            <p class="px-3 mb-1 text-[11px] font-semibold uppercase tracking-wider text-white/35">
                <?php echo e($section['label']); ?>

            </p>
            <div class="space-y-1">
                <?php $__currentLoopData = $section['groups']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div x-data="{ open: <?php echo e(request()->is($group['key'].'*') ? 'true' : 'false'); ?> }">
                    <button type="button" @click="open = !open"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-gray-300 hover:bg-white/5 hover:text-white transition">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <?php echo $icons[$group['icon']]; ?>

                            </svg>
                            <span class="text-sm font-medium"><?php echo e($group['label']); ?></span>
                        </span>
                        <svg :class="open ? '-rotate-180' : ''" class="w-4 h-4 text-white/40 transition-transform duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="mt-1 me-4 pe-3 border-e-2 border-white/10 space-y-1">
                        <?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e($item['url']); ?>"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-white/50 hover:bg-white/5 hover:text-white transition">
                            <span class="w-1 h-1 rounded-full bg-[#F5811E]"></span>
                            <?php echo e($item['label']); ?>

                        </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>

    <!-- تسجيل الخروج -->
    <div class="border-t border-white/10 p-3">
        <a href="<?php echo e(route('profile.edit')); ?>"
            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-white/5 hover:text-white transition">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="3.5" />
                <path d="M5 20c0-3.5 3-6 7-6s7 2.5 7 6" />
            </svg>
            <?php echo e(__('Profile')); ?>

        </a>
        <form method="POST" action="<?php echo e(route('logout')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-white/5 hover:text-white transition">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <path d="M16 17l5-5-5-5" />
                    <path d="M21 12H9" />
                </svg>
                <?php echo e(__('Log Out')); ?>

            </button>
        </form>
    </div>
</aside>

<!-- طبقة تظليل لإغلاق القائمة على الموبايل -->
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
    class="fixed inset-0 bg-black/40 z-30 lg:hidden"></div><?php /**PATH C:\xampp\htdocs\my-erp\resources\views/layouts/main-sidebar.blade.php ENDPATH**/ ?>