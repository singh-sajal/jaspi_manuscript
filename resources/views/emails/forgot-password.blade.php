<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">

    <table align="center" cellpadding="0" cellspacing="0"
        style="width: 100%; max-width: 600px; background-color: #ffffff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
        <thead>
            <tr>
                <td style="background-color: #4d87d3; color: #ffffff; text-align: center; padding: 20px;">
                    <h1 style="margin: 0; font-size: 24px;">Password Reset Request</h1>
                </td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 20px; font-size: 16px; line-height: 1.5; color: #333;">
                    <p>Dear {{ $user->name }},</p>
                    <p>We received a request to reset your password.</p>
                    <p>If you made this request, you can change your password by clicking the link below:</p>
                    <p style="text-align: center; margin: 20px 0;">


                        <a href="{{ $url }}"
                            style="color: #ffffff; text-decoration: none; background-color: #4d87d3; padding: 10px 20px; border-radius: 5px; display: inline-block; font-size: 16px;">
                            Reset Password
                        </a>

                    </p>
                    <p>If you did not request a password reset, please ignore this email or contact support if you have
                        concerns.</p>
                    <p>Thank you,<br>Manuscript</p>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td style="background-color: #f4f4f4; text-align: center; padding: 10px; font-size: 12px; color: #666;">
                    <p style="margin: 0;">&copy; {{ date('Y') }} Manuscript. All rights reserved.</p>
                </td>
            </tr>
        </tfoot>
    </table>

</body>

</html>
