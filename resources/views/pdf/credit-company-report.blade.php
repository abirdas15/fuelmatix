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
        <p>Company: {{ $name }}</p>
        <p>Date: {{ $date }}</p>
    </div>

    <!-- Report Content -->
    <div class="print-body">
        <table class="print-details">
            <tr>
                <th>Date</th>
                <th>Bill</th>
                <th>Paid Amount</th>
                <th>Due Amount</th>
            </tr>
            <tr>
                <th>Opening Balance</th>
                <th></th>
                <th></th>
                <th style="text-align: right">{{ $data['opening_balance']['amount'] < 0 ? $data['opening_balance']['amount_format'] : '' }}</th>
                <th style="text-align: right">{{ $data['opening_balance']['amount'] > 0 ? $data['opening_balance']['amount_format'] : '' }}</th>
            </tr>
            @foreach($data['data'] as $each)
                <tr>
                    <td>{{ $each['date'] }}</td>
                    <td style="text-align: right">{{ $each['bill_amount'] }}</td>
                    <td style="text-align: right">{{ $each['paid_amount'] }}</td>
                    <td style="text-align: right">{{ $each['due_amount'] }}</td>
                </tr>
            @endforeach
            <tr>
                <th colspan="3">Closing Balance</th>
                <th style="text-align: right">{{ $data['data'][count($data['data']) - 1]['due_amount'] }}</th>
            </tr>
        </table>
    </div>
</div>
<!-- Footer Section -->
@include('layouts.footer')
</body>
</html>
