<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankBranch extends Model
{
    // Enables Eloquent model factories to easily generate dummy/test data for this branch model
    use HasFactory;

    // Explicitly defines the database table name associated with this model
    protected $table = 'bank_branches';

    // Specifies the custom primary key column name for the table (Default is 'id')
    protected $primaryKey = 'id';

    // Defines the whitelist of attributes/columns that are allowed to be safely mass-assigned 
    // when using methods like BankBranch::create() or $branch->update()
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