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
        <p><strong>Windfall Report</strong></p>
        <p>Date: {{ $date }}</p>
    </div>

    <!-- Report Content -->
    <div class="print-body">
        <table class="print-details">
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Source</th>
                <th>Status</th>
                <th>Quantity</th>
                <th>Profit/Loss</th>
            </tr>
            @foreach($data['data'] as $each)
                <tr>
                    <td>{{ $each['date'] }}</td>
                    <td>{{ $each['product_name'] }}</td>
                    <td>{{ $each['source'] }}</td>
                    <td>{{ $each['status'] }}</td>
                    <td style="text-align: center">{{ $each['quantity'] }}</td>
                    <td style="text-align: right">{{ $each['amount'] }}</td>
                </tr>
            @endforeach
            <tr>
                <th colspan="2" style="text-align: right">Total</th>
                <th colspan="3">{{ $data['total']['status'] }}</th>
                <th style="text-align: right">{{ $data['total']['amount'] }}</th>
            </tr>
        </table>
    </div>
</div>
<!-- Footer Section -->
@include('layouts.footer')
</body>
</html>
