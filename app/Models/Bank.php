<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    // Enables Eloquent model factories to easily generate dummy/test data for this model
    use HasFactory;

    // Explicitly defines the database table name associated with this model
    protected $table = 'banks';

    // Specifies the custom primary key column name for the table (Default is 'id')
    protected $primaryKey = 'id';

    // Defines the whitelist of attributes/columns that are allowed to be safely mass-assigned 
    // when using methods like Bank::create() or $bank->update()
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