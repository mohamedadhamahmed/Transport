<x-app-layout>
    @include('transport.partials.styles')
    @include('transport.partials.phone-script')
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('transport.partials.page-header', ['title' => __('transport.new_waybill')])
            @include('transport.partials.flash')

            <form method="POST" action="{{ route('transport.waybills.store') }}">
                @csrf
                @include('transport.waybills._form')
            </form>
        </div>
    </div>
</x-app-layout>
