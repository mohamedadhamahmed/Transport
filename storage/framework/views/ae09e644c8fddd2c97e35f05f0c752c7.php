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

    <div class="py-6" x-data="productMovementReport()">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 12h4l3 8 4-16 3 8h4"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg"><?php echo e(__('reports.products.movement')); ?></h2>
                </div>
                <a href="<?php echo e(route('reports.products.index')); ?>"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    <?php echo e(__('reports.products.title')); ?>

                </a>
            </div>

            
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('reports.products.movement_choose_product')); ?></label>
                <div class="relative max-w-md">
                    <input type="text" x-model="searchQuery" @input.debounce.300ms="search()"
                           placeholder="<?php echo e(__('reports.products.movement_search_placeholder')); ?>"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    <div x-show="searchResults.length > 0" x-cloak
                         class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-y-auto">
                        <template x-for="p in searchResults" :key="p.id">
                            <button type="button" @click="selectProduct(p)"
                                    class="w-full text-start px-4 py-2 text-sm hover:bg-[#1456E8]/5 border-b border-gray-50 last:border-0">
                                <span x-text="p.name"></span>
                                <span class="text-gray-400" x-text="p.code ? ' (' + p.code + ')' : ''"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <template x-if="selectedProduct">
                    <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm bg-[#1456E8]/10 text-[#1456E8] border border-[#1456E8]/10">
                        <span x-text="selectedProduct.name"></span>
                        <button type="button" @click="clearProduct()" class="text-[#1456E8]/60 hover:text-[#1456E8]">×</button>
                    </div>
                </template>
            </div>

            
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl overflow-hidden" x-show="selectedProduct" x-cloak>
                <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('invoices.operation_type')); ?></label>
                        <select x-model="operationsType" @change="loadOperations()"
                            class="rounded-lg border-gray-300 shadow-sm text-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value="all"><?php echo e(__('invoices.operation_type_all')); ?></option>
                            <option value="sales"><?php echo e(__('products.operations.type_sales')); ?></option>
                            <option value="purchases"><?php echo e(__('products.operations.type_purchases')); ?></option>
                            <option value="transfers"><?php echo e(__('products.operations.type_transfers')); ?></option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('reports.date_from')); ?></label>
                        <input type="date" x-model="operationsDateFrom" @change="loadOperations()"
                            class="rounded-lg border-gray-300 shadow-sm text-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('reports.date_to')); ?></label>
                        <input type="date" x-model="operationsDateTo" @change="loadOperations()"
                            class="rounded-lg border-gray-300 shadow-sm text-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">#</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.invoice_number')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.product')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.date')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.operation_type')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.operation_entity')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.quantity')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.unit_price')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <template x-for="(row, idx) in operationsRows" :key="idx">
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-3 py-2 text-gray-400" x-text="idx + 1"></td>
                                    <td class="px-3 py-2 text-gray-700" x-text="row.document_number || '-'"></td>
                                    <td class="px-3 py-2 font-medium text-gray-800" x-text="row.product_name"></td>
                                    <td class="px-3 py-2 text-gray-500" x-text="row.date || '-'"></td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                              :class="{
                                                  'bg-emerald-50 text-emerald-700 border border-emerald-100': row.type_key === 'sales',
                                                  'bg-[#F5811E]/10 text-[#F5811E] border border-[#F5811E]': row.type_key === 'purchases',
                                                  'bg-[#1456E8]/10 text-[#1456E8]': row.type_key === 'transfers',
                                              }" x-text="row.type_label"></span>
                                    </td>
                                    <td class="px-3 py-2 text-gray-600" x-text="row.entity_name || '-'"></td>
                                    <td class="px-3 py-2 text-gray-600" x-text="row.quantity"></td>
                                    <td class="px-3 py-2 text-gray-600" x-text="(parseFloat(row.price) || 0).toFixed(2)"></td>
                                </tr>
                            </template>
                            <tr x-show="!operationsLoading && operationsRows.length === 0">
                                <td colspan="8" class="px-3 py-8 text-center text-gray-400">
                                    <?php echo e(__('invoices.no_operations_found')); ?>

                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-10 text-center text-gray-400" x-show="!selectedProduct">
                <?php echo e(__('reports.products.movement_no_product_selected')); ?>

            </div>
        </div>
    </div>

    <script>
        function productMovementReport() {
            return {
                searchQuery: '',
                searchResults: [],
                selectedProduct: null,
                operationsRows: [],
                operationsLoading: false,
                operationsType: 'all',
                operationsDateFrom: '',
                operationsDateTo: '',
                async search() {
                    if (this.searchQuery.trim().length < 1) {
                        this.searchResults = [];
                        return;
                    }
                    const res = await fetch(`<?php echo e(route('invoices.products.search')); ?>?q=` + encodeURIComponent(this.searchQuery));
                    this.searchResults = await res.json();
                },
                selectProduct(p) {
                    this.selectedProduct = p;
                    this.searchQuery = '';
                    this.searchResults = [];
                    this.loadOperations();
                },
                clearProduct() {
                    this.selectedProduct = null;
                    this.operationsRows = [];
                },
                async loadOperations() {
                    if (!this.selectedProduct) return;
                    this.operationsLoading = true;
                    try {
                        const params = new URLSearchParams({ type: this.operationsType });
                        if (this.operationsDateFrom) params.set('date_from', this.operationsDateFrom);
                        if (this.operationsDateTo) params.set('date_to', this.operationsDateTo);
                        const url = `<?php echo e(route('products.operations.data', ['product' => '__PID__'])); ?>`.replace('__PID__', this.selectedProduct.id);
                        const res = await fetch(url + '?' + params.toString());
                        const data = await res.json();
                        this.operationsRows = data.rows || [];
                    } finally {
                        this.operationsLoading = false;
                    }
                },
            };
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
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/reports/products/movement.blade.php ENDPATH**/ ?>