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
        Bill Summary
        <p><strong>Company Name: {{ $name }}</strong></p>
        <p>Date: {{ $date }}</p>
    </div>

    <!-- Report Content -->
    <div class="print-body">
        <table class="print-details">
            <tr>
                <th rowspan="2">Car Number</th>
                @foreach($data['products'] as $product)
                    <th colspan="3" style="text-align: center">{{ $product['name'] }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach($data['products'] as $product)
                    <th style="text-align: center">Quantity</th>
                    <th>Unit Price</th>
                    <th>Amount</th>
                @endforeach
            </tr>
            @foreach ($data['data'] as $each)
                <tr>
                    <td>{{ $each['car_number'] }}</td>
                    @foreach ($data['products'] as $product)
                        @php
                            $productData = collect($each['products'])->firstWhere('name', $product['name']);
                        @endphp
                        <td style="text-align: center">
                            {{ $productData['quantity'] ?? '' }}
                        </td>
                        <td style="text-align: right">
                            {{ $productData['unit_price'] ?? '' }}
                        </td>
                        <td style="text-align: right">
                            {{ $productData['amount'] ?? '' }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
            <tr>
                <th>Total</th>
                @foreach($data['total'] as $amount)
                    <th colspan="3" style="text-align: right">{{ $amount }}</th>
                @endforeach
            </tr>
            <tr>
                <th colspan="{{ count($data['products']) * 3 }}">Grand Total</th>
                <th style="text-align: right">{{ $data['grandTotal'] }}</th>
            </tr>
        </table>
    </div>
</div>
<!-- Footer Section -->
@include('layouts.footer')
</body>
</html>
