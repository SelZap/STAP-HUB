<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Report Received</title>
    <style>
        body { margin: 0; padding: 0; background: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #0f172a; }
        .wrap { max-width: 580px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(15,23,42,.10); }
        .header { background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); padding: 32px 36px; }
        .header-title { color: #fff; font-size: 20px; font-weight: 800; margin: 0 0 4px; }
        .header-sub { color: rgba(255,255,255,.7); font-size: 13px; margin: 0; }
        .body { padding: 32px 36px; }
        .greeting { font-size: 15px; margin-bottom: 16px; line-height: 1.6; }
        .section-title { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: #64748b; margin-bottom: 12px; margin-top: 24px; }
        .detail-grid { display: table; width: 100%; border-collapse: collapse; }
        .detail-row { display: table-row; }
        .detail-label { display: table-cell; font-size: 12px; color: #64748b; padding: 7px 12px 7px 0; width: 38%; vertical-align: top; }
        .detail-value { display: table-cell; font-size: 13px; font-weight: 600; color: #0f172a; padding: 7px 0; border-bottom: 1px solid #f1f5f9; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .note { background: #f8fafc; border-left: 3px solid #0f172a; border-radius: 0 8px 8px 0; padding: 14px 16px; font-size: 13px; line-height: 1.6; color: #475569; margin-top: 24px; }
        .footer { background: #f8fafc; padding: 20px 36px; border-top: 1px solid #e2e8f0; text-align: center; }
        .footer p { font-size: 11px; color: #94a3b8; margin: 4px 0; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <p class="header-title">STAP Hub</p>
        <p class="header-sub">Smart Traffic Automation Program — Marikina City</p>
    </div>
    <div class="body">
        <p class="greeting">
            Dear <strong>{{ $report->reporting_party_name }}</strong>,<br>
            We have received your incident report. Here is a summary of the information you submitted.
        </p>

        <div class="section-title">Report Details</div>
        <div class="detail-grid">
            <div class="detail-row">
                <div class="detail-label">Reference No.</div>
                <div class="detail-value">#{{ str_pad($report->incident_id, 5, '0', STR_PAD_LEFT) }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Incident Date</div>
                <div class="detail-value">{{ \Carbon\Carbon::parse($report->incident_date)->format('F j, Y') }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Incident Time</div>
                <div class="detail-value">{{ \Carbon\Carbon::createFromFormat('H:i', $report->incident_time)->format('g:i A') }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Location</div>
                <div class="detail-value">{{ $report->location_description }}</div>
            </div>
            @if ($report->vehicle_type)
            <div class="detail-row">
                <div class="detail-label">Vehicle Type(s)</div>
                <div class="detail-value">{{ ucwords(str_replace(['_', ','], [' ', ', '], $report->vehicle_type)) }}</div>
            </div>
            @endif
            <div class="detail-row">
                <div class="detail-label">Injuries Reported</div>
                <div class="detail-value">{{ $report->people_hurt ? 'Yes (' . $report->injured_count . ' injured)' : 'None reported' }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Status</div>
                <div class="detail-value"><span class="badge badge-pending">{{ ucfirst($report->status) }}</span></div>
            </div>
        </div>

        <div class="note">
            Our team will review your report and may contact you at this email address
            if additional information is needed. Please keep this reference number
            <strong>#{{ str_pad($report->incident_id, 5, '0', STR_PAD_LEFT) }}</strong> for your records.
        </div>
    </div>
    <div class="footer">
        <p><strong>STAP Hub</strong> — Mayor Gil Fernando Avenue / Sumulong Highway, Marikina City</p>
        <p>This is an automated message. Please do not reply directly to this email.</p>
    </div>
</div>
</body>
</html>