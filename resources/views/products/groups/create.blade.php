<x-app-layout>
    <div class="py-6">
        <div class="max-w-[600px] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <h2 class="text-lg font-bold mb-4 text-gray-800">{{ __('products.add_group') }}</h2>
                
                <form method="POST" action="{{ route('product-groups.store') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.group_name_ar') }} *</label>
                        <input type="text" name="group_ar" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.group_name_en') }}</label>
                        <input type="text" name="group_en" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>

                    <!-- أزرار الحفظ والإلغاء -->
                    <div class="flex items-center gap-3 mt-6">
                        <button type="submit" class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                            {{ __('products.save') }}
                        </button>
                        <a href="{{ route('products.choose_branch') }}" class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                            {{ __('products.cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>