<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reminder: Pending Manuscript Review</title>
    <style>
        /* Base styles mimicking Bootstrap */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8f9fa;
            color: #212529;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .card {
            background-color: #ffffff;
            border: 1px solid rgba(0,0,0,.125);
            border-radius: 0.375rem;
            overflow: hidden;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,.125);
            padding: 1rem 1.25rem;
        }
        .card-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #0d6efd; /* Bootstrap primary blue */
        }
        .card-body {
            padding: 1.25rem;
        }
        .table {
            width: 100%;
            margin-bottom: 1.5rem;
            color: #212529;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
            text-align: left;
        }
        .btn-success {
            display: inline-block;
            font-weight: 500;
            line-height: 1.5;
            color: #ffffff;
            text-align: center;
            text-decoration: none;
            background-color: #198754;
            border: 1px solid #198754;
            padding: 0.5rem 1rem;
            font-size: 1rem;
            border-radius: 0.375rem;
            margin: 10px 0 20px 0;
        }
        .btn-success:hover {
            background-color: #157347;
        }
        .text-muted {
            color: #6c757d;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Action Required: Pending Manuscript Review</h2>
            </div>

            <div class="card-body">
                <p>Dear <strong>{{ $reviewer->name ?? 'Reviewer' }}</strong>,</p>

                <p>This is a gentle reminder regarding the manuscript recently assigned to you for a review. We highly value your expertise and are eagerly awaiting your evaluation.</p>

                @if (!empty($reminderNumber))
                    <p>
                        This is <strong>reminder #{{ $reminderNumber }}</strong>@if (!empty($deadline))
                            &mdash; please submit your review by
                            <strong>{{ \Illuminate\Support\Carbon::parse($deadline)->format('d-M-Y') }}</strong>@endif.
                    </p>
                @endif

                <table class="table">
                    <tbody>
                        <tr>
                            <th scope="row" style="width: 30%;">Manuscript ID</th>
                            <td><code>{{ $application->application_id ?? '' }}</code></td>
                        </tr>
                        <tr>
                            <th scope="row">Title</th>
                            <td>{{ $application->title ?? '' }}</td>
                        </tr>
                    </tbody>
                </table>

                <div style="text-align: center;">
                    <a href="{{ $url }}" class="btn-success">View Manuscript & Submit Review</a>
                </div>

                <p>If you require additional time, or if you are no longer able to complete this review, please let us know as soon as possible so we can update our records and keep the author informed.</p>

                <p>Thank you for your continued support and contribution.</p>

                <p>Best regards,<br>
                <strong>{{ config('app.name') }} Team</strong></p>
            </div>
        </div>

        <div class="footer text-muted">
            <p>This is an automated message from {{ config('app.name') }}. Please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>
