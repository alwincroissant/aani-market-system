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
        .totals-section {
            margin-top: 30px;
            text-align: right;
        }
        .totals-table {
            width: 400px;
            margin-left: auto;
        }
        .totals-table th {
            background-color: #27ae60;
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

    <div class="date-range">
        Period: {{ $startDate }} to {{ $endDate }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Order Number</th>
                <th>Vendor Name</th>
                <th>Type</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($allSales as $sale)
                <tr>
                    <td>{{ $sale->order_number }}</td>
                    <td>{{ $sale->business_name }}</td>
                    <td>{{ ucfirst($sale->sale_type) }}</td>
                    <td align="right">KSH {{ number_format($sale->total, 2) }}</td>
                    <td>{{ ucfirst($sale->status) }}</td>
                    <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #95a5a6;">No sales found for the selected period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <th style="background-color: #16a085;">Total Revenue</th>
                <td style="background-color: #d5f4e6; font-weight: bold;">KSH {{ number_format($totalRevenue, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
