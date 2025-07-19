<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mail View</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .mail-container {
            max-width: 70%;
            margin: 50px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            padding: 30px 40px;
        }

        .mail-header {
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }

        .subject {
            font-size: 1.7rem;
            font-weight: 600;
            color: #343a40;
        }

        .sender-details {
            margin-top: 10px;
            font-size: 0.95rem;
            color: #6c757d;
        }

        .sender-name {
            font-weight: 500;
            color: #212529;
        }

        .message-body {
            margin-top: 30px;
            font-size: 1.05rem;
            line-height: 1.6;
            color: #495057;
            white-space: pre-wrap;
        }

        .mail-footer {
            margin-top: 40px;
            text-align: right;
        }

        .action-btn {
            border-radius: 30px;
            padding: 8px 22px;
        }
    </style>
</head>
<body>

    <div class="mail-container">
        <div class="mail-header">
            <div class="subject">{{ $data['subject'] }}</div>
            <div class="sender-details">
                <span class="sender-name">{{ $data['sender_name'] ?? 'Admin' }}</span>
                &nbsp;&lt;{{ $data['sender_email'] ?? 'admin@example.com' }}&gt;
            </div>
        </div>

        <div class="message-body">
            {!! nl2br(e($data['message'])) !!}
        </div>

        <div class="mail-footer">
            <button class="btn btn-outline-primary action-btn">Reply</button>
            <button class="btn btn-outline-secondary action-btn">Forward</button>
        </div>
    </div>

</body>
</html>
