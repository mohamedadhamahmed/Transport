<x-app-layout>
    @include('transport.partials.styles')
    @include('transport.partials.phone-script')
    <div class="py-6">
        <div class="max-w-[1200px] mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('transport.partials.page-header', ['title' => __('transport.edit_truck')])
            @include('transport.partials.flash')

            <form method="POST" action="{{ route('transport.trucks.update', $truck) }}" class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="return_to" value="{{ old('return_to', request('return_to')) }}">
                @include('transport.trucks._form')
            </form>
        </div>
    </div>
</x-app-layout>
