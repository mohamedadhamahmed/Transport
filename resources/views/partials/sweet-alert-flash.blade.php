{{--
    رسائل SweetAlert2 - بتظهر تلقائي أي وقت فيه session('success') أو
    session('error') أو أخطاء validation، بدل الشريط الرمادي/الأخضر
    القديم. ضيفي @include('partials.sweet-alert-flash') في أي صفحة
    عايزة الرسائل دي تظهر فيها (تحت هيدر الصفحة مباشرة أحسن مكان).
--}}
@if (session('success') || session('error') || $errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: @json(session('success')),
                    confirmButtonColor: '#0F1B4C',
                    confirmButtonText: '{{ app()->getLocale() === 'ar' ? 'تم' : 'OK' }}',
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: @json(session('error')),
                    confirmButtonColor: '#0F1B4C',
                    confirmButtonText: '{{ app()->getLocale() === 'ar' ? 'حسنًا' : 'OK' }}',
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: @json($errors->first()),
                    confirmButtonColor: '#0F1B4C',
                    confirmButtonText: '{{ app()->getLocale() === 'ar' ? 'حسنًا' : 'OK' }}',
                });
            @endif
        });
    </script>
@endif
