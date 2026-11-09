<!DOCTYPE html>
<html>
<head>
    <title>Final Decision on Your Manuscript</title>
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
        .decision-box {
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
        }
        .decision-Accepted {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .decision-Rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .decision-Revision {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
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
        <h2>Final Decision on Your Manuscript</h2>
    </div>

    <div class="content">
        <p>Dear Author,</p>

        <p>We would like to inform you about the final decision regarding your manuscript (ID: <strong>{{ $application->application_id }}</strong>).</p>

        <span class="decision-box decision-{{ $decision }}">
            {{ $decision }}
        </span>

        @isset($comment)
            <p><strong>Editorial Comments / Instructions:</strong></p>
            <div class="comment-box">
                {!! $comment !!}
            </div>
        @endisset

        @if ($decision == 'Accepted')
            <p>Congratulations! Your manuscript has been accepted for publication. Our technical team will contact you with further instructions regarding the upcoming steps.</p>
        @elseif ($decision == 'Rejected')
            <p>We regret to inform you that your manuscript has not been accepted for publication. We thank you for considering the Journal of Antimicrobial Stewardship Practices and Infectious Diseases (JASPI) as a potential venue for your work.</p>
        @elseif ($decision == 'Revision Required')
            <p>Please log in to your dashboard to review the comments, revise your manuscript accordingly, and submit the updated version at your earliest convenience.</p>
        @endif

        <a href="{{ $url }}" class="button">View Your Manuscript</a>
    </div>

    <div class="footer">
        <p>Thank you,<br>
        {{ config('app.name') }} Editorial Team</p>
    </div>

</body>
</html>