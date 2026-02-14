<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
        }
        .header p {
            margin: 5px 0;
            color: #7f8c8d;
        }
        .date-info {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #34495e;
            color: white;
            padding: 10px;
            text-align: left;
            border: 1px solid #2c3e50;
        }
        td {
            padding: 10px;
            border: 1px solid #ecf0f1;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #ecf0f1;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #95a5a6;
            font-size: 12px;
        }
        .summary {
            margin-top: 20px;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>AANI Market System</h1>
        <p>Vendor Attendance Report</p>
    </div>

    <div class="date-info">
        Market Date: {{ \Carbon\Carbon::parse($marketDate)->format('l, F d, Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Business Name</th>
                <th>Owner Name</th>
                <th>Check In</th>
                <th>Check Out</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendance as $record)
                <tr>
                    <td>{{ $record->business_name }}</td>
                    <td>{{ $record->owner_name }}</td>
                    <td>{{ \Carbon\Carbon::parse($record->check_in_time)->format('H:i:s') }}</td>
                    <td>{{ $record->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->format('H:i:s') : 'Not checked out' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #95a5a6;">No attendance records found for this date.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <p>Total Vendors Present: {{ $attendance->count() }}</p>
    </div>

    <div class="footer">
        <p>Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
