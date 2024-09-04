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
        <p><strong>Stock Summary</strong></p>
        <p>Date: {{ $date }}</p>
    </div>

    <!-- Report Content -->
    <div class="print-body">
        @foreach($data['data'] as $product)
            <table class="print-details">
                <tr class="bg-custom">
                    <th colspan="6" style="text-align: center;">{{ $product['product_name'] }}</th>
                </tr>
                <tr>
                    <th>Nozzle</th>
                    <th>Current Meter</th>
                    <th>Previous Meter</th>
                    <th>Sale</th>
                    <th>Unit Price</th>
                    <th>Amount</th>
                </tr>
                @foreach($product['tanks'] as $tank)
                    @foreach($tank['dispensers'] as $dispenser)
                        @foreach($dispenser['nozzle'] as $nozzle)
                            <tr>
                                <td>{{ $nozzle['nozzle_name'] }}</td>
                                <td style="text-align: right">{{ $nozzle['end_reading_format'] }}</td>
                                <td style="text-align: right">{{ $nozzle['start_reading_format'] }}</td>
                                <td style="text-align: right">{{ $nozzle['sale_format'] }}</td>
                                <td style="text-align: right">{{ $nozzle['unit_price_format'] }}</td>
                                <td style="text-align: right">{{ $nozzle['amount_format'] }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach
                <tr>
                    <th colspan="3" style="text-align: right">Sub Total:</th>
                    <td style="text-align: right">{{ $product['total'] }}</td>
                    <td></td>
                    <td style="text-align: right">{{ $product['subtotal_amount'] }}</td>
                </tr>
                <tr>
                    <th colspan="3" style="text-align: right">Less: Meter Test</th>
                    <td style="text-align: right">{{ $product['adjustment'] }}</td>
                    <td></td>
                    <td style="text-align: right">{{ $product['adjustment_amount'] }}</td>
                </tr>
                <tr>
                    <th colspan="3" style="text-align: right">Total</th>
                    <td style="text-align: right">{{ $product['total_sale'] }}</td>
                    <td></td>
                    <td style="text-align: right">{{ $product['total_amount'] }}</td>
                </tr>
            </table>
        @endforeach

        <div class="text-center"><h2>Received and Under Tank Summary</h2></div>
        @foreach($data['data'] as $product)
            <table class="print-details">
                <tr>
                    <th>U/Tank Name</th>
                    <th>Previous Balance</th>
                    <th>Receive</th>
                    <th>Total</th>
                    <th style="text-align: center">{{ $product['gain_loss'] >= 0 ? 'Gain' : 'Loss' }} Ratio</th>
                </tr>
                <tr>
                    <td>{{ $product['product_name'] }}</td>
                    <td style="text-align: right">{{ $product['end_reading'] }}</td>
                    <td style="text-align: right">{{ $product['tank_refill'] }}</td>
                    <td style="text-align: right">{{ $product['total_by_product'] }}</td>
                    <td style="text-align: right">{{ $product['gain_loss_format'] }}</td>
                </tr>
            </table>
            <table class="print-details">
                <tr class="bg-custom">
                    <th colspan="4" style="text-align: center;">{{ $product['product_name'].' Under Tank' }}</th>
                </tr>
                <tr>
                    <th>U/Tank Name</th>
                    <th style="text-align: center">U/Tank as per DIP</th>
                    <th style="text-align: center">In Tank Lorry</th>
                    <th style="text-align: center">Closing Balance</th>
                </tr>
                @foreach($product['tanks'] as $index => $tank)
                    <tr>
                        <td>{{ $tank['tank_name'] }}</td>
                        <td style="text-align: right">{{ $tank['end_reading_format'] }}</td>
                        @if($index == 0)
                            <td style="text-align: right" rowspan="{{ count($product['tanks']) }}">
                                {{ $product['pay_order'] }}
                            </td>
                            <td style="text-align: right" rowspan="{{ count($product['tanks']) }}">
                                {{ $product['closing_balance'] }}
                            </td>
                        @endif
                    </tr>
                @endforeach
            </table>
        @endforeach
        <div class="text-center"><h2>Company Sale</h2></div>
        <table class="print-details">
            <tr>
                <th>Company Name</th>
                <th>Product Name</th>
                <th style="text-align: center">Quantity</th>
                <th style="text-align: end">Amount</th>
            </tr>
            @foreach($data['companySales'] as $companySales)
                <tr>
                    <td>{{ $companySales['name'] }}</td>
                    <td class="">{{ $companySales['product_name'] }}</td>
                    <td style="text-align: center">{{ $companySales['quantity'] }}</td>
                    <td style="text-align: right">{{ $companySales['amount_format'] }}</td>
                </tr>
            @endforeach
            <tr>
                <th colspan="2" style="text-align: right">Total:</th>
                <td style="text-align: center">{{ $data['total']['quantity'] }}</td>
                <td style="text-align: right">{{ $data['total']['amount'] }}</td>
            </tr>
        </table>
            <div class="text-center"><h2>Expense</h2></div>
            <table class="print-details">
                <tr>
                    <th>Expense Category</th>
                    <th>Payment Type</th>
                    <th>Amount</th>
                </tr>
                @foreach($data['expenses'] as $expense)
                    <tr>
                        <td>{{ $expense['expense_type'] }}</td>
                        <td>{{ $expense['payment_method'] }}</td>
                        <td style="text-align: right">{{ $expense['amount_format'] }}</td>
                    </tr>
                @endforeach
                <tr>
                    <th colspan="2" style="text-align: right">Total:</th>
                    <td style="text-align: right">{{ $data['total']['expense'] }}</td>
                </tr>
            </table>
    </div>
</div>
<!-- Footer Section -->
@include('layouts.footer')
</body>
</html>
