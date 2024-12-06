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
        <p><strong>Pos Machine Report</strong></p>
    </div>

    <!-- Report Content -->
    <div class="print-body">
        <table class="print-details">
            <tr>
                <th>Date</th>
                <th>Card Name</th>
                <th>Voucher Number</th>
                <th>
                    Card Number/<br>
                    Transaction ID
                </th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Amount</th>
            </tr>
            @foreach($data['data'] as $each)
                <tr>
                    <td>{{ $each['date'] }}</td>
                    <td>{{ $each['category_name'] }}</td>
                    <td>{{ $each['voucher_number'] }}</td>
                    <td>{{ $each['card_number'] }}</td>
                    <td>{{ $each['product_name'] }}</td>
                    <td style="text-align: right">{{ $each['quantity'] }}</td>
                    <td style="text-align: right">{{ $each['price'] }}</td>
                    <td style="text-align: right">{{ $each['subtotal'] }}</td>
                </tr>
            @endforeach
            <tr>
                <th colspan="5" style="text-align: right">Total</th>
                <th style="text-align: right">{{ $data['total']['quantity'] }}</th>
                <th style="text-align: right"></th>
                <th style="text-align: right">{{ $data['total']['amount'] }}</th>
            </tr>
        </table>
    </div>
</div>
<!-- Footer Section -->
@include('layouts.footer')
</body>
</html>
