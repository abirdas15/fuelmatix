<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Report</title>
    @include('layouts.style')
</head>
<body>
<div class="container">
    <!-- Company Header Section -->
    @include('layouts.header')

    <div class="report-details">
        <p><strong>Vendor Report</strong></p>
        <p>Vendor: {{ $vendor_name }}</p>
        <p>Date: {{ $date }}</p>
    </div>

    <!-- Report Content -->
    <div class="print-body">
        <table class="print-details">
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Payment Method</th>
                <th>Billed</th>
                <th>Paid</th>
                <th>Balance</th>
            </tr>
            @foreach($data['data'] as $each)
                <tr>
                    <td>{{ $each['date'] }}</td>
                    <td>{{ $each['product_name'] }}</td>
                    <td>{{ $each['payment_method'] }}</td>
                    <td style="text-align: right">{{ $each['bill'] }}</td>
                    <td style="text-align: right">{{ $each['paid'] }}</td>
                    <td style="text-align: right">{{ $each['balance'] }}</td>
                </tr>
            @endforeach
            <tr>
                <th colspan="3" style="text-align: right">Total</th>
                <th style="text-align: right">{{ $data['total']['bill'] }}</th>
                <th style="text-align: right">{{ $data['total']['paid'] }}</th>
                <th style="text-align: right">{{ $data['total']['balance'] }}</th>
            </tr>
        </table>
    </div>
</div>
<!-- Footer Section -->
@include('layouts.footer')
</body>
</html>
