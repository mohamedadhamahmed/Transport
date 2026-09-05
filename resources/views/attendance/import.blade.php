<x-app-layout>
    <div class="py-6">
        <div class="max-w-[900px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center gap-3">
                <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3v12m0 0l-4-4m4 4l4-4M4 21h16" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-white font-bold text-lg leading-tight">{{ __('attendance.import_from_biometric') }}</h2>
                    <p class="text-white/45 text-xs mt-0.5">{{ __('attendance.subtitle') }}</p>
                </div>
            </div>

            @if(session('success'))
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-2.5">
                {{ session('success') }}
            </div>
            @endif
            @if($errors->any())
            <div class="rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-2.5">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
            @endif

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6 space-y-6">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <p class="text-sm text-gray-600">{{ __('attendance.subtitle') }}</p>
                    <a href="{{ route('attendance.import.template') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-[#1456E8] bg-[#1456E8]/10 hover:bg-[#1456E8]/20 transition whitespace-nowrap">
                        {{ __('attendance.download_template') }}
                    </a>
                </div>

                <form method="POST" action="{{ route('attendance.import.store') }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
                    @csrf
                    <input type="file" name="attendance_excel" accept=".xlsx,.xls" required
                           class="w-full max-w-md text-sm text-gray-700 file:me-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[#0F1B4C] file:text-white">
                    <button type="submit" class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                        {{ __('attendance.upload') }}
                    </button>
                    <a href="{{ route('attendance.index') }}" class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        {{ __('attendance.cancel') }}
                    </a>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
