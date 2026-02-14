<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
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
        .vendor-info {
            background-color: #ecf0f1;
            padding: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #27ae60;
        }
        .date-range {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #27ae60;
            color: white;
            padding: 10px;
            text-align: left;
            border: 1px solid #229954;
        }
        td {
            padding: 10px;
            border: 1px solid #ecf0f1;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .totals-section {
            margin-top: 20px;
            text-align: right;
        }
        .totals-table {
            width: 300px;
            margin-left: auto;
        }
        .totals-table th {
            background-color: #229954;
        }
        .totals-table td {
            text-align: right;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #95a5a6;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>AANI Market System</h1>
        <p>Sales Report</p>
    </div>

    <div class="vendor-info">
        <strong>Vendor:</strong> {{ $vendor->business_name }}<br>
        <strong>Owner:</strong> {{ $vendor->owner_name }}
    </div>

    <div class="date-range">
        Period: {{ $startDate }} to {{ $endDate }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th style="text-align: right;">Orders</th>
                <th style="text-align: right;">Total Sales</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $record)
                <tr>
                    <td>{{ $record->date }}</td>
                    <td style="text-align: right;">{{ $record->order_count }}</td>
                    <td style="text-align: right;">KSH {{ number_format($record->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #95a5a6;">No sales data found for the selected period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <th>Total Orders</th>
                <td>{{ $totalOrders }}</td>
            </tr>
            <tr>
                <th style="background-color: #16a085;">Total Sales</th>
                <td style="background-color: #d5f4e6; font-weight: bold;">KSH {{ number_format($totalSales, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
