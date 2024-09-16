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
        Sales Report
        <p>Date: {{ $date }}</p>
    </div>

    <!-- Report Content -->
    <div class="print-body">
        <table class="print-details">
            <thead>
            <tr style="page-break-inside: avoid">
                <th>Date</th>
                <th>Tank</th>
                <th>Opening Balance (Tank)</th>
                <th>Stock In</th>
                <th>Nozzle</th>
                <th>Opening Meter</th>
                <th>Closing Meter</th>
                <th>Sale</th>
                <th>Total Sale</th>
                <th>Rate</th>
                <th>Amount</th>
                <th>Total Amount</th>
                <th>Closing Balance (Tank)</th>
            </tr>
            </thead>
            <tbody style="page-break-inside: avoid">
            @foreach($data as $dateIndex => $sale)
                <!-- Calculate total rows for the sale -->
                @php
                    $rowspan = 0;
                    foreach ($sale['tanks'] as $tank) {
                        foreach ($tank['dispensers'] as $dispenser) {
                            $rowspan += count($dispenser['nozzle']);
                        }
                    }
                @endphp

                    <!-- Loop through each tank -->
                @foreach($sale['tanks'] as $tankIndex => $tank)
                    <!-- Loop through each dispenser -->
                    @foreach($tank['dispensers'] as $dispenserIndex => $dispenser)
                        <!-- Loop through each nozzle -->
                        @foreach($dispenser['nozzle'] as $nozzleIndex => $nozzle)
                            <tr class="{{ $dateIndex % 2 === 0 ? 'table-striped-row' : '' }}" style="page-break-inside: avoid; page-break-before: auto">
                                <!-- Only show date for the first tank and dispenser of the sale -->
                                @if($tankIndex === 0 && $dispenserIndex === 0 && $nozzleIndex === 0)
                                    <td rowspan="{{ $rowspan }}">{{ $sale['date'] }}</td>
                                @endif
                                <!-- Only show tank name for the first dispenser and nozzle -->
                                @if($dispenserIndex === 0 && $nozzleIndex === 0)
                                    @php
                                        $dispenserRowspan = count($tank['dispensers']);
                                    @endphp
                                    <td rowspan="{{ $dispenserRowspan }}">{{ $tank['tank_name'] }}</td>
                                    <td rowspan="{{ $dispenserRowspan }}">{{ $tank['start_reading_format'] }}</td>
                                    <td rowspan="{{ $dispenserRowspan }}">{{ $tank['refill_format'] }}</td>
                                @endif
                                <td>{{ $nozzle['name'] }}</td>
                                <td>{{ $nozzle['start_reading_format'] }}</td>
                                <td>{{ $nozzle['end_reading_format'] }}</td>
                                <td>{{ $nozzle['sale_format'] }}</td>
                                @if($dispenserIndex === 0 && $nozzleIndex === 0)
                                    <td rowspan="{{ $dispenserRowspan }}">{{ $tank['total_sale_format'] }}</td>
                                    <td rowspan="{{ $dispenserRowspan }}">{{ $tank['selling_price_format'] }}</td>
                                @endif
                                <td>{{ $nozzle['amount_format'] }}</td>
                                @if($dispenserIndex === 0 && $nozzleIndex === 0)
                                    <td rowspan="{{ $dispenserRowspan }}">{{ $tank['total_amount_format'] }}</td>
                                    <td rowspan="{{ $dispenserRowspan }}">{{ $tank['end_reading_format'] }}</td>
                                @endif
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<!-- Footer Section -->
@include('layouts.footer')
</body>
</html>
