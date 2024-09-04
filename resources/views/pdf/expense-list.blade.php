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
        <p><strong>Expense Report</strong></p>
        <p>Date: {{ $date }}</p>
    </div>

    <!-- Report Content -->
    <div class="print-body">
        <table class="print-details">
            <tr>
                <th>Memo No</th>
                <th>Date</th>
                <th>Paid To</th>
                <th>Amount</th>
                <th>Category</th>
                <th>Remarks</th>
            </tr>
            @php
            $total = 0;
            @endphp
            @foreach($data['data'] as $each)
                @php
                    $total += $each['amount'];
                @endphp
                <tr>
                    <td>{{ $each['id'] }}</td>
                    <td>{{ $each['date'] }}</td>
                    <td>{{ $each['paid_to'] }}</td>
                    <td style="text-align: right">{{ number_format($each['amount'], 2) }}</td>
                    <td>{{ $each['expense'] }}</td>
                    <td>{{ $each['remarks'] }}</td>
                </tr>
            @endforeach
            <tr>
                <th colspan="3" style="text-align: right">Total</th>
                <th style="text-align: right">{{ number_format($total, 2)  }}</th>
                <th></th>
                <th></th>
            </tr>
        </table>
    </div>
</div>
<!-- Footer Section -->
@include('layouts.footer')
</body>
</html>
