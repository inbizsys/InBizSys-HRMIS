<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankBranch extends Model
{

    use HasFactory;

    protected $table = 'bank_branches';

    protected $primaryKey = 'id';

    protected $fillable = [
        'bank_id',
        'branch_code',
        'branch_name',
        'is_active',
    ];

   
     public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
}