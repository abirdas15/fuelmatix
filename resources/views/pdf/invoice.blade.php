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
    </style>
</head>
<body>
<div style="width: 100%; margin-top: 20px; padding: 20px">
    <div style="width: 50%; float: left">
        <div style="margin-bottom: 5px"><strong>{{ $data['company']['name'] }}</strong></div>
        <address style="margin-bottom: 5px">{{ $data['company']['address'] }}</address>
        <div style="margin-bottom: 5px">Phone: {{ $data['company']['phone_number'] }}</div>
        <div style="margin-bottom: 5px">Email: {{ $data['company']['email'] }}</div>
    </div>
    <div style="width: 50%; float: right">
        <div style="width: 90%">
            <div style="text-align: right; margin-bottom: 5px"><strong>INVOICE</strong></div>
            <table class="table">
                <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>{{ $data['invoice_number'] }}</td>
                    <td>{{ date('d/m/Y', strtotime($data['date'])) }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div style="width: 100%; margin-bottom: 50px; margin-top: 50px">
        <div style="margin-bottom: 5px;"><strong>Bill To</strong></div>
        <div style="margin-bottom: 5px">{{ $data['customer_company']['name'] }}</div>
        <address style="margin-bottom: 5px">{{ $data['customer_company']['address'] }}</address>
        <div style="margin-bottom: 5px">Phone: {{ $data['customer_company']['phone'] }}</div>
        <div style="margin-bottom: 5px">Email: {{ $data['customer_company']['email'] }}</div>
    </div>
    <table class="table" style="width: 95%; margin-bottom: 30px">
        <thead>
        <tr>
            <th>Product</th>
            <th>Car Number</th>
            <th style="text-align: center">Quantity</th>
            <th style="text-align: right">Unit Price</th>
            <th style="text-align: right">Subtotal</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data['invoice_item'] as $item)
            <tr>
                <td>{{ $item['product_name'] }}</td>
                <td>{{ $item['car_number'] }}</td>
                <td style="text-align: center">{{ $item['quantity'] }}</td>
                <td style="text-align: right">{{ $item['price'] }}</td>
                <td style="text-align: right">{{ $item['subtotal'] }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <th colspan="4" style="text-align: right">Total</th>
            <th style="text-align: right">{{ $data['amount'] }}</th>
        </tr>
        </tfoot>
    </table>
    <div style="text-align: center">
        <em>Thank you for your business!</em>
    </div>
</div>
</body>
</html>
