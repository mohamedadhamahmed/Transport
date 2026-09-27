<x-app-layout>
    @include('transport.partials.styles')
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('transport.partials.page-header', ['title' => __('transport.new_invoice'), 'subtitle' => __('transport.new_invoice_subtitle')])
            @include('transport.partials.flash')

            <form method="POST" action="{{ route('transport.invoices.store') }}" id="transport-invoice-form">
                @csrf
                @include('transport.invoices._form')
            </form>
        </div>
    </div>
</x-app-layout>
