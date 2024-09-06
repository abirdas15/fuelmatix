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
        <p>Date: {{ $date }}</p>
    </div>

    <!-- Report Content -->
    <div class="print-body">
        <table class="print-details">
            <tr>
                <th>Company Name</th>
                @foreach($data['products'] as $product)
                    <th style="text-align: right">{{ $product['name'] }}</th>
                @endforeach
            </tr>
            @foreach ($data['data'] as $each)
                <tr>
                    <td>{{ $each['company_name'] }}</td>
                    @foreach ($data['products'] as $product)
                        <td style="text-align: right">
                            @php
                                $productData = collect($each['products'])->firstWhere('name', $product['name']);
                            @endphp
                            {{ $productData['amount'] ?? '' }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
            <tr>
                <th>Total</th>
                @foreach($data['total'] as $amount)
                    <th style="text-align: right">{{ $amount }}</th>
                @endforeach
            </tr>
        </table>
    </div>
</div>
<!-- Footer Section -->
@include('layouts.footer')
</body>
</html>
