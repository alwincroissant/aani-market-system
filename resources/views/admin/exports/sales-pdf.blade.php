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
                <th>Subtotal</th>
                <th>Market Fee (5%)</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->business_name }}</td>
                    <td align="right">KSH {{ number_format($order->subtotal, 2) }}</td>
                    <td align="right">KSH {{ number_format($order->market_fee, 2) }}</td>
                    <td align="right">KSH {{ number_format($order->total, 2) }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #95a5a6;">No orders found for the selected period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <th>Total Gross Sales</th>
                <td>KSH {{ number_format($totalGrossSales, 2) }}</td>
            </tr>
            <tr>
                <th>Total Market Fees</th>
                <td>KSH {{ number_format($totalMarketFees, 2) }}</td>
            </tr>
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
