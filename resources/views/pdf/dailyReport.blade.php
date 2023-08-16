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
    <h3 class="text-center mb-4">Product Sale</h3>
    <table class="table table-bordered mb-5">
        <thead>
        <tr>
            <th rowspan="2">Sale</th>
            <th colspan="2" class="text-center">Shift 1</th>
            <th colspan="2" class="text-center">Shift 2</th>
            <th rowspan="2" class="text-center">Total</th>
        </tr>
        <tr>
            <th class="text-end">Quantity</th>
            <th class="text-end">Amount</th>
            <th class="text-end">Quantity</th>
            <th class="text-end">Amount</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Octane</td>
            <td class="text-end">230</td>
            <td class="text-end">
                <div>32,000</div>
            </td>
            <td class="text-end">
                <div>32,000</div>
                <div><i class="fa-solid fa-circle-down text-danger"></i></div>
                <div class="text-danger">5%</div>
            </td>
            <td class="text-end">230</td>

            <td class="text-end">64,000</td>
        </tr>
        <tr>
            <td>Octane</td>
            <td class="text-end">230</td>
            <td class="text-end">
                <div>34,000</div>
            </td>
            <td class="text-end">
                <div>34,000</div>
                <div><i class="fa-solid fa-circle-up text-success"></i></div>
                <div class="text-success">+5%</div>
            </td>
            <td class="text-end">230</td>

            <td class="text-end">64,000</td>
        </tr>
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
        <tr>
            <td>Octane</td>
            <td>3-3-23 4:34:56</td>
            <td>287 Litres</td>
            <td>-5 litres</td>
        </tr>
        </tbody>
    </table>
    <h3 class="text-center mb-4">Stock (tank_log)</h3>
    <table class="table table-bordered mb-5">
        <thead>
        <tr>
            <th>Product</th>
            <th>Start (3-3-23 4:34:56)</th>
            <th>End (3-3-23 4:34:56)</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Octane</td>
            <td>400litres</td>
            <td>287 Litres</td>
        </tr>
        </tbody>
    </table>
    <h3 class="text-center mb-4">Expenses</h3>
    <table class="table table-bordered mb-5">
        <tr>
            <th>Salary</th>
            <td>3,00,000</td>
        </tr>
        <tr>
            <th>Maintenance</th>
            <td>34,0000</td>
        </tr>
        <tr>
            <th>COGS (Octane)</th>
            <td>7,00,000</td>
        </tr>
    </table>
    <h3 class="text-center mb-4">Due Payments</h3>
    <table class="table table-bordered mb-5">
        <thead>
        <tr>
            <th>Provider</th>
            <th>Date Submitted</th>
            <th>Due Date</th>
            <th>Amount</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Broadbank INternet</td>
            <td>3-3-23 4:34:56</td>
            <td>3-3-23 4:34:56</td>
            <td>32,0000</td>
        </tr>
        <tr>
            <td>COGS (Octane)</td>
            <td>3-3-23 4:34:56</td>
            <td>3-3-23 4:34:56</td>
            <td>32,0000</td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td>Total</td>
            <td>64,0000</td>
        </tr>
        </tbody>
    </table>
    <h3 class="text-center mb-4">Due Invoices</h3>
    <table class="table table-bordered mb-5">
        <thead>
        <tr>
            <th>Party</th>
            <th>Billed/Invoiced</th>
            <th>Due</th>
            <th>Overdue </th>
            <th>Total </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Boisakhi</td>
            <td>32,000</td>
            <td>32,0000</td>
            <td>30,000</td>
            <td>32,0000</td>
        </tr>
        <tr>
            <td>Akij Grojp</td>
            <td>32,000</td>
            <td>32,0000</td>
            <td>30,000</td>
            <td>32,0000</td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td>Total</td>
            <td>64,0000</td>
        </tr>
        </tbody>
    </table>
    <h3 class="text-center mb-4">Asset Balance </h3>
    <table class="table table-bordered mb-5">
        <tr>
            <th>Cash</th>
            <td>3,00,000</td>
        </tr>
        <tr>
            <th>Cash in hand-Rafiq</th>
            <td>34,0000</td>
        </tr>
        <tr>
            <th>Exim Bank</th>
            <td>7,00,000</td>
        </tr>
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
