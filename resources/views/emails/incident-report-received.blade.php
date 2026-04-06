<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Report Received</title>
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            background-color: #f4f6fb;
            margin: 0;
            padding: 0;
            color: #1B2744;
        }
        .wrapper {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(27,39,68,0.08);
        }
        .header {
            background-color: #1B2744;
            padding: 32px 40px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 22px;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .header span {
            color: #29B357;
            font-weight: 700;
        }
        .body {
            padding: 36px 40px;
        }
        .body p {
            font-size: 15px;
            line-height: 1.7;
            margin: 0 0 16px;
            color: #374151;
        }
        .highlight-box {
            background: #f0fdf4;
            border-left: 4px solid #29B357;
            border-radius: 6px;
            padding: 16px 20px;
            margin: 24px 0;
        }
        .highlight-box p {
            margin: 0;
            font-size: 14px;
            color: #1B2744;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 24px 0;
            font-size: 14px;
        }
        .details-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .details-table td:first-child {
            font-weight: 600;
            color: #1B2744;
            width: 40%;
        }
        .details-table td:last-child {
            color: #374151;
        }
        .footer {
            background: #f4f6fb;
            padding: 24px 40px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
        .footer strong {
            color: #1B2744;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>STAP <span>Hub</span></h1>
        </div>

        <div class="body">
            <p>Dear <strong>{{ $report->reporting_party_name }}</strong>,</p>

            <p>Thank you for your report! We will be sure to respond as soon as possible to your report.</p>

            <div class="highlight-box">
                <p>Your incident report has been successfully received and is currently under review by our traffic management team.</p>
            </div>

            <p>Here is a summary of what you submitted:</p>

            <table class="details-table">
                <tr>
                    <td>Report Reference</td>
                    <td>#{{ $report->incident_id }}</td>
                </tr>
                <tr>
                    <td>Incident Date</td>
                    <td>{{ \Carbon\Carbon::parse($report->incident_date)->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td>Incident Time</td>
                    <td>{{ \Carbon\Carbon::parse($report->incident_time)->format('h:i A') }}</td>
                </tr>
                <tr>
                    <td>Location</td>
                    <td>{{ $report->location_description }}</td>
                </tr>
                <tr>
                    <td>Submitted By</td>
                    <td>{{ $report->reporting_party_name }}</td>
                </tr>
            </table>

            <p>If you have additional information to add or have any questions, please do not hesitate to reach out to us.</p>

            <p>Thank you for helping us keep the community safe.</p>

            <p>
                Regards,<br>
                <strong>STAP Hub Traffic Management Team</strong>
            </p>
        </div>

        <div class="footer">
            <p>This is an automated message from <strong>STAP Hub</strong>.<br>
            Mayor Gil Fernando Avenue &amp; Sumulong Highway, Quezon City</p>
        </div>
    </div>
</body>
</html>