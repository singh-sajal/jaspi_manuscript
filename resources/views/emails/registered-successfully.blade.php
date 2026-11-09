<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - JASPI</title>
    <style>
        body {
            margin: 0;
            padding: 10px;
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
        }

        .container {
            width: 100%;
            max-width: 600px;
            background-color: #ffffff;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            overflow: hidden;
        }

        .header {
            background-color: #28a745;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 22px;
        }

        .content {
            padding: 30px;
            text-align: center;
        }

        .icon {
            font-size: 60px;
            margin-bottom: 15px;
        }

        .content h2 {
            color: #28a745;
            margin-top: 0;
        }

        .content p {
            font-size: 15px;
            color: #555;
            margin: 10px 0;
        }

        .highlight {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
            font-size: 14px;
            color: #856404;
        }

        .btn {
            display: inline-block;
            background-color: #1a5a96;
            color: #ffffff !important;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 15px;
        }

        .btn:hover {
            background-color: #15437a;
        }

        .footer {
            padding: 15px;
            font-size: 12px;
            color: #777777;
            background-color: #f8f9fa;
            border-top: 1px solid #eeeeee;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Registration Successful</h1>
        </div>

        <div class="content">
            <div class="icon">&#10004;</div>

            <h2>Thank You for Registering!</h2>

            <p>Your registration has been received successfully. Our team will review your information and process your application.</p>

            <div class="highlight">
                {{-- <strong>What happens next?</strong><br><br> --}}
                You will receive an acceptance email within <strong>24-48 hours</strong>. If it takes longer than expected, please feel free to contact the administration to know more about your application status.
            </div>

            <p>For any queries, reach out to us at <a href="mailto:jaspi@saspi.in">jaspi@saspi.in</a></p>

            <a href="{{ route('home') }}" class="btn">Go to Home</a>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} JASPI. All rights reserved.
        </div>
    </div>
</body>

</html>
