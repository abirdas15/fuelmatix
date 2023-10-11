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
            width: 95%;
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
    <div style="text-align: center"><strong>{{ $data['company_name'] }}</strong></div>
    <div style="text-align: center"><address>{{ $data['address'] }}</address></div>
    <div style="text-align: center"><strong>Salary Report</strong></div>
    <div style="text-align: center"><strong>{{ $data['date'] }}</strong></div>
    <table class="table" style="margin-top: 10px">
        <tr>
            <th>Pay Date</th>
            <td>{{ $data['pay_date'] }}</td>
        </tr>
        <tr>
            <th>Employee ID</th>
            <td>{{ $data['employee_id'] }}</td>
        </tr>
        <tr>
            <th>Name</th>
            <td>{{ $data['employee_name'] }}</td>
        </tr>
        <tr>
            <th>Position</th>
            <td>{{ $data['position'] }}</td>
        </tr>
        <tr>
            <th>Payment Method</th>
            <td>{{ $data['bank_name'] }}</td>
        </tr>
        <tr>
            <th>Amount</th>
            <td>{{ number_format($data['amount'], 2) }}</td>
        </tr>
        <tr>
            <th>Amount in Word</th>
            <td>{{ $data['amount_in_word'] }}</td>
        </tr>
    </table>

    <div style="margin-top: 80px">
        <strong>Received</strong>
    </div>
    <div style="margin-top: 80px; border: 1px solid; width: 200px"></div>
    <div style="margin-top: 10px">
        <strong>Employee Signature</strong>
    </div>
</div>
</body>
</html>
