<x-app-layout>
    <div class="p-6 max-w-2xl mx-auto" dir="rtl">
        <h1 class="text-lg font-bold text-[#0F1B4C] mb-4">{{ __('contracts.edit') }}</h1>
        <form action="{{ route('contracts.update', $contract) }}" method="POST" class="bg-white rounded-xl shadow p-6 space-y-4">
            @csrf @method('PUT')
            @include('contracts._form')
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2.5 rounded-lg">
                {{ __('contracts.save') }}
            </button>
        </form>
    </div>
</x-app-layout>