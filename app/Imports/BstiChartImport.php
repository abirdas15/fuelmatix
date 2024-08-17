<?php

namespace App\Imports;

use App\Models\BstiChart;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class BstiChartImport implements ToCollection
{
    protected $tank_id;
    public function __construct(int $tank_id)
    {
        $this->tank_id = $tank_id;
    }

    /**
     * @param Collection $rows
     * @return void
     */
    public function collection(Collection $rows)
    {
        $dataArray = [];
        foreach ($rows as $row) {
            if ($row[0] != null && $row[1] != null) {
                $dataArray[] = [
                    'height' => $row[0],
                    'volume' => $row[1],
                    'tank_id' => $this->tank_id
                ];
            }
        }
        if (count($dataArray) > 0) {
            BstiChart::insert($dataArray);
        }
    }
}
