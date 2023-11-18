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
    <div style="text-align: center"><strong>{{ $company['name'] }}</strong></div>
    <div style="text-align: center"><address>{{ $company['address'] }}</address></div>
    <div style="text-align: center"><strong>Company Bill</strong></div>
    <div style="text-align: center"><strong>{{ $company['date'] }}</strong></div>
    <table class="table" style="margin-top: 10px">
        <thead>
            <tr>
                <th>Product</th>
                <th style="text-align: right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td style="text-align: right">{{ number_format($row['amount'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th style="text-align: right">Total</th>
                <th style="text-align: right">{{ number_format(array_sum(array_column($data, 'amount')), 2) }}</th>
            </tr>
        </tfoot>
    </table>
</div>
</body>
</html>
