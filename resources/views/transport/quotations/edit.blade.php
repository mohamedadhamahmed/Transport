<x-app-layout>
    @include('transport.partials.styles')
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('transport.partials.page-header', ['title' => __('transport.edit_quotation') . ' ' . $quotation->quotation_number])
            @include('transport.partials.flash')

            <form method="POST" action="{{ route('transport.quotations.update', $quotation) }}">
                @csrf
                @method('PUT')
                @include('transport.quotations._form')
            </form>
        </div>
    </div>
</x-app-layout>
