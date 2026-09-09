
<div class="md:col-span-3 border-t border-gray-100 pt-4 mt-2"
     x-data="productAlternatesPicker(<?php echo \Illuminate\Support\Js::from($selectedPrimaries)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($excludeId)->toHtml() ?>)">
    <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
        <input type="checkbox" x-model="isAlternate"
               class="rounded border-gray-300 text-[#1456E8] focus:ring-[#1456E8]">
        <?php echo e(__('products.alternates_section_title')); ?>

    </label>
    
    <input type="hidden" name="is_alternate" :value="isAlternate ? 1 : 0">

    <div x-show="isAlternate" x-cloak class="mt-3 space-y-2">
        <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('products.primary_products_label')); ?></label>
        <div class="relative">
            <input type="text" x-model="searchQuery" @input.debounce.300ms="search()"
                   placeholder="<?php echo e(__('products.search_primary_product_placeholder')); ?>"
                   class="w-full max-w-md rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            <div x-show="searchResults.length > 0" x-cloak
                 class="absolute z-10 mt-1 w-full max-w-md bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-y-auto">
                <template x-for="p in searchResults" :key="p.id">
                    <button type="button" @click="select(p)"
                            class="w-full text-start px-4 py-2 text-sm hover:bg-[#1456E8]/5 border-b border-gray-50 last:border-0"
                            x-text="p.text"></button>
                </template>
            </div>
        </div>
        <div class="flex flex-wrap gap-2 mt-2">
            <template x-for="p in selected" :key="p.id">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-[#1456E8]/10 text-[#1456E8] border border-[#1456E8]/20">
                    <span x-text="p.text"></span>
                    <button type="button" @click="remove(p.id)" class="text-[#1456E8]/60 hover:text-[#1456E8]" :aria-label="'<?php echo e(__('products.remove_primary_product')); ?>'">×</button>
                    <input type="hidden" name="primary_product_ids[]" :value="p.id">
                </span>
            </template>
            <span x-show="selected.length === 0" class="text-xs text-gray-400"><?php echo e(__('products.no_primary_products_selected')); ?></span>
        </div>
    </div>
</div>

<script>
    function productAlternatesPicker(initialSelected, excludeId) {
        return {
            isAlternate: (initialSelected || []).length > 0,
            searchQuery: '',
            searchResults: [],
            selected: initialSelected || [],
            async search() {
                if (this.searchQuery.trim().length < 1) {
                    this.searchResults = [];
                    return;
                }
                const params = new URLSearchParams({ q: this.searchQuery });
                if (excludeId) params.set('exclude_id', excludeId);
                const res = await fetch(`<?php echo e(route('products.search-alternates')); ?>?` + params.toString());
                const data = await res.json();
                const selectedIds = this.selected.map(p => p.id);
                this.searchResults = data.filter(p => !selectedIds.includes(p.id));
            },
            select(p) {
                if (!this.selected.some(s => s.id === p.id)) {
                    this.selected.push(p);
                }
                this.searchQuery = '';
                this.searchResults = [];
            },
            remove(id) {
                this.selected = this.selected.filter(p => p.id !== id);
            },
        };
    }
</script>
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/products/_alternates-field.blade.php ENDPATH**/ ?>