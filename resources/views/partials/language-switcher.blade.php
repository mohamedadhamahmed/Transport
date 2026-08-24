<div class="flex items-center justify-center gap-2 py-2 text-sm">
    <a href="{{ route('lang.switch', 'ar') }}"
       class="px-3 py-1 rounded {{ app()->getLocale() === 'ar' ? 'font-bold text-indigo-600 underline' : 'text-gray-500 hover:text-indigo-500' }}">
        العربية
    </a>
    <span class="text-gray-300">|</span>
    <a href="{{ route('lang.switch', 'en') }}"
       class="px-3 py-1 rounded {{ app()->getLocale() === 'en' ? 'font-bold text-indigo-600 underline' : 'text-gray-500 hover:text-indigo-500' }}">
        English
    </a>
</div>
