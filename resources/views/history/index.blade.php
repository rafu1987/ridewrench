@section('title', __('history.title'))

<x-app-layout>
    <div class="page-hero mb-4">
        <div>
            <p class="text-white-50 mb-1">
                <i class="fa-light fa-sharp fa-clock-rotate-left me-1"></i>
                {{ __('history.kicker') }}
            </p>

            <h1 class="display-6 fw-bold mb-1">
                {{ __('history.title') }}
            </h1>

            <p class="mb-0 text-white-50">
                {{ __('history.subtitle') }}
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-body py-5 text-center">
            @if ($events->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped rounded-3 overflow-hidden text-start mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('history.date') }}</th>
                                <th>{{ __('history.bike') }}</th>
                                <th>{{ __('history.rule') }}</th>
                                <th>{{ __('history.note') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $event)
                                <tr>
                                    <td class="text-nowrap">
                                        {{ \App\Support\RideWrench::formatDateTime($event->performed_at) }}
                                    </td>
                                    <td>
                                        {{ $event->bike_name ?: __('common.notSet') }}
                                    </td>
                                    <td>
                                        {{ $event->rule_name ?: __('common.notSet') }}
                                    </td>
                                    <td>
                                        @if (!empty($event->note))
                                            {{ $event->note }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($events->hasPages())
                    <div class="p-3 border-top">
                        {{ $events->links() }}
                    </div>
                @endif
            @else
                <div class="p-4 p-lg-5 text-center">
                    <div class="display-5 text-success mb-3">
                        <i class="fa-light fa-sharp fa-clock-rotate-left"></i>
                    </div>

                    <h2 class="h4">
                        {{ __('history.emptyTitle') }}
                    </h2>

                    <p class="text-muted mb-0">
                        {{ __('history.emptyText') }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>