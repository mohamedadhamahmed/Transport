<footer class="bg-[#0F1B4C] text-white/70 border-t border-white/10 mt-auto py-4 px-6">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
        
        {{-- الشعار والاسم --}}
        <div class="flex items-center gap-2.5">
            <img src="{{ asset('images/sidebar-icon.png') }}" alt="{{ config('app.name', 'دفتركم') }}" class="w-6 h-6 object-contain">
            <span class="text-white font-bold">{{ config('app.name', 'دفتركم') }}</span>
            <span class="text-white/30">|</span>
            <span class="text-white/50">نظام إدارة الفواتير والمبيعات</span>
            <span>            جميع الحقوق محفوظة &copy; {{ date('Y') }}
            </span>
        </div>




    </div>
</footer>