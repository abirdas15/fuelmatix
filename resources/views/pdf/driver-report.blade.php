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
        @if(!empty($name))
            <p>Company: {{ $name }}</p>
        @endif
        <p>Date: {{ $date }}</p>
    </div>

    <!-- Report Content -->
    <div class="print-body">
        <table class="print-details">
            <tr>
                <th style="width: 15%">Date</th>
                <th style="width: 30%">Company Name</th>
                <th style="width: 15%">Car Number</th>
                <th style="width: 13%">Voucher No</th>
                <th style="width: 12%">Quantity</th>
                <th style="width: 15%">Bill</th>
            </tr>
            @foreach($data['data'] as $each)
                <tr>
                    <td>{{ $each['date'] }}</td>
                    <td>{{ $each['company_name'] }}</td>
                    <td>{{ $each['car_number'] }}</td>
                    <td>{{ $each['voucher_no'] }}</td>
                    <td style="text-align: center">{{ $each['quantity'] }}</td>
                    <td style="text-align: right">{{ $each['bill'] }}</td>
                </tr>
            @endforeach
            <tr>
                <th colspan="4">Total</th>
                <th style="text-align: center">{{ $data['total']['quantity'] }}</th>
                <th style="text-align: right">{{ $data['total']['bill'] }}</th>
            </tr>
        </table>
    </div>
</div>
<!-- Footer Section -->
@include('layouts.footer')
</body>
</html>
