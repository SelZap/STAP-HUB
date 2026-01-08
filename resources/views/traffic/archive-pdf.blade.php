<! DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Traffic Data Archive Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 3px solid #e63946;
            padding-bottom: 1rem;
        }

        .header h1 {
            font-size: 28px;
            color: #1a2238;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #5a6c7d;
            font-size: 12px;
        }

        . meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            font-size:  11px;
            color: #5a6c7d;
        }

        .chart-section {
            margin-bottom: 2rem;
            page-break-inside: avoid;
        }

        .chart-section h2 {
            font-size: 16px;
            color: #1a2238;
            margin-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        table thead {
            background:  #f8f9fa;
        }

        table th {
            padding: 0.75rem;
            text-align: left;
            font-weight: 700;
            color: #1a2238;
            border:  1px solid #dee2e6;
            font-size: 11px;
        }

        table td {
            padding: 0.75rem;
            border: 1px solid #dee2e6;
            font-size: 11px;
            color: #5a6c7d;
        }

        table tbody tr:nth-child(even) {
            background:  #f8f9fa;
        }

        . badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius:  4px;
            font-size:  10px;
            font-weight:  600;
        }

        .badge-light {
            background: #d4edda;
            color: #155724;
        }

        . badge-medium {
            background:  #fff3cd;
            color: #856404;
        }

        .badge-heavy {
            background: #f8d7da;
            color: #721c24;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        . summary-box {
            border: 1px solid #dee2e6;
            padding: 1rem;
            text-align: center;
            border-radius: 4px;
            background: #f8f9fa;
        }

        .summary-box . label {
            font-size: 11px;
            color: #5a6c7d;
            margin-bottom: 0.5rem;
        }

        . summary-box .value {
            font-size: 24px;
            font-weight:  700;
            color: #1a2238;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Traffic Data Archive Report</h1>
        <p>STAP Hub - Smart Traffic Automation Program</p>
    </div>

    <div class="meta">
        <span>Report Generated: {{ $generatedAt->format('M d, Y - H:i A') }}</span>
        <span>Total Records: {{ count($trafficData) }}</span>
    </div>

    <!-- Summary Stats -->
    @php
        $lightCount = $trafficData->where('level_of_service', 'light')->count();
        $mediumCount = $trafficData->where('level_of_service', 'medium')->count();
        $heavyCount = $trafficData->where('level_of_service', 'heavy')->count();
    @endphp

    <div class="summary">
        <div class="summary-box">
            <div class="label">Light Traffic</div>
            <div class="value" style="color: #28a745;">{{ $lightCount }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Medium Traffic</div>
            <div class="value" style="color: #ffc107;">{{ $mediumCount }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Heavy Traffic</div>
            <div class="value" style="color: #dc3545;">{{ $heavyCount }}</div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="chart-section">
        <h2>Traffic Trends Overview</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Light</th>
                    <th>Medium</th>
                    <th>Heavy</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chartData as $data)
                    <tr>
                        <td>{{ $data['date'] }}</td>
                        <td>{{ $data['light'] }}</td>
                        <td>{{ $data['medium'] }}</td>
                        <td>{{ $data['heavy'] }}</td>
                        <td><strong>{{ $data['total'] }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="page-break"></div>

    <!-- Data Table -->
    <div class="chart-section">
        <h2>Detailed Traffic Records</h2>
        
        @if($trafficData->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Road</th>
                        <th>Level of Service</th>
                        <th>Weather</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trafficData as $record)
                        <tr>
                            <td>{{ $record->date->format('M d, Y') }}</td>
                            <td>{{ $record->time }}</td>
                            <td>{{ $record->road }}</td>
                            <td>
                                <span class="badge badge-{{ $record->level_of_service }}">
                                    {{ ucfirst($record->level_of_service) }}
                                </span>
                            </td>
                            <td>{{ $record->weather }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No traffic data available.</p>
        @endif
    </div>
</body>
</html>