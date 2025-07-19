@extends('admin.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb-button')

@endsection
@section('css')
    <style>
        .widget-stat {
            position: relative;
            overflow: hidden;
        }

        .widget-stat .widget-icon {
            position: absolute;
            top: -12%;
            right: -6%;
            /* background: #fff; */
            filter: brightness(95%);

            color: white;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            font-size: 25px;
            justify-content: center;
            align-items: center;
        }

        canvas {
            max-width: 100%;
            height: 300px !important;
        }
    </style>
@endsection
@section('content')
    @php
        $widgets = ['bg-primary', 'bg-warning', 'bg-dark', 'bg-danger', 'bg-success', 'bg-info'];
        $widgetIcons = [
            'ri-folder-chart-line',
            'ri-send-plane-line',
            'ri-eye-line',
            'ri-close-circle-line',
            'ri-check-double-line',
            'ri-edit-line',
        ];
        $userCountWidgets = ['text-purple', 'text-success', 'text-primary', 'text-success'];
    @endphp
    <div class="card">
        <div class="card-body">

            <div class="card">
                <div class="card-body">
                    <div class="row task">
                        @foreach ($userCounts as $key => $count)
                            @php
                                $userWidgetClassIndex = $loop->index % 4;
                                $userWidgetClass = $userCountWidgets[$userWidgetClassIndex];

                            @endphp
                            <div class="col-xl-2 col-sm-4 col-6">
                                <div class="task-summary">
                                    <div class="d-flex align-items-center">
                                        <h2 class="{{ $userWidgetClass }} count">{{ $count }}</h2>
                                        <span>{{ ucfirst($key) }}</span>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                        <div class="col-4 d-flex justify-content-end">
                            <h3 id="liveClock" class="fw-bold text-purple mb-0"></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between gap-2">
                @foreach ($counts as $key => $count)
                    @php
                        $widgetClassIndex = $loop->index % 6;
                        $widgetClass = $widgets[$widgetClassIndex];
                        $icon = $widgetIcons[$widgetClassIndex];
                        $progressPercent =
                            ($count / isset($counts['Total Applications']) ? $counts['Total Applications'] : 1) * 100;
                    @endphp
                    <div class="widget-stat card flex-grow-1 {{ $widgetClass }} text-white">
                        <div class="widget-icon {{ $widgetClass }}">
                            <i class="{{ $icon }}"></i>
                        </div>
                        <div class="card-body p-4">
                            <h4 class="card-title text-white">
                                {{ str_replace('Applications', '', $key) }}
                            </h4>
                            <h3 class="mt-3 text-white">{{ $count }}</h3>
                            <div class="progress mb-2">
                                <div class="progress-bar progress-animated bg-light" style="width: {{ $progressPercent }}%">
                                </div>
                            </div>
                            <small>Applications</small>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row">
                <div class="col-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Monthly Applications</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyApplicationChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-4 px-1">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Recent Applications</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive recentOrderTable">
                                <table class="verticle-middle table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Application No.</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">Status</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentApplications as $application)
                                            <tr>
                                                <td> <a class="text-info text-decoration-underline"
                                                        href="{{ route('admin.application.show', $application->uuid) }}">
                                                        {{ $application->application_id ?? '' }} </a></td>

                                                <td>{{ $application->created_at->format('d-M-Y') }}</td>
                                                <td><span
                                                        class="badge badge-rounded badge-primary">{{ ucFirst($application->status ?? '') }}</span>
                                                </td>

                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="p-4 text-center">No Data Available !</td>
                                            </tr>
                                        @endforelse

                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-6 px-1">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Application Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="applicationStatusChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-6 px-1">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Weekly Applications</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="weeklyApplicationChart" height="250"></canvas>
                        </div>
                    </div>
                </div>


            </div>


        </div>
    </div>

@endsection

@section('javascripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @include('admin.cms.charts.statuswise-chart')
    @include('admin.cms.charts.weekly-chart')
    @include('admin.cms.charts.monthly-chart')
    <script>
        function updateClock() {
            const now = new Date();
            const clock = document.getElementById('liveClock');
            if (clock) {
                clock.textContent = now.toLocaleTimeString();
            }
        }

        setInterval(updateClock, 1000);
        updateClock(); // initial call
    </script>

@endsection
