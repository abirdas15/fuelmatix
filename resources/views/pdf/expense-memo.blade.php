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
    </style>
    <title>Memo of Expenses</title>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{{ $company->name }}</h1>
        <p>{{ $company->address }}</p>
    </div>

    <table class="title-table" style="margin-bottom: 10px">
        <tr>
            <td style="width: 60%;" class="memo-cell">MEMO OF EXPENSES</td>
            <td class="date">
                Date: {{ $data['date'] }}<br>
                Memo No:{{ $data['id'] }}
            </td>
        </tr>
    </table>

    <table class="details">
        <tr>
            <td colspan="4">
                <strong>To Mr/Messrs:{{ $data['paid_to'] }}</strong>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <strong>On account of:{{ $data['remarks'] }}</strong>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <strong>Being Paid By P.O/Cheque:{{ $payment_method }}</strong>
            </td>
        </tr>
    </table>

    <table class="accounts">
        <thead>
        <tr>
            <td style="text-align: center;" colspan="2"><strong>Heads of Account</strong></td>
            <td><strong>Accounts Code</strong></td>
            <td><strong>Taka</strong></td>
            <td><strong>Ps</strong></td>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td colspan="2">{{ $category_name }}</td>
            <td></td>
            <td>{{ $data['amount_format'] }}</td>
            <td></td>
        </tr>
        <tr>
            <td colspan="3" style="vertical-align: middle; text-align: left; height: 60px">
                Total Taka: <span>{{ $data['number_text'] }}</span> only
            </td>
            <td style="vertical-align: middle;">{{ $data['amount_format'] }}</td>
            <td style="vertical-align: middle;"></td>
        </tr>

        <tr>
            <td style="vertical-align: bottom; font-weight: bold; height: 100px;">Accountant</td>
            <td style="vertical-align: bottom; font-weight: bold;">Dy Manager</td>
            <td style="vertical-align: bottom; font-weight: bold;">Manager</td>
            <td style="vertical-align: bottom; font-weight: bold;">Managing Director</td>
            <td style="text-align: center; vertical-align: middle;">
                <div style="margin-bottom: 10px;">Received above amount in full</div>
                <div style="border: 1px solid #000; height: 40px; width: 100px; text-align: center; line-height: 40px; margin: 0 auto;">
                    Stamp
                </div>
                <div>Signature of payee & date</div>
            </td>
        </tr>

        </tbody>
    </table>
    @if($data['status'] == 'approve')
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="text-align: left; width: 50%; padding: 10px;">Approved Date: {{ $approve_date }}</td>
                <td style="text-align: right; width: 50%; padding: 10px;">Approved By: {{ $approve_by }}</td>
            </tr>
        </table>
    @endif
</div>
</body>
</html>
