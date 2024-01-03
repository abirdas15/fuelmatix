<!DOCTYPE html>
<html>
<head>
    <style>
        @page { margin: 5px; }
        body { margin: 5px; }
        .table-bordered tr th {
            font-size: 14px;
        }
        .table-bordered tr td {
            font-size: 14px;
        }
        .table {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }
        .table td, .table th {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .table tr:nth-child(even){background-color: #f2f2f2;}

        .table tr:hover {background-color: #ddd;}

        .table th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            color: black;
        }
        .text-center {
            text-align: center;
        }
        .text-end {
            text-align: right;
        }
    </style>
</head>
<body>
<div style="width: 100%; margin-top: 20px; padding: 20px">
    <table class="table" style="width: 95%; margin-bottom: 30px">
        <thead>
        <tr>
            <th colspan="3" style="text-align: center; background-color: #fcfbfb">Product Sale</th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <th>Shift</th>
                <th>Quantity</th>
                <th style="text-align: right">Amount</th>
            </tr>
        </tbody>
        @foreach($data['shift_sale'] as $shiftSale)
            <tbody>
                <tr>
                    <th colspan="3" style="text-align: center; background-color: #eae9e9">{{ $shiftSale['product_name'] }}</th>
                </tr>
            </tbody>
            @foreach($shiftSale['data'] as $row)
                <tbody>
                <tr>
                    <td>{{ $row['time'] }}</td>
                    <td>{{ $row['quantity'].' '.$row['unit'] }}</td>
                    <td style="text-align: right">{{ $row['amount'] }}</td>
                </tr>
                </tbody>
            @endforeach
        @endforeach
        @foreach($data['pos_sale'] as $posSale)
            <tbody>
            <tr>
                <th colspan="3" style="text-align: center; background-color: #eae9e9">{{ $posSale['product_name'] }}</th>
            </tr>
            <tr>
                <td style="background-color: #ffffff">{{ $posSale['time'] }}</td>
                <td style="background-color: #ffffff">{{ $posSale['quantity'].' '.$posSale['unit'] }}</td>
                <td style="text-align: right; background-color: #ffffff">{{ $posSale['amount'] }}</td>
            </tr>
            </tbody>
        @endforeach
        <tfoot>
            <tr>
                <th colspan="2" style="text-align: right">Total</th>
                <th style="text-align: right">{{ $data['total']['sale'] }}</th>
            </tr>
        </tfoot>
    </table>
    <table class="table" style="width: 95%; margin-bottom: 30px">
        <thead>
            <tr>
                <th colspan="4" style="text-align: center; background-color: #fcfbfb">Refill</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>Product</th>
                <th>Time</th>
                <th>Quantity</th>
                <th>Loss/Gain</th>
            </tr>
        </tbody>
        <tbody>
        @foreach($data['tank_refill'] as $row)
            <tr>
                <td>{{ $row['product_name'] }}</td>
                <td>{{ $row['date'] }}</td>
                <td>{{ $row['quantity'].' '.$row['unit'] }}</td>
                <td>{{ $row['net_profit'].' '.$row['unit'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <table class="table" style="width: 95%; margin-bottom: 30px">
        <thead>
        <tr>
            <th colspan="3" style="text-align: center; background-color: #fcfbfb">Stock</th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <th>Product</th>
                <th>Start</th>
                <th>End</th>
            </tr>
        </tbody>
        <tbody>
        @foreach($data['stock'] as $row)
            <tr>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['opening_stock'].' '.$row['unit'] }}</td>
                <td>{{ $row['closing_stock'].' '.$row['unit'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <table class="table" style="width: 95%; margin-bottom: 30px">
        <thead>
            <tr>
                <th colspan="2" style="text-align: center; background-color: #fcfbfb">Expenses</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['expense'] as $row)
                <tr>
                    <td>{{ $row['category_name'] }}</td>
                    <td class="text-end">{{ $row['amount'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <table class="table" style="width: 95%; margin-bottom: 30px">
        <thead>
            <tr>
                <th colspan="2" style="text-align: center; background-color: #fcfbfb">Asset Balance</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data['asset_balance']['cash'] as $row)
            <tr>
                <td>{{ $row['category_name'] }}</td>
                <td class="text-end">{{ $row['amount'] }}</td>
            </tr>
        @endforeach
        @foreach($data['asset_balance']['bank'] as $row)
            <tr>
                <td>{{ $row['category_name'] }}</td>
                <td class="text-end">{{ $row['amount'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <table class="table" style="width: 95%; margin-bottom: 30px">
        <thead>
        <tr>
            <th colspan="2" style="text-align: center; background-color: #fcfbfb">Due Payments</th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <th>Provider</th>
                <th style="text-align: right">Amount</th>
            </tr>
            @foreach($data['due_payments'] as $row)
                <tr>
                    <td >{{ $row['category_name'] }}</td>
                    <td style="text-align: right">{{ $row['amount'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <table class="table" style="width: 95%; margin-bottom: 30px">
        <thead>
        <tr>
            <th colspan="2" style="text-align: center; background-color: #fcfbfb">Due Invoices</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>Party</th>
            <th style="text-align: right">Total </th>
        </tr>
        @foreach($data['due_invoice'] as $row)
            <tr>
                <td>{{ $row['category_name'] }}</td>
                <td style="text-align: right">{{ $row['amount'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
{{--    <h3 class="text-center mb-4">Attendance</h3>--}}
{{--    <table class="table" style="width: 95%; margin-bottom: 30px">--}}
{{--        <thead>--}}
{{--        <tr>--}}
{{--            <th>Shift 1</th>--}}
{{--            <th>Shift 2</th>--}}
{{--        </tr>--}}
{{--        </thead>--}}
{{--        <tbody>--}}
{{--        <tr>--}}
{{--            <td>--}}
{{--                <div>Fuelman -3</div>--}}
{{--                <div>Guard -2</div>--}}
{{--                <div>Suprevisor (suvo)-1</div>--}}
{{--                <div>Engineer (Yasin) -1</div>--}}
{{--            </td>--}}
{{--            <td>--}}
{{--                <div>Fuelman -3</div>--}}
{{--                <div>Guard -2</div>--}}
{{--                <div>Suprevisor (suvo)-1</div>--}}
{{--                <div>Engineer (Yasin) -1</div>--}}
{{--            </td>--}}
{{--        </tr>--}}
{{--        </tbody>--}}
{{--    </table>--}}
</div>
</body>
</html>
