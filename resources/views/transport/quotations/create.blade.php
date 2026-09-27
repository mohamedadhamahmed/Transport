<x-app-layout>
    @include('transport.partials.styles')
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('transport.partials.page-header', ['title' => __('transport.new_quotation')])
            @include('transport.partials.flash')

            <form method="POST" action="{{ route('transport.quotations.store') }}">
                @csrf
                @include('transport.quotations._form')
            </form>
        </div>
    </div>
</x-app-layout>
