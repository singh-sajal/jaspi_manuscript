<!DOCTYPE html>
<html>

<head>
    <title>Action Required: Manuscript Awaiting Your Response</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: center;
            border-bottom: 2px solid #c45a11;
        }

        .content {
            padding: 20px 0;
        }

        .meta {
            background-color: #f1f1f1;
            padding: 15px;
            margin: 20px 0;
        }

        .meta table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td {
            padding: 4px 0;
            vertical-align: top;
            font-size: 0.95em;
        }

        .meta td:first-child {
            width: 34%;
            color: #777777;
        }

        .overdue {
            color: #c45a11;
            font-weight: bold;
        }

        .comment-box {
            background-color: #f1f1f1;
            padding: 15px;
            border-left: 4px solid #c45a11;
            margin: 20px 0;
            font-style: italic;
            white-space: pre-line;
        }

        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #777777;
            text-align: center;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #c45a11;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>Action Required: Manuscript Awaiting Your Response</h2>
    </div>

    <div class="content">
        <p>Hello {{ $authorName ?: 'there' }},</p>

        <p>
            The following manuscript is still awaiting a response from you, and has been
            pending for some time. Please review it and submit your action so that the
            editorial process can continue.
        </p>

        <div class="meta">
            <table>
                <tr>
                    <td>Manuscript ID</td>
                    <td><strong>{{ $application->application_id ?? '---' }}</strong></td>
                </tr>
                <tr>
                    <td>Title</td>
                    <td>{{ $application->title ?? '---' }}</td>
                </tr>
                <tr>
                    <td>Current status</td>
                    <td><strong>{{ \Illuminate\Support\Str::ucfirst(str_replace('_', ' ', (string) $application->status)) }}</strong></td>
                </tr>
                @if (!is_null($waitingDays))
                    <tr>
                        <td>Waiting for</td>
                        <td><span class="overdue">{{ $waitingDays }} day(s)</span></td>
                    </tr>
                @endif
            </table>
        </div>

        @if (!empty($body))
            <p><strong>Message from the editorial team:</strong></p>
            <div class="comment-box">{!! $body !!}</div>
        @else
            <p>
                @if ($application->status === 'proofreading')
                    The manuscript has been sent to you for proofreading. Please review the
                    formatted proof, apply any necessary corrections, and upload the
                    proofread file.
                @else
                    A revision has been requested on this manuscript. Please make the
                    required changes and re-submit the revised manuscript along with a
                    point-by-point response.
                @endif
            </p>
        @endif

        <p>Please log in to your dashboard to review the manuscript and submit your action.</p>

        <a href="{{ $url }}" class="button">Go to Dashboard</a>
    </div>

    <div class="footer">
        <p>Thank you,<br>
            {{ config('app.name') }} Editorial Team</p>
    </div>

</body>

</html>
