@extends('admin.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb-button')
    @php
        use App\Models\Admin;
        $verify = Admin::where('rev_type', 'external')->where('status', 0)->count();

    @endphp

    @if (isSuperAdmin())
        @if ($verify == 0)
            <div class="alert alert-success text-center fw-bold">
                No verification pending.
            </div>
        @elseif ($verify == 1)
            <div class="alert alert-warning text-center fw-bold">
                Please verify {{ $verify }} External Reviewer —
                <a href="{{ route('admin.staff.index') }}" class="alert-link">Go to Staff</a>
            </div>
        @else
            <div class="alert alert-danger text-center fw-bold">
                Please verify {{ $verify }} External Reviewers —
                <a href="{{ route('admin.staff.index') }}" class="alert-link">Go to Staff</a>
            </div>
        @endif
    @endif



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

    @if (auth()->check() && !session('cookie_consent_shown') && isReviewer())
        <!-- Cookie + Ethics Consent Modal -->
        <div class="modal fade" id="cookieConsentModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content p-4">
                    <div class="modal-header">
                        <h4 class="modal-title fw-bold">
                            <i class="bi bi-shield-lock-fill me-2"></i> Consent Required
                        </h4>
                    </div>

                    <div class="modal-body">
                        <p class="mb-3">
                            We use cookies to ensure you get the best experience. By continuing, you agree to our cookie
                            policy.
                        </p>
                        <ul class="mb-3">
                            <li>Remember your login session securely.</li>
                            <li>Improve performance and speed.</li>
                            <li>Store your preferences and settings.</li>
                            <li>Provide personalized content where applicable.</li>
                            <li>Analyze site usage for improvements.</li>
                        </ul>
                        <p class="text-muted">
                            By clicking <strong>Accept</strong>, you agree to our use of cookies.
                            {{-- in line with our --}}
                            {{-- <a href="#" class="text-decoration-underline">Privacy Policy</a>. --}}
                        </p>

                        <div class="alert alert-warning rounded-3 p-3 mt-4">
                            <h5 class="fw-bold text-danger mb-2">
                                ⚠️ Important Ethical Reminder
                            </h5>
                            <p class="mb-2">
                                Let’s practice and be a <strong>HARD FACTOR</strong> while doing research and publication in
                                infection prevention,
                                source control, right diagnosis, and right antimicrobial utilization with respect to:
                            </p>
                            <ul>
                                <li><strong>H</strong>onesty</li>
                                <li><strong>A</strong>uthorship</li>
                                <li><strong>R</strong>espect & Review</li>
                                <li><strong>D</strong>ata analysis & sharing</li>
                                <li><strong>F</strong>airness</li>
                                <li><strong>A</strong>ccuracy</li>
                                <li><strong>C</strong>onflicts of interest</li>
                                <li><strong>T</strong>ransparency</li>
                                <li><strong>O</strong>riginality</li>
                                <li><strong>R</strong>etractions & corrections</li>
                            </ul>
                            <p class="mb-2">
                                These principles must be followed carefully in relation to the drug, its dose, delivery,
                                duration,
                                and decision on follow-up.
                            </p>
                            <p class="mb-0">
                                In addition, all reviewers and authors are required to go through and practice the mandatory
                                policies of JASPI:
                                <a href="https://jaspi.saspi.in/publication-ethics/" target="_blank">Publication Ethics</a>,
                                <a href="https://jaspi.saspi.in/editorial-policy/" target="_blank">Editorial Policy</a>, and
                                <a href="https://jaspi.saspi.in/peer-review/" target="_blank">Peer Review Policy</a>.
                            </p>
                        </div>
                    </div>

                    <div class="modal-footer border-top-0">
                        <form method="POST" action="{{ route('admin.cookie.consent') }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-primary px-4">Accept</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let modal = new bootstrap.Modal(document.getElementById('cookieConsentModal'));
                modal.show();
            });
        </script>
    @endif






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
                                <div class="progress-bar progress-animated bg-light"
                                    style="width: {{ $progressPercent }}%">
                                </div>
                            </div>
                            <small>Manuscripts</small>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row">
                <div class="col-8">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Monthly Manuscripts</h4>
                            <select id="monthlyChartYear" class="form-select form-select-sm" style="width:auto;">
                                @foreach ($years as $yr)
                                    <option value="{{ $yr }}"
                                        {{ $yr == $charts['monthlyApplicationChart']['year'] ? 'selected' : '' }}>
                                        {{ $yr }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyApplicationChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-4 px-1">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Recent Manuscripts</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive recentOrderTable">
                                <table class="verticle-middle table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Manuscript No.</th>
                                            <th scope="col">Updated On</th>
                                            <th scope="col">Status</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentApplications as $application)
                                            <tr>
                                                <td> <a class="text-info text-decoration-underline"
                                                        href="{{ route('admin.application.show', $application->uuid) }}">
                                                        {{ $application->application_id ?? '' }} </a></td>

                                                <td>{{ $application->updated_at->format('d-M-Y') }}</td>
                                                <td><a href="{{ route('admin.application.show', $application->uuid) }}"
                                                        class="badge badge-rounded badge-primary">{{ ucFirst($application->status ?? '') }}</a>
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
                            <h4 class="card-title">Manuscript Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="applicationStatusChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-6 px-1">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Weekly Manuscripts</h4>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" id="weeklyChartPrev" class="btn btn-sm btn-outline-secondary">
                                    <i class="ri-arrow-left-s-line"></i>
                                </button>
                                <small id="weeklyChartRange" class="text-muted">
                                    {{ $charts['lastSevenDaysApplicationChart']['rangeLabel'] ?? '' }}
                                </small>
                                <button type="button" id="weeklyChartNext" class="btn btn-sm btn-outline-secondary">
                                    <i class="ri-arrow-right-s-line"></i>
                                </button>
                            </div>
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
