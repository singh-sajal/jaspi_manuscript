<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to JASPI - Your Account Credentials</title>
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

        .credentials-box {
            background-color: #f1f5f9;
            border-left: 4px solid #1a5a96;
            padding: 15px;
            margin: 20px 0;
        }

        .credentials-box p {
            margin: 5px 0;
        }

        .password-box {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 12px 15px;
            margin: 20px 0;
            text-align: center;
        }

        .password-label {
            font-size: 0.9em;
            color: #856404;
            margin-bottom: 5px;
        }

        .password-value {
            font-size: 20px;
            font-weight: bold;
            color: #856404;
            letter-spacing: 2px;
            font-family: 'Courier New', Courier, monospace;
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

        .security-notice {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 12px 15px;
            margin: 20px 0;
            color: #721c24;
            font-size: 0.95em;
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
                            <h1>Welcome to JASPI</h1>
                        </td>
                    </tr>

                    <tr>
                        <td class="content">
                            <p>Dear Sir/Ma'am,</p>

                            <p>Welcome to the <strong>Journal of Antimicrobial Stewardship Practices and Infectious Diseases (JASPI)</strong>! Your account has been created and you now have access to the reviewer portal.</p>

                            <p>Please find your login credentials below:</p>

                            <div class="credentials-box">
                                <p><strong>Login URL:</strong> <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>
                                <p><strong>Email:</strong> {{ $staff->email }}</p>
                                <p><strong>Username:</strong> {{ $staff->username }}</p>
                            </div>

                            <div class="password-box">
                                <div class="password-label">Your Password</div>
                                <div class="password-value">{{ $password }}</div>
                            </div>

                            <div class="btn-container">
                                <a href="{{ $loginUrl }}" class="btn">Log In to Your Account</a>
                            </div>

                            <p style="text-align: center; margin-top: -5px;">If you have forgotten your password, <a href="{{ $loginUrl }}">click here to reset it</a>.</p>

                            <div class="security-notice">
                                <strong>Important:</strong> For your account security, please change your password immediately after your first login. You can do this from your profile settings after logging in.
                            </div>

                            <p>If you have any questions or need assistance, please contact the editorial office at <a href="mailto:jaspi@saspi.in">jaspi@saspi.in</a>.</p>

                            <p>We look forward to your valuable contribution as a reviewer.</p>

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
                            You received this email because an account was created for you on the JASPI reviewer portal.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
