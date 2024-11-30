<?php

namespace App\Models;

use App\Common\Module;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $table = 'transactions';
    public $timestamps = false;
    protected $appends = [
        'file_path'
    ];
    public function getFilePathAttribute(): ?string
    {
        if (!empty($this->file)) {
            return asset('uploads/'.$this->file);
        }
        return null;
    }
    public function staff_loan_payment()
    {
        return $this->hasMany(Transaction::class, 'module_id', 'id')
            ->where('module', Module::STAFF_LOAN_PAYMENT)
            ->havingRaw('credit_amount > 0');
    }
}
