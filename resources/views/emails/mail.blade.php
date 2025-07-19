<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CA of India</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .email-header-top {
            border-top: 2px solid #387ad1;
        }

        .email-header {
            background-color: #639ae2;
            color: #ffffff;
            text-align: left;
            padding: 10px;
            border-top: 2px solid #387ad1;
        }

        .email-header h4 {
            margin: 0;

        }

        .email-header img {
            max-width: 150px;
        }

        .email-body {
            padding: 20px;
            color: #333333;
            font-size: 16px;
            line-height: 0.85;
        }

        .email-cta {
            text-align: center;
            margin: 20px 0;
        }

        .email-cta a {
            background-color: #2c3034;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            display: inline-block;
        }

        .email-footer {
            background-color: #f8f9fa;
            text-align: center;
            padding: 15px;
            color: #6c757d;
            font-size: 14px;
            border-bottom: 2px solid #6c757d;
        }

        .email-footer a {
            color: #007bff;
            text-decoration: none;
        }

        /* Responsive Design */
        @media only screen and (max-width: 600px) {
            .email-body {
                font-size: 14px;
            }

            .email-cta a {
                font-size: 14px;
                padding: 10px 15px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header-top"></div>
        {{-- @if (isset($email_header)) --}}
        <div class="email-header">
            <h4>{{ $subject ?? 'Email subject' }}</h4>
        </div>
        {{-- @endif --}}
        <!-- Body -->
        <div class="email-body">
            {!! $email_body ?? '' !!}
            @if (isset($action_link))
                <div class="email-cta">
                    <a href="{{ $action_link }}">View Details</a>
                </div>
            @endif

        </div>
        <!-- Footer -->
        <div class="email-footer">
            &copy; {{ now()->format('Y') }} CAOI. All rights reserved.<br>

            Need help? <a href="#">Contact Support</a>.

        </div>
    </div>
</body>

</html>
