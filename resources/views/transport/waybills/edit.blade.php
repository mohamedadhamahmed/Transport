<x-app-layout>
    @include('transport.partials.styles')
    @include('transport.partials.phone-script')
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('transport.partials.page-header', ['title' => __('transport.edit_waybill') . ' ' . $waybill->waybill_number])
            @include('transport.partials.flash')

            <form method="POST" action="{{ route('transport.waybills.update', $waybill) }}">
                @csrf
                @method('PUT')
                @include('transport.waybills._form')
            </form>
        </div>
    </div>
</x-app-layout>
