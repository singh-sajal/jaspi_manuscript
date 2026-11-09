@extends('author.app')

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
    @if (auth()->check() && !session('cookie_consent_shown'))
        <!-- Cookie Consent Modal -->
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
                        <form method="POST" action="{{ route('author.cookie.consent') }}" class="m-0">
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
        $widgets = ['bg-primary', 'bg-warning', 'bg-dark', 'bg-success', 'bg-danger', 'bg-info'];
        $widgetIcons = [
            'ri-folder-chart-line',
            'ri-edit-line',
            'ri-send-plane-line',
            'ri-eye-line',
            'ri-close-circle-line',
            'ri-check-double-line',
        ];
    @endphp
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between gap-2">
                @foreach ($counts as $key => $count)
                    @php
                        $widgetClassIndex = $loop->index % 6;
                        $widgetClass = $widgets[$widgetClassIndex];
                        $icon = $widgetIcons[$widgetClassIndex];

                        // Calculate percentage safely
                        $totalApps =
                            isset($counts['Total Applications']) && $counts['Total Applications'] > 0
                                ? $counts['Total Applications']
                                : 1;
                        $progressPercent = ($count / $totalApps) * 100;

                        // Your type array
                        $type = ['', 'incomplete', 'submitted', 'revised', 'rejected', 'published'];

                        // --- LOGIC CHANGE HERE ---
                        // 1. Get the current type string based on the loop index
                        $currentType = $type[$loop->index] ?? '';

                        // 2. If $currentType is empty (the first one), send empty array [].
                        //    Otherwise, send ['type' => 'value']
                        $routeParams = empty($currentType) ? [] : ['type' => $currentType];
                    @endphp

                    <div class="widget-stat card flex-grow-1 {{ $widgetClass }} text-white">
                        {{-- 3. Use the $routeParams variable here --}}
                        <a href="{{ route('author.application.index', $routeParams) }}">
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
                                <small class="text text-light">Manuscripts</small>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="row">
                <div class="col-4 px-1">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Recent Manuscripts</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive recentOrderTable">
                                <table class="verticle-middle  table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Title</th>
                                            <th scope="col">Updated On</th>
                                            <th scope="col">Status</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentApplications as $application)
                                            <tr>
                                                {{-- <td> <a class="text-info text-decoration-underline"
                                                        href="{{ route('author.application.show', $application->uuid) }}">
                                                        {{ $application->title ?? '' }} </a></td> --}}

                                                <td>
                                                    @php
                                                        if (
                                                            $application->status == 'incomplete' ||
                                                            $application->status == 'revise'
                                                        ) {
                                                            // $route = route('author.application.create', [
                                                            //     'applicationId' => $application->uuid,
                                                            //     'step' => 'preview',
                                                            // ]);
                                                            $route = route(
                                                                'author.application.show',
                                                                $application->uuid,
                                                            );
                                                        } else {
                                                            $route = route(
                                                                'author.application.show',
                                                                $application->uuid,
                                                            );
                                                        }

                                                    @endphp
                                                    <a class="text-info text-decoration-underline"
                                                        href="{{ $route }}">
                                                        {{ \Illuminate\Support\Str::limit($application->title, 10) }}
                                                    </a>
                                                </td>

                                                <td>{{ $application->updated_at->format('d-M-Y') }}</td>

                                                <td>
                                                    @if (in_array($application->status, [
                                                            'editor-accepts',
                                                            'editor-rejects',
                                                            'editor-revise',
                                                            'chief-approval',
                                                            'chief-review',
                                                            'chief-response',
                                                            'assigning reviewers',
                                                            'reviewed',
                                                        ]))
                                                        {{-- <span class="badge badge-sm bg-dark">Processing</span> --}}
                                                        <a href="{{ route('author.application.show', $application->uuid) }}"
                                                            class="badge badge-rounded badge-primary">Processing</a>
                                                    @else
                                                        {{-- <span class="badge badge-sm bg-dark">{{ ucfirst($application->status) }}</span> --}}
                                                        <a href="{{ route('author.application.show', $application->uuid) }}"
                                                            class="badge badge-rounded badge-primary">{{ ucfirst($application->status) ?? '' }}</a>
                                                    @endif
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
                <div class="col-4 px-1">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Manuscript Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="applicationStatusChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-4 px-1">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Weekly Manuscripts</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="weeklyApplicationChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Monthly Manuscripts</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyApplicationChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

@endsection

@section('javascripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @include('author.cms.charts.statuswise-chart')
    @include('author.cms.charts.weekly-chart')
    @include('author.cms.charts.monthly-chart')
@endsection
