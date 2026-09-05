<x-app-layout>
    <div class="py-6">
        <div class="max-w-[1200px] mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg">
                <h2 class="text-white font-bold text-lg">{{ __('employees.edit') }} - {{ $employee->name }}</h2>
            </div>
            <form method="POST" action="{{ route('employees.update', $employee->id) }}" class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                @csrf
                @method('PUT')
                @include('employees._form')
            </form>
        </div>
    </div>
</x-app-layout>
