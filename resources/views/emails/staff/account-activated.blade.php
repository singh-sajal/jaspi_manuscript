<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Account Has Been Activated - JASPI</title>
    <style>
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

        .credentials-box {
            background-color: #f1f5f9;
            border-left: 4px solid #1a5a96;
            padding: 15px;
            margin: 20px 0;
        }

        .credentials-box p {
            margin: 5px 0;
        }

        .btn-container {
            text-align: center;
            padding: 20px 0;
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
            text-align: center;
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
                            <h1>Account Activated</h1>
                        </td>
                    </tr>

                    <tr>
                        <td class="content">
                            <p>Dear Sir/Ma'am,</p>

                            <p>We are pleased to inform you that your account has been <strong>activated</strong>. You can now access the JASPI reviewer panel using the credentials that were created during your registration.</p>

                            <div class="credentials-box">
                                <p><strong>Login URL:</strong> <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>
                                <p><strong>Email:</strong> {{ $staff->email }}</p>
                                <p><strong>Username:</strong> {{ $staff->username }}</p>
                            </div>

                            <div class="btn-container">
                                <a href="{{ $loginUrl }}" class="btn">Log In to Your Account</a>
                            </div>

                            <p style="text-align: center; margin-top: -5px;">If you have forgotten your password, <a href="{{ $loginUrl }}">click here to reset it</a>.</p>

                            {{-- <p>If you have forgotten your password or face any issues logging in, please contact the editorial office at <a href="mailto:jaspi@saspi.in">jaspi@saspi.in</a>.</p> --}}

                            <p>Warm regards,<br>
                                <strong>Editorial Team</strong><br>
                                Journal of Antimicrobial Stewardship Practices and Infectious Diseases (JASPI)<br>
                                <a href="mailto:jaspi@saspi.in">jaspi@saspi.in</a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td class="footer">
                            &copy; {{ date('Y') }} JASPI. All rights reserved.<br>
                            You received this email because your account was activated on the JASPI reviewer portal.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
