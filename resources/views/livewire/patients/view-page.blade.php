<div class="min-h-screen bg-gray-50">
    @include('contents.patients.show.header')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('contents.patients.show.statistics')

        @include('contents.patients.show.next-appointment-alert')
  
        @include('contents.patients.show.tabs')

        <div class="tab-content">
            @if ($activeTab === 'overview')
                @include('contents.patients.show.overview-tab')
            @endif

            @if ($activeTab === 'appointments')
                @include('contents.patients.show.appointments-tab')
            @endif

            @if ($activeTab === 'visits')
                @include('contents.patients.show.visits-tab')
            @endif
        </div>
    </div>

    @include('contents.patients.show.loading-overlay')
</div>

@push('styles')
    @include('contents.patients.show.styles')
@endpush
