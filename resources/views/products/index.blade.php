<x-app-layout>
    <div class="py-6" x-data="{
            stockModalOpen: false,
            stockProductId: null,
            stockProductName: '',
            stockNewQty: 0,
            openStockModal(id, name, qty) {
                this.stockProductId = id;
                this.stockProductName = name;
                this.stockNewQty = qty;
                this.stockModalOpen = true;
            },
            search: '{{ request('search') }}',
            group: '{{ request('product_group') }}',
            loading: false,
            fetchProducts(url = null) {
                this.loading = true;
                const baseUrl = '{{ route('products.index', $branch->id) }}';
                let finalUrl;

                if (url) {
                    // pagination link جاي فيه query string خاص بيه، نضيفله partial=1
                    const u = new URL(url, window.location.origin);
                    u.searchParams.set('partial', '1');
                    finalUrl = u.toString();
                } else {
                    const params = new URLSearchParams();
                    if (this.search) params.set('search', this.search);
                    if (this.group) params.set('product_group', this.group);
                    params.set('partial', '1');
                    finalUrl = baseUrl + '?' + params.toString();
                }

                fetch(finalUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.text())
                .then(html => {
                    document.getElementById('products-table-wrapper').innerHTML = html;
                    // نحدث شريط العنوان بدون باراميتر partial
                    const cleanUrl = new URL(finalUrl, window.location.origin);
                    cleanUrl.searchParams.delete('partial');
                    window.history.pushState({}, '', cleanUrl.toString());
                    this.loading = false;
                })
                .catch(() => { this.loading = false; });
            }
         }"
         @click.prevent="
            if ($event.target.closest('#products-table-wrapper .pagination a, #products-table-wrapper nav a')) {
                fetchProducts($event.target.closest('a').getAttribute('href'));
            }
         ">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 8l9-5 9 5-9 5-9-5Z" /><path d="M3 8v8l9 5 9-5V8" /><path d="M12 13v8" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('products.all_products') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('products.branch') }}: {{ $branch->name }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('products.choose_branch') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        {{ __('products.change_branch') }}
                    </a>
                    <a href="{{ route('products.import.form', $branch->id) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-[#F5811E] hover:brightness-95 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3v12M7 8l5-5 5 5" /><path d="M5 21h14" />
                        </svg>
                        {{ __('products.import_excel') }}
                    </a>
                </div>
            </div>

            @if(session('success'))
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-2.5">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="relative">
                        <input type="text"
                               x-model="search"
                               @input.debounce.400ms="fetchProducts()"
                               placeholder="{{ __('products.search_placeholder') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>

                    <select x-model="group" @change="fetchProducts()"
                            class="rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        <option value="">{{ __('products.all_groups') }}</option>
                        @foreach($productGroups as $pg)
                            <option value="{{ $pg->id }}">{{ $pg->group_ar }}</option>
                        @endforeach
                    </select>

                    <div class="flex items-center">
                        <span x-show="loading" class="text-xs text-gray-400">{{ __('products.loading') ?? 'جاري التحميل...' }}</span>
                    </div>
                </div>

                <div id="products-table-wrapper" style="position: relative;">
                    @include('products._table')
                </div>
            </div>
        </div>

        {{-- مودال تعديل كمية المخزون - في نفس نطاق x-data بتاع الصفحة --}}
        <div x-show="stockModalOpen" x-cloak
             class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl p-6 w-full max-w-md" @click.outside="stockModalOpen = false">
                <h3 class="font-semibold text-lg text-gray-800 mb-1">{{ __('products.edit_stock') }}</h3>
                <p class="text-sm text-gray-500 mb-4" x-text="stockProductName"></p>

                <form :action="'/products/' + stockProductId + '/stock'" method="POST">
                    @csrf
                    @method('PATCH')
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.new_quantity') }}</label>
                    <input type="number" step="0.01" name="stock_quantity" x-model.number="stockNewQty"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#F5811E] focus:ring-[#F5811E] mb-3">

                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.reason') }}</label>
                    <input type="text" name="reason" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#F5811E] focus:ring-[#F5811E] mb-4">

                    <div class="flex items-center gap-3">
                        <button type="submit" class="px-5 py-2 rounded-lg text-white font-medium bg-[#F5811E] hover:brightness-95 transition">
                            {{ __('products.save') }}
                        </button>
                        <button type="button" @click="stockModalOpen = false" class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                            {{ __('products.cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>