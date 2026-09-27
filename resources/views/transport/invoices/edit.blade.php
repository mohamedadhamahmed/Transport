<x-app-layout>
    @include('transport.partials.styles')
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('transport.partials.page-header', ['title' => $invoice->is_draft ? __('transport.edit_draft') . ' #' . $invoice->id : __('transport.edit_invoice') . ' ' . $invoice->invoice_number])
            @include('transport.partials.flash')

            <form method="POST" action="{{ route('transport.invoices.update', $invoice) }}" id="transport-invoice-form">
                @csrf
                @method('PUT')
                @include('transport.invoices._form')
            </form>
        </div>
    </div>
</x-app-layout>
