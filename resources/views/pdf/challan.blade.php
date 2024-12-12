<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }
        .container {
            box-sizing: border-box;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            position: relative;
        }
        .header h1 {
            font-size: 20px;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header h2 {
            font-size: 14px;
            margin: 0;
            font-weight: normal;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        .date {
            position: absolute;
            top: 0;
            right: 0;
            font-weight: bold;
            font-size: 12px;
        }
        .title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin: 20px 0;
        }
        .details {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .details td {
            padding: 5px 0;
            vertical-align: top;
        }
        .dotted-line {
            border-bottom: 1px dotted #000;
            display: inline-block;
            width: 80%;
            vertical-align: middle;
        }
        .dotted-line1 {
            border-bottom: 1px dotted #000;
            display: inline-block;
            width: 100%;
            vertical-align: middle;
        }
        .accounts {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .accounts td {
            padding: 5px;
            border: 1px solid #000;
            vertical-align: top;
            text-align: center;
        }
        .accounts .text-left {
            text-align: left;
            padding-left: 10px;
        }
        .accounts .bold {
            font-weight: bold;
        }
        .signatures {
            margin-top: 30px;
            text-align: center;
            width: 100%;
        }
        .signatures td {
            padding: 10px;
            border: 1px solid #000;
        }
        .footer {
            margin-top: 30px;
            font-weight: bold;
        }
        .footer p {
            margin: 10px 0;
        }
        .title-table {
            width: 100%;
            border-collapse: collapse;
        }
        .title-table td {
            vertical-align: middle;
        }
        .memo-cell {
            text-align: right;
            font-weight: bold;
            text-decoration: underline;
        }
        .date {
            text-align: right;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 200px;
        }
    </style>
    <title>Memo of Expenses</title>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{{ $data['company_name'] }}</h1>
        <p>{{ $data['company_address'] }}</p>
    </div>

    <table class="title-table" style="margin-bottom: 10px">
        <tr>
            <td style="width: 60%;" class="memo-cell">Delivery Challan</td>
            <td class="date">
                Date: {{ $data['date'] }}<br>
                Challan No:{{ $data['challan_no'] }}
            </td>
        </tr>
    </table>
    <table class="accounts">
        <thead>
        <tr>
            <td><strong>Sl No</strong></td>
            <td><strong>Product Name</strong></td>
            <td><strong>Quantity</strong></td>
            <td><strong>Unit Price</strong></td>
            <td style="text-align: right"><strong>Total</strong></td>
        </tr>
        </thead>
        <tbody>
            @foreach($data['items'] as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['product_name'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td>{{ $item['unit_price'] }}</td>
                    <td style="text-align: right">{{ $item['total'] }}</td>
                </tr>
            @endforeach
            <tr>
                <td style="text-align: right" colspan="4"><strong>Grand Total</strong></td>
                <td style="text-align: right"><strong>{{ $total }}</strong></td>
            </tr>
        </tbody>
    </table>
    <table class="footer-table">
        <tr>
            <td style="text-align: left; width: 50%; padding: 10px;">
                ------------------------<br>
                Manager & AC<br>
                {{ $company->name }}<br>
                {{ $company->address }}<br>
            </td>
            <td style="text-align: right; width: 50%; padding: 10px;">
                -----------------------<br>
                Receiver Signature
            </td>
        </tr>
    </table>
</div>
</body>
</html>
