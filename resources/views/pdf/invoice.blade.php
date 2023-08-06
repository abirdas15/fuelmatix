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
        <div style="margin-bottom: 5px"><strong>{{ $company['name'] }}</strong></div>
        <address style="margin-bottom: 5px">{{ $company['address'] }}</address>
        <div style="margin-bottom: 5px">Phone: {{ $company['phone_number'] }}</div>
        <div style="margin-bottom: 5px">Email: {{ $company['email'] }}</div>
    </div>
    <div style="width: 50%; float: left;">
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
    <div style="width: 100%">
        <div style="margin-bottom: 5px"><strong>Bill To</strong></div>
        <address style="margin-bottom: 5px">{{ $company['address'] }}</address>
        <div style="margin-bottom: 5px">Phone: {{ $company['phone_number'] }}</div>
        <div style="margin-bottom: 5px">Email: {{ $company['email'] }}</div>
    </div>
</div>
</body>
</html>
