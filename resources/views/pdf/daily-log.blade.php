<!DOCTYPE html>
<html>
<head>
    <style>
        @page { margin: 5px; }
        body { margin: 5px; }
        .table {
            width: 95%;
        }
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
    <h3 class="text-center mb-4">Product Sale</h3>
    <table class="table table-bordered mb-5">
        <thead>
        <tr>
            <th rowspan="2">Sale</th>
            @for($i=1; $i <= $data['shift_sale']['totalShift']; $i++)
                <th style="text-align: center" colspan="{{ $data['shift_sale']['totalShift'] + 1 }}" class="text-center">Shift {{ $i }}</th>
            @endfor
            <th style="text-align: center" colspan="2">Total</th>
        </tr>
        <tr>
            @for($i=1; $i <= $data['shift_sale']['totalShift']; $i++)
                <th>Quantity</th>
                <th class="text-end">Amount</th>
            @endfor
            <th>Quantity</th>
            <th class="text-end">Amount</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data['shift_sale']['data'] as $row)
            <tr>
                <td>{{ $row['name'] }}</td>
                @foreach($row['value'] as $value)
                    <td>{{ $value['quantity'] }}liters</td>
                    <td class="text-end">
                        <div>{{ $value['amount'] }}</div>
                    </td>
                @endforeach
                <td>{{ $row['total']['quantity'] }}liters</td>
                <td class="text-end">
                    <div>{{ $row['total']['amount'] }}</div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <h3 class="text-center mb-4">Refill</h3>
    <table class="table table-bordered mb-5">
        <thead>
        <tr>
            <th>Product</th>
            <th>Time</th>
            <th>Liters</th>
            <th>Loss/Gain</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data['tank_refill'] as $row)
            <tr>
                <td>{{ $row['product_name'] }}</td>
                <td>{{ $row['date'] }}</td>
                <td>{{ $row['quantity'] }}litres</td>
                <td>{{ $row['net_profit'] }} litres</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <h3 class="text-center mb-4">Stock (tank_log)</h3>
    <table class="table table-bordered mb-5">
        <thead>
        <tr>
            <th>Product</th>
            <th>Start</th>
            <th>End</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data['stock'] as $row)
            <tr>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['opening_stock'] }}litres</td>
                <td>{{ $row['closing_stock'] }}litres</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <h3 class="text-center mb-4">Expenses</h3>
    <table class="table table-bordered mb-5">
        <tr>
            <th>Salary</th>
            <td class="text-end">{{ $data['expense']['salary'] }}</td>
        </tr>
        @foreach($data['expense']['cost_of_good_sold'] as $row)
            <tr>
                <th>COGS ({{ $row['category_name'] }})</th>
                <td class="text-end">{{ $row['amount'] }}</td>
            </tr>
        @endforeach

    </table>
    <h3 class="text-center mb-4">Due Payments</h3>
    <table class="table table-bordered mb-5">
        <thead>
        <tr>
            <th>Provider</th>
{{--            <th>Date Submitted</th>--}}
{{--            <th>Due Date</th>--}}
            <th>Amount</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data['due_payments'] as $row)
            <tr>
                <td class="text-end">{{ $row['category_name'] }}</td>
                {{--            <td>3-3-23 4:34:56</td>--}}
                {{--            <td>3-3-23 4:34:56</td>--}}
                <td class="text-end">{{ $row['amount'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <h3 class="text-center mb-4">Due Invoices</h3>
    <table class="table table-bordered mb-5">
        <thead>
        <tr>
            <th>Party</th>
{{--            <th>Billed/Invoiced</th>--}}
{{--            <th>Due</th>--}}
{{--            <th>Overdue </th>--}}
            <th>Total </th>
        </tr>
        </thead>
        <tbody>
        @foreach($data['due_invoice'] as $row)
            <tr>
                <td class="text-end">{{ $row['category_name'] }}</td>
                {{--            <td>3-3-23 4:34:56</td>--}}
                {{--            <td>3-3-23 4:34:56</td>--}}
                <td class="text-end">{{ $row['amount'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <h3 class="text-center mb-4">Asset Balance </h3>
    <table class="table table-bordered mb-5">
        @foreach($data['asset_balance']['cash'] as $row)
            <tr>
                <th>{{ $row['category_name'] }}</th>
                <td class="text-end">{{ $row['amount'] }}</td>
            </tr>
        @endforeach
        @foreach($data['asset_balance']['bank'] as $row)
            <tr>
                <th>{{ $row['category_name'] }}</th>
                <td class="text-end">{{ $row['amount'] }}</td>
            </tr>
        @endforeach
    </table>
    <h3 class="text-center mb-4">Attendance</h3>
    <table class="table table-bordered mb-5">
        <thead>
        <tr>
            <th>Shift 1</th>
            <th>Shift 2</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                <div>Fuelman -3</div>
                <div>Guard -2</div>
                <div>Suprevisor (suvo)-1</div>
                <div>Engineer (Yasin) -1</div>
            </td>
            <td>
                <div>Fuelman -3</div>
                <div>Guard -2</div>
                <div>Suprevisor (suvo)-1</div>
                <div>Engineer (Yasin) -1</div>
            </td>
        </tr>
        <tr>
            <td>
                Leave
            </td>
            <td></td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>
