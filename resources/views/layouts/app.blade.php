<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
@include('layouts.head')
<!-- يمكنك وضع الـ CSS هنا مباشرة أو داخل ملف layouts.head -->
<link href="{{ asset('assets/libs/tom-select/css/tom-select.css') }}" rel="stylesheet">

<body class="font-sans antialiased bg-gray-100" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">

        @include('layouts.main-sidebar')

        <div class="flex-1 flex flex-col min-w-0">

            @include('layouts.main-header')

            <!-- عنوان الصفحة -->
            @if (isset($header))
                <div class="bg-white border-b px-4 sm:px-6 lg:px-8 py-4">
                    {{ $header }}
                </div>
            @endif

            <!-- محتوى الصفحة -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>

            @include('layouts.footer')
        </div>
    </div>

    <!-- جميع السكربتات يجب أن تكون هنا داخل الـ body -->
    @include('layouts.footer-scripts')
    
    <script src="{{ asset('assets/libs/tom-select/js/tom-select.complete.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>