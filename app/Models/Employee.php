<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    // 🌟 [UPDATE]: ලිපින විස්තර (address fields) කෙලින්ම සේව් වෙන්න fillable එකට එකතු කළා
    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'nic_number',
        'passport_number',
        'marital_status',
        'phone_number',
        'job_position',
        'country',
        'date_of_join',
        'status',
        'address_line_1', // 👈 අලුතින් එක් කළා
        'address_line_2', // 👈 අලුතින් එක් කළා
        'city',           // 👈 අලුතින් එක් කළා
        'postal_code'     // 👈 අලුතින් එක් කළා
    ];

    // ❌ පරණ address() relationship එක අයින් කළා (මොකද දැන් වෙනම ටේබල් එකක් නැති නිසා)

    /**
     * Defines a One-to-One relationship with the EmployeeEmergencyContact model.
     */
    public function emergencyContact(): HasOne
    {
        return $this->hasOne(EmployeeEmergencyContact::class);
    }

    /**
     * Defines a One-to-One relationship with the EmployeeSalaryAndBank model.
     */
    public function salaryAndBank(): HasOne
    {
        return $this->hasOne(EmployeeSalaryAndBank::class);
    }

    /**
     * Defines a One-to-Many relationship with the EmployeeRelation model.
     */
    public function relations(): HasMany
    {
        return $this->hasMany(EmployeeRelation::class);
    }

    /**
     * Defines a One-to-Many relationship with the EmployeeRelation model explicitly.
     */
    public function employeeRelations()
    {
        return $this->hasMany(EmployeeRelation::class, 'employee_id');
    }

    /**
     * Defines a relationship with the User model.
     */
    public function user(): BelongsTo
    {
        // 🌟 අපි කලින් කතා වුණ විදිහට employees ටේබල් එකට 'user_id' Column එක දැම්මට පස්සේ මේක සුපිරියටම වැඩ කරනවා
        return $this->belongsTo(User::class, 'user_id');
    }
}