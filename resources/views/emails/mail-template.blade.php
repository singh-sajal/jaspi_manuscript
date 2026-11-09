<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mail View</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            background-color: #f4f6f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .mail-container {
            max-width: 600px; /* Using fixed px is safer for email clients than % */
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            padding: 30px;
        }

        .mail-header {
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        /* Table used for guaranteed alignment in Outlook/Gmail */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        .meta-table th {
            text-align: left;
            color: #6c757d;
            font-weight: 600;
            padding: 4px 15px 4px 0;
            width: 80px;
            vertical-align: top;
        }

        .meta-table td {
            color: #212529;
            padding: 4px 0;
        }

        .subject-row td {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            padding-top: 10px;
        }

        .subject-row th {
            padding-top: 10px;
        }

        .message-body {
            font-size: 1.05rem;
            line-height: 1.6;
            color: #334155;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>

    <div class="mail-container">
        <div class="mail-header">
            <table class="meta-table">
                <tr>
                    <th>From:</th>
                    <td><strong>{{ $data['sender_name'] ?? 'System' }}</strong> &lt;{{ $data['sender_email'] ?? '' }}&gt;</td>
                </tr>
                <tr>
                    <th>To:</th>
                    <td>{{ $data['to_email'] ?? 'Recipient' }}</td>
                </tr>
                <tr>
                    <th>Date:</th>
                    <td>{{ $data['date'] ?? '' }}</td>
                </tr>
                <tr class="subject-row">
                    <th>Subject:</th>
                    <td>{{ $data['subject'] ?? 'No Subject' }}</td>
                </tr>
            </table>
        </div>

        <div class="message-body">
            {!! nl2br(e($data['message'] ?? '')) !!}
        </div>
    </div>

</body>
</html>
