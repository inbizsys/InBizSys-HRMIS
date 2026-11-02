<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    // Enables Eloquent model factories to easily generate dummy/test data for this model
    use HasFactory;


    protected $table = 'banks';
    protected $primaryKey = 'id';


    protected $fillable = [
        'bank_code',
        'bank_name',
        'is_active',
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(BankBranch::class);
    }
}