<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invitation to Join as a JASPI Peer Reviewer</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8f9fa;
            color: #212529;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background-color: #ffffff;
            border: 1px solid rgba(0, 0, 0, .125);
            border-radius: 0.375rem;
            overflow: hidden;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, .075);
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, .125);
            padding: 1rem 1.25rem;
            text-align: center;
        }

        .card-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #0d6efd;
        }

        .card-body {
            padding: 1.5rem;
        }

        .btn-primary {
            display: inline-block;
            font-weight: 500;
            line-height: 1.5;
            color: #ffffff;
            text-align: center;
            text-decoration: none;
            background-color: #0d6efd;
            border: 1px solid #0d6efd;
            padding: 0.5rem 1.25rem;
            font-size: 1rem;
            border-radius: 0.375rem;
            margin: 20px 0;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
        }

        .text-muted {
            color: #6c757d;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.875rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            {{-- <div class="card-header">
                <h2 class="card-title">Invitation to Join as a JASPI Peer Reviewer</h2>
            </div> --}}

            <div class="card-body">
                <p>Respected Sir/Madam,</p>

                <p>We are pleased to invite you to join the <strong>Journal of Antimicrobial Stewardship Practices and
                        Infectious Diseases (JASPI)</strong> as a Peer Reviewer.</p>

                <p>JASPI is a peer-reviewed scholarly journal dedicated to promoting evidence-based practices in
                    antimicrobial stewardship. For more information, please visit: <a href="https://jaspi.saspi.in"
                        style="color: #0d6efd; text-decoration: none;">https://jaspi.saspi.in</a>.</p>

                <p>As a reviewer, you will have the opportunity to evaluate submitted manuscripts, provide valuable
                    feedback to authors, and contribute to maintaining the quality and integrity of published research.
                </p>

                <div style="text-align: center; color: #ffffff;">
                    <a href="{{ $url }}" class="btn-primary" style="color: #ffffff;">Accept Invitation &
                        Register</a>
                </div>


                <p>If the button above does not work, please copy and paste the following link into your web browser:
                </p>
                <p style="word-break: break-all; font-size: 0.9em; color: #0d6efd;">
                    {{ $url }}
                </p>

                <p>Thank you for your valuable contribution to the scientific community. We look forward to your
                    participation as a JASPI Peer Reviewer.
                </p>

                <p>
                    Published by:
                    <strong>Society of Antimicrobial Stewardship PractIces (SASPI) in India </strong><br>
                    Web: <a href="https://saspi.in" style="color: #6c757d; text-decoration: none;">https://saspi.in</a>
                </p>

                <p>With regards,<br>
                    <strong>JASPI Editorial Office</strong>
                </p>
            </div>
        </div>

        <div class="footer text-muted">
            {{-- <p>
                Published by: <strong>Society of Antimicrobial Stewardship PractIces in India (SASPI)</strong><br>
                Web: <a href="https://saspi.in" style="color: #6c757d; text-decoration: none;">https://saspi.in</a>
            </p>
            <p>&copy; {{ date('Y') }} JASPI. All rights reserved.</p> --}}
        </div>
    </div>
</body>

</html>
