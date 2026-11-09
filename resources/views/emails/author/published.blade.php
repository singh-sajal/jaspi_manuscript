<!DOCTYPE html>
<html>
<head>
    <title>Your Manuscript is Published</title>
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
        .doi-box {
            background-color: #d4edda;
            padding: 10px 15px;
            border-left: 4px solid #28a745;
            margin: 20px 0;
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
        <h2>Congratulations, Your Manuscript is Published!</h2>
    </div>

    <div class="content">
        <p>Dear Author,</p>

        <p>We are pleased to inform you that your manuscript (ID: <strong>{{ $application->application_id }}</strong>) has been officially published by the Journal of Antimicrobial Stewardship Practices and Infectious Diseases (JASPI).</p>

        @isset($doi)
            <div class="doi-box">
                <strong>DOI:</strong> {{ $doi }}
            </div>
        @endisset

        <p>We sincerely thank you for choosing JASPI and for your valuable contribution to the scientific community.</p>

        <a href="{{ $url }}" class="button">View Your Manuscript</a>
    </div>

    <div class="footer">
        <p>Warm regards,<br>
        {{ config('app.name') }} Editorial Team</p>
    </div>

</body>
</html>