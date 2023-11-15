<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tank extends Model
{
    use HasFactory;
    protected $table = 'tank';
    public $timestamps = false;
    public function last_reading()
    {
        return $this->hasOne(TankLog::class, 'tank_id', 'id');
    }
    protected $appends = [
        'volume'
    ];
    public function getVolumeAttribute()
    {
        $bstiChart = BstiChart::select('volume')->where('tank_id', $this->id) ->where('height', '=', floor($this->height))
            ->first();
        return $bstiChart['volume'] ?? 0;
    }
}
