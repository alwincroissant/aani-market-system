<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Products Report</title>
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
        <p>Products Report</p>
    </div>

    <div class="vendor-info">
        <strong>Vendor:</strong> {{ $vendor->business_name }}<br>
        <strong>Owner:</strong> {{ $vendor->owner_name }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>SKU</th>
                <th style="text-align: right;">Price</th>
                <th style="text-align: right;">Stock</th>
                <th style="text-align: right;">Total Sold</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->sku ?? 'N/A' }}</td>
                    <td style="text-align: right;">KSH {{ number_format($product->price, 2) }}</td>
                    <td style="text-align: right;">{{ $product->quantity }}</td>
                    <td style="text-align: right;">{{ $product->total_sold }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #95a5a6;">No products found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
