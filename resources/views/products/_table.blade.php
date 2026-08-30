<div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
    <table class="min-w-full text-sm">
        <thead>
            <tr class="bg-[#0F1B4C] text-white/80">
                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase">{{ __('products.code') }}</th>
                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase">{{ __('products.name') }}</th>
                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase">{{ __('products.group') }}</th>
                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase">{{ __('products.stock_quantity') }}</th>
                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase">{{ __('products.purchase_price') }}</th>
                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase">{{ __('products.sale_price') }}</th>
                <th class="px-3 py-2.5"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
            @forelse($products as $product)
            <tr class="hover:bg-[#1456E8]/5 transition">
                <td class="px-3 py-2 text-gray-500">{{ $product->code }}</td>
                <td class="px-3 py-2 font-medium text-gray-800">{{ $product->name }}</td>
                <td class="px-3 py-2 text-gray-500">{{ $product->productGroup->name ?? '-' }}</td>
                <td class="px-3 py-2">
                    <span class="font-semibold {{ $product->stock_quantity <= ($product->low_stock_alert_quantity ?? 0) ? 'text-red-600' : 'text-emerald-600' }}">
                        {{ $product->stock_quantity }}
                    </span>
                </td>
                <td class="px-3 py-2 text-gray-600">{{ number_format($product->purchase_price, 2) }}</td>
                <td class="px-3 py-2 text-gray-600">{{ number_format($product->sale_price, 2) }}</td>
                <td class="px-3 py-2">
                    <div class="flex gap-2">
                        <a href="{{ route('products.edit', $product->id) }}"
                           class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-[#1456E8] bg-[#1456E8]/10 hover:bg-[#1456E8]/20 transition">
                            {{ __('products.edit') }}
                        </a>
                        <button type="button"
                                @click="openStockModal({{ $product->id }}, @js($product->name), {{ $product->stock_quantity }})"
                                class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-[#F5811E] bg-[#F5811E]/10 hover:bg-[#F5811E]/20 transition">
                            {{ __('products.edit_stock') }}
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-3 py-10 text-center text-gray-400">{{ __('products.no_data') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($products->hasPages())
<div class="mt-4">{{ $products->links() }}</div>
@endif