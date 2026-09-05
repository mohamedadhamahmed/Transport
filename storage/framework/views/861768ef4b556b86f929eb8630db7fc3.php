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
    <?php
        // صلاحية عرض الربح: صاحب الشركة ممكن يمنع بعض الموظفين من شوفان
        // عمود/بطاقة الربح في شاشة تعديل سند التسليم (نفس الصلاحية
        // مستخدمة في الفواتير وعروض الأسعار وشاشة الإنشاء كمان).
        $canViewProfit = auth()->user()?->hasPermission('sensitive_data.view_profit');
    ?>
    
    <style>
        .ts-wrapper {
            position: relative;
            width: 100%;
        }
        .ts-wrapper.single .ts-control {
            display: flex;
            align-items: center;
            width: 100%;
            box-sizing: border-box;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #fff;
            min-height: 42px;
            padding-inline-start: 0.75rem;
            padding-inline-end: 1.75rem;
            padding-block: 0.5rem;
            font-size: 0.875rem;
            color: rgb(17 24 39);
            cursor: pointer;
            overflow: hidden;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .ts-wrapper.single.focus .ts-control,
        .ts-wrapper.single.dropdown-active .ts-control {
            border-color: #1456E8;
            box-shadow: 0 0 0 1px #1456E8;
        }
        .ts-wrapper.single .ts-control::after {
            content: "";
            position: absolute;
            inset-inline-end: 0.85rem;
            top: 50%;
            width: 0.4rem;
            height: 0.4rem;
            border-inline-end: 1.5px solid rgb(156 163 175);
            border-block-end: 1.5px solid rgb(156 163 175);
            transform: translateY(-70%) rotate(45deg);
            pointer-events: none;
        }
        .ts-control input {
            color: inherit;
            font-size: inherit;
            background: transparent;
            min-width: 2rem;
            cursor: pointer;
        }
        .ts-control input::placeholder { color: rgb(156 163 175); }
        .ts-wrapper.single .ts-control > .item {
            color: rgb(17 24 39);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .ts-dropdown {
            z-index: 9999;
            margin-top: 0.25rem;
            background-color: #fff;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            box-shadow: 0 10px 25px -5px rgba(15, 27, 76, 0.18), 0 8px 10px -6px rgba(15, 27, 76, 0.12);
            overflow: hidden;
            font-size: 0.875rem;
            text-align: start;
        }
        .ts-dropdown .ts-dropdown-content { max-height: 15rem; overflow-y: auto; }
        .ts-dropdown .option { white-space: normal; word-break: break-word; padding: 0.55rem 0.75rem; cursor: pointer; }
        .ts-dropdown .option.active,
        .ts-dropdown .option:hover { background-color: #1456E8; color: #fff; }
        .ts-hidden-accessible {
            border: 0 !important;
            clip: rect(0 0 0 0) !important;
            clip-path: inset(50%) !important;
            height: 1px !important;
            overflow: hidden !important;
            padding: 0 !important;
            position: absolute !important;
            width: 1px !important;
            white-space: nowrap !important;
        }
    </style>

    <div class="py-6" x-data="deliveryForm()">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-7 py-6 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <span class="absolute -top-12 -end-12 w-40 h-40 rounded-full bg-white/5 pointer-events-none"></span>
                <span class="absolute -bottom-16 -start-16 w-52 h-52 rounded-full bg-[#F5811E]/10 pointer-events-none"></span>

                <div class="relative flex items-center gap-4">
                    <span class="w-12 h-12 shrink-0 rounded-xl bg-[#F5811E]/15 ring-1 ring-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 8l9-5 9 5-9 5-9-5Z" />
                            <path d="M3 8v8l9 5 9-5V8" />
                            <path d="M12 13v8" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-[11px] font-bold text-white/40 uppercase tracking-wide"><?php echo e(__('deliverynote.title')); ?></p>
                        <h2 class="text-white font-bold text-xl leading-tight mt-0.5"><?php echo e(__('deliverynote.edit_delivery_note')); ?> #<?php echo e($invoice->id); ?></h2>
                        <p class="text-white/60 text-xs mt-1"><?php echo e(__('deliverynote.delivery_product_subtitle')); ?></p>
                    </div>
                </div>
                <div class="relative flex items-center gap-2">
                    <button type="button" id="open-tax-calculator-btn"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium text-white bg-white/10 hover:bg-white/20 border border-white/10 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="2" width="16" height="20" rx="2" />
                            <path d="M8 6h8M8 10h.01M12 10h.01M16 10h.01M8 14h.01M12 14h.01M16 14h.01" />
                        </svg>
                        حاسبة الضريبة والخصم
                    </button>
                    <a href="<?php echo e(route('deliverynote.show', $invoice->id)); ?>"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-[#0F1B4C] bg-white hover:bg-white/95 transition whitespace-nowrap shadow-sm">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5V6a2 2 0 0 1 2-2h9l5 5v10.5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z" />
                            <path d="M14 4v4a1 1 0 0 0 1 1h4" />
                        </svg>
                        <?php echo e(__('deliverynote.back')); ?>

                    </a>
                </div>
            </div>

            <form id="delivery-form" method="POST" action="<?php echo e(route('deliverynote.update', $invoice->id)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" name="items_json" id="items_json">

                
                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.chooseclient')); ?> *</label>
                            <div class="flex gap-2">
                                <select name="customer_id" id="customer_select" x-model="selectedCustomerId"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                                    <option value="1"><?php echo e(__('deliverynote.cash_customer')); ?></option>
                                    <?php $__currentLoopData = $Customer; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($id); ?>" selected><?php echo e($name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <button type="button" @click="customerModalOpen = true"
                                        class="px-3 rounded-lg bg-[#0F1B4C]/5 text-[#0F1B4C] hover:bg-[#0F1B4C]/10 transition text-sm whitespace-nowrap font-medium">
                                    + <?php echo e(__('deliverynote.add_new_customer')); ?>

                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.notesClient')); ?></label>
                            <input type="text" name="note" x-model="note"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">P.O#</label>
                            <input type="text" name="po_number" x-model="poNumber"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.tax')); ?></label>
                            <select x-model.number="taxRate"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="0"><?php echo e(__('deliverynote.tax')); ?> 0%</option>
                                <?php $__currentLoopData = $taxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($tax->rate / 100); ?>" <?php if(abs($tax->rate / 100 - $defaultTaxRate) < 0.0001): echo 'selected'; endif; ?>>(<?php echo e($tax->rate); ?>%) <?php echo e($tax->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <p class="text-[11px] text-gray-400 mt-1"><?php echo e(__('deliverynote.tax_estimate_note')); ?></p>
                        </div>
                    </div>
                </div>

                
                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6 mt-6">
                    <div class="flex items-start gap-3 mb-4 flex-wrap">
                        <div class="relative">
                            <input type="text" x-model="searchQuery" @input.debounce.300ms="searchProducts()"
                                   placeholder="<?php echo e(__('deliverynote.search_product_placeholder')); ?>"
                                   class="w-full max-w-md rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <div x-show="searchResults.length > 0" x-cloak
                                 class="absolute z-10 mt-1 w-full max-w-md bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-y-auto">
                                <template x-for="p in searchResults" :key="p.id">
                                    <button type="button" @click="addProduct(p)"
                                            class="w-full text-start px-4 py-2 hover:bg-[#1456E8]/5 flex items-center justify-between border-b border-gray-50 last:border-0">
                                        <span>
                                            <span class="font-medium text-gray-800" x-text="p.name"></span>
                                            <span class="text-xs text-gray-400" x-text="p.code ? ' (' + p.code + ')' : ''"></span>
                                        </span>
                                        <span class="text-sm text-gray-500" x-text="p.sale_price"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                        <button type="button" @click="openProductPicker()"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white text-sm font-medium bg-[#0F1B4C] hover:bg-[#0F1B4C]/90 transition whitespace-nowrap">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18M8 4v16"/>
                            </svg>
                            <?php echo e(__('deliverynote.choose_product')); ?>

                        </button>
                        <button type="button" @click="productModalOpen = true"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white text-sm font-medium bg-[#F5811E] hover:brightness-95 transition whitespace-nowrap">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 5v14M5 12h14"/>
                            </svg>
                            <?php echo e(__('deliverynote.new_product')); ?>

                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-[#0F1B4C] text-white/80">
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('deliverynote.product_number')); ?></th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('deliverynote.product_name')); ?></th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('deliverynote.quantity')); ?></th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('deliverynote.unit_price')); ?></th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('deliverynote.discount')); ?></th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('deliverynote.total')); ?></th>
                                    <?php if($canViewProfit): ?>
                                        <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('deliverynote.profit_per_unit')); ?></th>
                                    <?php endif; ?>
                                    <th class="px-3 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="hover:bg-[#1456E8]/5 transition">
                                        <td class="px-3 py-2 text-gray-400 text-xs" x-text="item.code || '-'"></td>
                                        <td class="px-3 py-2 font-medium text-gray-800 min-w-[300px] whitespace-normal" x-text="item.name"></td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0.01" x-model.number="item.quantity"
                                                   class="w-20 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" x-model.number="item.unit_price"
                                                   class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" x-model.number="item.discount"
                                                   class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                        </td>
                                        <td class="px-3 py-2 font-semibold text-[#0F1B4C]" x-text="lineTotal(item).toFixed(2)"></td>
                                        <?php if($canViewProfit): ?>
                                            <td class="px-3 py-2" :class="lineProfit(item) < 0 ? 'text-red-600' : 'text-emerald-600'" x-text="lineProfit(item).toFixed(2)"></td>
                                        <?php endif; ?>
                                        <td class="px-3 py-2">
                                            <button type="button" @click="removeItem(index)"
                                                    class="inline-flex items-center gap-1 text-red-600 hover:text-red-700 text-xs font-medium">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M6 7l1 13a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-13"/>
                                                </svg>
                                                <?php echo e(__('deliverynote.remove')); ?>

                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="items.length === 0">
                                    <td colspan="<?php echo e($canViewProfit ? 8 : 7); ?>" class="px-3 py-8 text-center text-gray-400">
                                        <?php echo e(__('deliverynote.no_items_yet')); ?>

                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.discount_on_invoice')); ?></label>
                            <input type="number" step="0.01" min="0" name="discountOnInvoice" x-model.number="discountOnInvoice"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>

                    
                    <div class="grid grid-cols-2 <?php echo e($canViewProfit ? 'md:grid-cols-5' : 'md:grid-cols-4'); ?> gap-4 mt-6">
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1"><?php echo e(__('deliverynote.total')); ?></div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="subtotal.toFixed(2)"></div>
                        </div>
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1"><?php echo e(__('deliverynote.discount_on_invoice')); ?></div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="(parseFloat(discountOnInvoice) || 0).toFixed(2)"></div>
                        </div>
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1"><?php echo e(__('deliverynote.tax_total')); ?></div>
                            <div class="font-semibold text-[#0F1B4C]" x-text="taxAmount.toFixed(2)"></div>
                        </div>
                        <?php if($canViewProfit): ?>
                            <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4 text-center">
                                <div class="text-xs text-gray-500 mb-1"><?php echo e(__('deliverynote.total_profit')); ?></div>
                                <div class="font-semibold text-emerald-700" x-text="totalProfit.toFixed(2)"></div>
                            </div>
                        <?php endif; ?>
                        <div class="rounded-lg p-4 text-center text-white bg-[#0F1B4C] relative overflow-hidden">
                            <span class="absolute inset-x-0 bottom-0 h-0.5 bg-[#F5811E]"></span>
                            <div class="text-xs text-white/50 mb-1"><?php echo e(__('deliverynote.pending_value')); ?></div>
                            <div class="font-bold text-lg" x-text="grandTotal.toFixed(2)"></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-6">
                        <button type="button" @click="submitDelivery()" :disabled="isSubmitting"
                                class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting"><?php echo e(__('deliverynote.update_delivery_note')); ?></span>
                            <span x-show="isSubmitting" x-cloak><?php echo e(__('deliverynote.saving_please_wait')); ?></span>
                        </button>
                        <a href="<?php echo e(route('deliverynote.show', $invoice->id)); ?>"
                           class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                            <?php echo e(__('deliverynote.cancel')); ?>

                        </a>
                    </div>
                </div>
            </form>
        </div>

        <?php echo $__env->make('partials.tax-calculator-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        
        <div x-show="customerModalOpen" x-cloak
             class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto">
            <div class="bg-white rounded-xl p-6 w-full max-w-2xl my-8" @click.outside="customerModalOpen = false">
                <h3 class="font-semibold text-lg text-gray-800 mb-4"><?php echo e(__('deliverynote.add_new_customer')); ?></h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.customer_name')); ?> *</label>
                        <input type="text" x-model="newCustomer.name"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.phone')); ?> *</label>
                        <input type="text" x-model="newCustomer.phone"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.email')); ?></label>
                        <input type="email" x-model="newCustomer.email"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.company_name')); ?></label>
                        <input type="text" x-model="newCustomer.company_name"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.tax_number')); ?></label>
                        <input type="text" x-model="newCustomer.tax_no"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.crn')); ?></label>
                        <input type="text" x-model="newCustomer.CRN"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.credit_limit')); ?></label>
                        <input type="number" step="0.01" min="0" x-model.number="newCustomer.credit_limit"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.notesClient')); ?></label>
                        <input type="text" x-model="newCustomer.notes"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>

                    
                    <div class="md:col-span-3">
                        <hr class="my-2 border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 mb-2"><?php echo e(__('deliverynote.national_address')); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.district')); ?></label>
                        <input type="text" x-model="newCustomer.district"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.street_name')); ?></label>
                        <input type="text" x-model="newCustomer.street_name"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.building_number')); ?></label>
                        <input type="text" x-model="newCustomer.building_number"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.plot_identification')); ?></label>
                        <input type="text" x-model="newCustomer.plot_identification"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.postal_code')); ?></label>
                        <input type="text" x-model="newCustomer.postal_code"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                </div>
                <div class="flex items-center gap-3 mt-6">
                    <button type="button" @click="createCustomer()"
                            class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                        <?php echo e(__('deliverynote.add')); ?>

                    </button>
                    <button type="button" @click="customerModalOpen = false"
                            class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        <?php echo e(__('deliverynote.cancel')); ?>

                    </button>
                </div>
            </div>
        </div>

        
        <div x-show="productModalOpen" x-cloak
             class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto">
            <div class="bg-white rounded-xl p-6 w-full max-w-2xl my-8" @click.outside="productModalOpen = false">
                <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                    <h3 class="font-semibold text-lg text-gray-800"><?php echo e(__('deliverynote.quick_add_product')); ?></h3>
                    <label class="flex items-center gap-2 text-sm font-medium text-red-600 cursor-pointer">
                        <input type="checkbox" x-model="translateEnabled"
                               @change="translateEnabled && newProduct.name ? translateProductName() : null"
                               class="rounded border-gray-300 text-[#1456E8] focus:ring-[#1456E8]">
                        تفعيل الترجمة
                    </label>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.product_name')); ?> *</label>
                        <input type="text" x-model="newProduct.name" @blur="translateEnabled ? translateProductName() : null"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.product_name_en')); ?></label>
                        <input type="text" x-model="newProduct.name_en"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.product_number')); ?></label>
                        <input type="text" x-model="newProduct.code"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.product_location')); ?></label>
                        <input type="text" x-model="newProduct.location"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.unit')); ?></label>
                        <input type="text" x-model="newProduct.unit"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.quantity')); ?></label>
                        <input type="number" step="0.01" min="0" x-model.number="newProduct.stock_quantity"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.purchase_price')); ?></label>
                        <input type="number" step="0.01" min="0" x-model.number="newProduct.purchase_price"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.sale_price')); ?></label>
                        <input type="number" step="0.01" min="0" x-model.number="newProduct.sale_price"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.low_stock_alert_quantity')); ?></label>
                        <input type="number" step="1" min="0" x-model.number="newProduct.low_stock_alert_quantity"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('deliverynote.notesClient')); ?></label>
                        <input type="text" x-model="newProduct.notes"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                </div>
                <div class="flex items-center gap-3 mt-6">
                    <button type="button" @click="createProduct()"
                            class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                        <?php echo e(__('deliverynote.add')); ?>

                    </button>
                    <button type="button" @click="productModalOpen = false"
                            class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        <?php echo e(__('deliverynote.cancel')); ?>

                    </button>
                </div>
            </div>
        </div>

        
        <div x-show="productPickerOpen" x-cloak
             class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl w-full max-w-6xl my-8 flex flex-col max-h-[90vh] shadow-2xl" @click.outside="productPickerOpen = false">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/10 bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] rounded-t-xl">
                    <h3 class="font-semibold text-white"><?php echo e(__('deliverynote.choose_product')); ?></h3>
                    <button type="button" @click="productPickerOpen = false" class="text-white/60 hover:text-white transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-6 py-4 border-b border-gray-100">
                    <input type="text" x-model="pickerSearch" @input.debounce.300ms="loadProducts(1)"
                           placeholder="<?php echo e(__('deliverynote.search_product_placeholder')); ?>"
                           class="w-full max-w-md rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                </div>
                <div class="overflow-y-auto">
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0">
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">#</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('deliverynote.product_number')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('deliverynote.product_name')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('deliverynote.quantity')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('deliverynote.unit_price')); ?></th>
                                <th class="px-3 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <template x-for="(p, idx) in pickerProducts" :key="p.id">
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-3 py-2 text-gray-400" x-text="(pickerPage - 1) * 20 + idx + 1"></td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100" x-text="p.code || '-'"></span>
                                    </td>
                                    <td class="px-3 py-2 font-medium text-gray-800 min-w-[220px] whitespace-normal" x-text="p.name"></td>
                                    <td class="px-3 py-2">
                                        <span x-show="(p.stock_quantity ?? 0) <= 0" class="text-red-600 font-bold text-xs"><?php echo e(__('deliverynote.not_available')); ?></span>
                                        <span x-show="(p.stock_quantity ?? 0) > 0" class="text-emerald-600 font-bold" x-text="p.stock_quantity"></span>
                                    </td>
                                    <td class="px-3 py-2 text-gray-500" x-text="(parseFloat(p.sale_price) || 0).toFixed(2)"></td>
                                    <td class="px-3 py-2">
                                        <button type="button" @click="addProduct(p); markAdded(p.id)"
                                                class="px-3 py-1.5 rounded-lg text-white text-xs font-medium transition whitespace-nowrap"
                                                :class="isAdded(p.id) ? 'bg-emerald-500' : 'bg-[#F5811E] hover:brightness-95'">
                                            <span x-show="!isAdded(p.id)">+ <?php echo e(__('deliverynote.add')); ?></span>
                                            <span x-show="isAdded(p.id)">✓ <?php echo e(__('deliverynote.added')); ?></span>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="!pickerLoading && pickerProducts.length === 0">
                                <td colspan="6" class="px-3 py-8 text-center text-gray-400">
                                    <?php echo e(__('deliverynote.no_products_found')); ?>

                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-between px-6 py-3 border-t border-gray-100 flex-wrap gap-2">
                    <span class="text-xs text-gray-400"
                          x-text="pickerTotal > 0 ? '<?php echo e(__('deliverynote.page_of_total')); ?>'.replace(':current', pickerPage).replace(':last', pickerLastPage).replace(':total', pickerTotal) : ''"></span>
                    <div class="flex gap-2">
                        <button type="button" @click="loadProducts(pickerPage - 1)" :disabled="pickerPage <= 1"
                                class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:hover:bg-white transition">
                            <?php echo e(__('deliverynote.previous')); ?>

                        </button>
                        <button type="button" @click="loadProducts(pickerPage + 1)" :disabled="pickerPage >= pickerLastPage"
                                class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:hover:bg-white transition">
                            <?php echo e(__('deliverynote.next')); ?>

                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        function deliveryForm() {
            return {
                isSubmitting: false,
                items: <?php echo json_encode($existingItems, 15, 512) ?>,
                searchQuery: '',
                searchResults: [],
                productPickerOpen: false,
                pickerSearch: '',
                pickerProducts: [],
                pickerPage: 1,
                pickerLastPage: 1,
                pickerTotal: 0,
                pickerLoading: false,
                addedProductIds: [],
                selectedCustomerId: '<?php echo e($invoice->customer_id); ?>',
                customerModalOpen: false,
                customerTomSelect: null,
                newCustomer: {
                    name: '', phone: '', email: '', company_name: '',
                    tax_no: '', CRN: '', credit_limit: 0, notes: '',
                    district: '', street_name: '', building_number: '',
                    plot_identification: '', postal_code: '',
                },
                productModalOpen: false,
                translateEnabled: false,
                newProduct: {
                    name: '', name_en: '', code: '', location: '', unit: '',
                    stock_quantity: 0, purchase_price: 0, sale_price: 0,
                    low_stock_alert_quantity: 0, notes: '',
                },
                note: <?php echo json_encode($invoice->note && $invoice->note !== '-' ? $invoice->note : '', 15, 512) ?>,
                poNumber: '',
                discountOnInvoice: 0,
                taxRate: <?php echo e($defaultTaxRate); ?>,

                init() {
                    const el = document.getElementById('customer_select');
                    if (el && window.TomSelect) {
                        this.customerTomSelect = new TomSelect(el, {
                            create: false,
                            placeholder: '-',
                            valueField: 'id',
                            labelField: 'text',
                            searchField: [],
                            load: (query, callback) => {
                                if (!query || query.length < 2) {
                                    callback();
                                    return;
                                }
                                fetch(`<?php echo e(route('customers.search')); ?>?q=` + encodeURIComponent(query))
                                    .then((res) => res.json())
                                    .then((json) => callback(json))
                                    .catch(() => callback());
                            },
                            onChange: (value) => {
                                this.selectedCustomerId = value;
                            },
                        });
                    }
                },

                async searchProducts() {
                    if (this.searchQuery.trim().length < 1) {
                        this.searchResults = [];
                        return;
                    }
                    const res = await fetch(`<?php echo e(route('deliverynote.products.search')); ?>?q=` + encodeURIComponent(this.searchQuery));
                    this.searchResults = await res.json();
                },

                addProduct(p) {
                    this.items.push({
                        product_id: p.id,
                        name: p.name,
                        code: p.code,
                        quantity: 1,
                        unit_price: parseFloat(p.sale_price) || 0,
                        purchase_price: parseFloat(p.purchase_price) || 0,
                        discount: 0,
                    });
                    this.searchQuery = '';
                    this.searchResults = [];
                },

                removeItem(index) {
                    const removed = this.items[index];
                    this.items.splice(index, 1);
                    if (removed && removed.product_id) {
                        this.addedProductIds = this.addedProductIds.filter(id => id !== removed.product_id);
                    }
                },

                openProductPicker() {
                    this.productPickerOpen = true;
                    this.pickerSearch = '';
                    this.loadProducts(1);
                },

                async loadProducts(page) {
                    if (page < 1) return;
                    this.pickerLoading = true;
                    try {
                        const res = await fetch(`<?php echo e(route('deliverynote.products.pick')); ?>?q=` + encodeURIComponent(this.pickerSearch) + `&page=` + page);
                        const data = await res.json();
                        this.pickerProducts = data.data;
                        this.pickerPage = data.current_page;
                        this.pickerLastPage = data.last_page;
                        this.pickerTotal = data.total;
                    } finally {
                        this.pickerLoading = false;
                    }
                },

                isAdded(id) {
                    return this.addedProductIds.includes(id);
                },

                markAdded(id) {
                    if (!this.addedProductIds.includes(id)) this.addedProductIds.push(id);
                },

                async createCustomer() {
                    if (!this.newCustomer.name || !this.newCustomer.phone) {
                        Swal.fire({
                            icon: 'warning',
                            title: <?php echo json_encode(__('deliverynote.enter_customer_name_phone'), 15, 512) ?>,
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: <?php echo json_encode(__('deliverynote.ok'), 15, 512) ?>,
                        });
                        return;
                    }
                    const res = await fetch(`<?php echo e(route('deliverynote.customers.quick')); ?>`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(this.newCustomer),
                    });
                    const data = await res.json();
                    if (data.id) {
                        if (this.customerTomSelect) {
                            this.customerTomSelect.addOption({ id: String(data.id), text: data.name });
                            this.customerTomSelect.addItem(String(data.id));
                        } else {
                            const select = document.getElementById('customer_select');
                            const opt = document.createElement('option');
                            opt.value = data.id;
                            opt.text = data.name;
                            opt.selected = true;
                            select.add(opt);
                        }
                        this.selectedCustomerId = String(data.id);
                        this.customerModalOpen = false;
                        this.newCustomer = {
                            name: '', phone: '', email: '', company_name: '',
                            tax_no: '', CRN: '', credit_limit: 0, notes: '',
                            district: '', street_name: '', building_number: '',
                            plot_identification: '', postal_code: '',
                        };
                    }
                },

                async translateProductName() {
                    if (!this.newProduct.name) {
                        return;
                    }
                    try {
                        const res = await fetch(`<?php echo e(route('products.translate')); ?>?text=` + encodeURIComponent(this.newProduct.name));
                        const data = await res.json();
                        if (data && data.translated) {
                            this.newProduct.name_en = data.translated;
                        }
                    } catch (e) {
                        // نتجاهل أي خطأ شبكة/ترجمة بهدوء - الحقل هيفضل قابل للتعديل يدويًا
                    }
                },
                async createProduct() {
                    if (!this.newProduct.name) {
                        Swal.fire({
                            icon: 'warning',
                            title: <?php echo json_encode(__('deliverynote.complete_product_data'), 15, 512) ?>,
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: <?php echo json_encode(__('deliverynote.ok'), 15, 512) ?>,
                        });
                        return;
                    }
                    const res = await fetch(`<?php echo e(route('deliverynote.products.quick')); ?>`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(this.newProduct),
                    });
                    const data = await res.json();
                    if (data.id) {
                        this.addProduct(data);
                        this.productModalOpen = false;
                        this.newProduct = {
                            name: '', name_en: '', code: '', location: '', unit: '',
                            stock_quantity: 0, purchase_price: 0, sale_price: 0,
                            low_stock_alert_quantity: 0, notes: '',
                        };
                    }
                },

                lineTotal(item) {
                    return ((parseFloat(item.unit_price) || 0) * (parseFloat(item.quantity) || 0)) - (parseFloat(item.discount) || 0);
                },

                lineProfit(item) {
                    return (parseFloat(item.unit_price) || 0) - (parseFloat(item.purchase_price) || 0);
                },

                get subtotal() {
                    return this.items.reduce((sum, i) => sum + this.lineTotal(i), 0);
                },

                get totalProfit() {
                    // إجمالي الربح = مجموع (الربح على القطعة × الكمية) لكل الأصناف
                    return this.items.reduce((sum, i) => sum + (this.lineProfit(i) * (parseFloat(i.quantity) || 0)), 0);
                },

                get taxAmount() {
                    const net = this.subtotal - (parseFloat(this.discountOnInvoice) || 0);
                    return net > 0 ? net * (parseFloat(this.taxRate) || 0) : 0;
                },

                get grandTotal() {
                    const total = this.subtotal - (parseFloat(this.discountOnInvoice) || 0) + this.taxAmount;
                    return total > 0 ? total : 0;
                },

                submitDelivery() {
                    // خط الدفاع الأول ضد الضغط المتكرر - لو فيه إرسال شغال
                    // بالفعل، أي ضغطة تانية بترجع فورًا من غير ما تعمل حاجة.
                    if (this.isSubmitting) {
                        return;
                    }
                    if (!this.selectedCustomerId) {
                        Swal.fire({
                            icon: 'warning',
                            title: <?php echo json_encode(__('deliverynote.select_customer_required'), 15, 512) ?>,
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: <?php echo json_encode(__('deliverynote.ok'), 15, 512) ?>,
                        });
                        return;
                    }
                    if (this.items.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: <?php echo json_encode(__('deliverynote.please_add_product'), 15, 512) ?>,
                            confirmButtonColor: '#0F1B4C',
                            confirmButtonText: <?php echo json_encode(__('deliverynote.ok'), 15, 512) ?>,
                        });
                        return;
                    }

                    this.isSubmitting = true;
                    document.getElementById('items_json').value = JSON.stringify(this.items);
                    document.getElementById('delivery-form').submit();
                },
            }
        }
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
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/delivery-note/edit.blade.php ENDPATH**/ ?>