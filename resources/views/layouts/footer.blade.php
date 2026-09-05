<footer class="bg-white text-gray-600 border-t border-gray-200 mt-auto py-4 px-6">
    <div class="max-w-7xl mx-auto flex flex-col items-center justify-center text-center gap-4 text-xs">
        
        {{-- الشعار والاسم --}}
        <div class="flex flex-wrap items-center justify-center gap-2.5">
            <img src="{{ asset('images/sidebar-icon.png') }}" alt="{{ config('app.name', 'NEW VISION') }}" class="w-6 h-6 object-contain">
            <span class="text-gray-900 font-bold">{{ config('app.name', 'NEW VISION') }}</span>
            <span class="text-gray-300">|</span>
            <span class="text-gray-500">نظام إدارة الفواتير والمبيعات</span>
            <span class="text-gray-500">جميع الحقوق محفوظة &copy; {{ date('Y') }}</span>
        </div>

    </div>
</footer>