<!DOCTYPE html>
<html>
<head>
    <title>Formatted Proof Available</title>
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
            border-bottom: 2px solid #007bff;
        }
        .content {
            padding: 20px 0;
        }
        .comment-box {
            background-color: #f1f1f1;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
            font-style: italic;
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
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>Your Formatted Proof Is Ready</h2>
    </div>

    <div class="content">
        <p>Hello {{ $application->author->name ?? 'there' }},</p>

        <p>Your manuscript (ID: <strong>{{ $application->application_id }}</strong>) has been formatted by the publisher, and the proof is now available for review.</p>

        @isset($comment)
        <p><strong>Publisher's Comment:</strong></p>
        <div class="comment-box">
            {!! $comment !!}
        </div>
        @endisset

        <p>Please log in to your dashboard to review the formatted proof.</p>

        <a href="{{ $url }}" class="button">Go to Dashboard</a>
    </div>

    <div class="footer">
        <p>Thank you,<br>
        {{ config('app.name') }} Editorial Team</p>
    </div>

</body>
</html>
