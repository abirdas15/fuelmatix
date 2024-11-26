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
        <p><strong>Sale History</strong></p>
    </div>

    <!-- Report Content -->
    <div class="print-body">
        <table class="print-details">
            <tr>
                <th>Date</th>
                <th style="width: 20%">Invoice Number</th>
                <th>Company Name</th>
                <th>Payment Method</th>
                <th>Voucher Number</th>
                <th>Car Number</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>User</th>
            </tr>
            @foreach($data as $each)
                <tr>
                    <td>{{ $each['date'] }}</td>
                    <td>{{ $each['invoice_number'] }}</td>
                    <td>{{ $each['company_name'] }}</td>
                    <td>{{ $each['payment_method'] }}</td>
                    <td>{{ $each['voucher_number'] }}</td>
                    <td>{{ $each['car_number'] }}</td>
                    <td>{{ $each['product_name'] }}</td>
                    <td>{{ $each['quantity'] }}</td>
                    <td>{{ $each['total_amount'] }}</td>
                    <td>{{ $each['user_name'] }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
<!-- Footer Section -->
@include('layouts.footer')
</body>
</html>
