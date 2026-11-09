<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation to Review Manuscript</title>
    <style>
        /* Basic reset for email clients */
        body {
            margin: 0;
            padding: 10px;
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
        }
        table {
            border-spacing: 0;
        }
        td {
            padding: 0;
        }
        img {
            border: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #e1e1e1;
        }
        .header {
            background-color: #1a5a96;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 20px;
        }
        .content {
            padding: 30px;
        }
        .manuscript-box {
            background-color: #f1f5f9;
            border-left: 4px solid #1a5a96;
            padding: 15px;
            margin: 20px 0;
        }
        .btn-container {
            text-align: center;
            padding: 30px 0;
        }
        .btn {
            background-color: #28a745;
            color: #ffffff !important;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
        }
        .footer {
            padding: 20px;
            font-size: 12px;
            color: #777777;
            background-color: #f8f9fa;
            border-top: 1px solid #eeeeee;
        }
        .decline-link {
            color: #dc3545;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table class="container" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="header">
                            <h1>JASPI Journal Invitation</h1>
                        </td>
                    </tr>

                    <tr>
                        <td class="content">
                            <p>Dear Dr. {{ $reviewer->name }},</p>
                            
                            <p>Greetings from the <strong>Journal of Antimicrobial Stewardship Practices and Infectious Diseases (JASPI)</strong>.</p>

                            <p>We would like to invite you to review the following manuscript submitted to JASPI:</p>

                            <div class="manuscript-box">
                                <strong>Manuscript Title:</strong> {{ $application->title ?? '' }}<br>
                                <strong>Manuscript ID:</strong> {{ $application->application_id ?? '' }}<br>
                                @isset($dueAt)
                                <strong>Review Deadline:</strong> {{ \Illuminate\Support\Carbon::parse($dueAt)->format('d M Y') }}<br>
                                @endisset
                                {{-- <strong>Review Type:</strong> {{ ucfirst($type) }} Reviewer --}}
                            </div>

                            @isset($dueAt)
                            <p>Kindly submit your review by <strong>{{ \Illuminate\Support\Carbon::parse($dueAt)->format('d M Y') }}</strong>. This is a {{ $profile === 'short' ? '7' : '14' }}-day review window, with reminders and a final deadline if we do not hear from you.</p>
                            @endisset

                            <p>The journal solicits manuscripts related to Infectious Diseases, Antimicrobial Resistance, Anti-infective Use and Monitoring, Hospital Infection Control, and Integrated Stewardship.</p>

                            <p>Considering your expertise in the field, we would be grateful if you could kindly review this manuscript.</p>

                            <div class="btn-container">
                                <a href="{{ $url }}" class="btn">Log in to Reviewer Portal</a>
                            </div>

                            <p>Please log in as a <strong>Reviewer</strong> using your registered email ID. After logging in, you will have the option to accept or decline the review invitation based on your availability.</p>

                            <p>If you have a conflict of interest or do not wish to review this manuscript, please click the link below to decline:</p>
                            <p><a href="https://docs.google.com/document/d/1ekoegVQfegMw3v92Z9KsuoKvxfTd9tFl/edit" class="decline-link">Click here to decline invitation</a></p>

                            <p>If you decline, we would sincerely appreciate your suggestions for alternate reviewers via return email.</p>

                            <p>Thank you for your time and valuable contribution.</p>

                            <p>Warm regards,<br>
                            <strong>Editorial Team</strong><br>
                            Journal of Antimicrobial Stewardship Practices and Infectious Diseases (JASPI)<br>
                            <a href="mailto:jaspi@saspi.in">jaspi@saspi.in</a></p>
                        </td>
                    </tr>

                    <tr>
                        <td class="footer" align="center">
                            &copy; {{ date('Y') }} JASPI. All rights reserved.<br>
                            You received this email because you are a registered reviewer for JASPI.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>