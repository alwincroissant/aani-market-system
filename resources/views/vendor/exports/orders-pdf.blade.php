<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Orders Report</title>
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
        <p>Orders Report</p>
    </div>

    <div class="vendor-info">
        <strong>Vendor:</strong> {{ $vendor->business_name }}<br>
        <strong>Owner:</strong> {{ $vendor->owner_name }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Order Number</th>
                <th>Order Status</th>
                <th>Item Status</th>
                <th>Date</th>
                <th style="text-align: right;">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td>{{ $order->order_reference }}</td>
                    <td>{{ ucfirst($order->order_status) }}</td>
                    <td>{{ ucfirst($order->item_status) }}</td>
                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d H:i') }}</td>
                    <td style="text-align: right;">KSH {{ number_format($order->total_amount ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #95a5a6;">No orders found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
