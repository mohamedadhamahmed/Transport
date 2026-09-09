<x-app-layout>
    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm p-6">

                <h2 class="text-xl font-bold text-[#0F1B4C] mb-6">{{ __('manufacturing.edit') }} — {{ $plan->name }}</h2>

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-xl text-sm">
                        <ul class="list-disc pr-4">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('manufacturing.production-plans.update', $plan) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('manufacturing.name') }}</label>
                        <input type="text" name="name" value="{{ $plan->name }}" required class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('manufacturing.bom_product') }}</label>
                        <select name="product_id" required class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value="">—</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected($product->id === $plan->product_id)>{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('manufacturing.bom_title') }}</label>
                        <select name="bill_of_material_id" class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value="">—</option>
                            @foreach($boms as $bom)
                                <option value="{{ $bom->id }}" @selected($bom->id === $plan->bill_of_material_id)>{{ $bom->name }} (#{{ $bom->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('manufacturing.order_quantity') }}</label>
                        <input type="number" step="0.001" name="quantity" value="{{ $plan->quantity }}" required class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('manufacturing.plan_starts') }}</label>
                        <input type="date" name="date_start" value="{{ $plan->date_start->format('Y-m-d') }}" required class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('manufacturing.plan_ends') }}</label>
                        <input type="date" name="date_end" value="{{ $plan->date_end->format('Y-m-d') }}" required class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('manufacturing.status') }}</label>
                        <select name="status_id" class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}" @selected($status->id === $plan->status_id)>{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit" class="bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white py-2.5 px-6 rounded-lg font-medium hover:opacity-90 transition shadow-sm">
                            {{ __('manufacturing.save') }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
