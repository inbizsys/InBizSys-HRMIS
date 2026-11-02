<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    protected $fillable = [
        'employee_id',
        'organization_id',
        'user_id',
        'epf_number',   
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'department_id',
        'shift_id',
        'employee_category_id',
        'is_ot_eligible',
        'nic_number',
        'passport_number',
        'marital_status',
        'phone_number',
        'job_position',
        'work_location',
        'date_of_join',
        'status',
        'address_line_1',
        'address_line_2',
        'city',
        'postal_code'
    ];


    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
    
    public function category()
{
    return $this->belongsTo(EmployeeCategory::class, 'employee_category_id');
}

    /**
     * Defines a One-to-One relationship with the EmployeeEmergencyContact model.
     */
    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmployeeEmergencyContact::class);
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

        return $this->hasMany(EmployeeRelation::class, 'employee_id', 'id');
    }

    /**
     * Defines a relationship with the User model.
     */
    public function user(): BelongsTo
    {

        return $this->belongsTo(User::class, 'user_id');
    }


    /**
     * Defines a relationship with the OrganisationProfile model.
     */
    public function organisationProfile(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'Organisation_id');
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        // Links the department foreign key constraint back to the primary key of Department model
        return $this->belongsTo(Department::class, 'department_id');
    }

     public function salaryComponents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmployeeSalaryComponent::class, 'employee_id');
    }
     public function employeeCategory(): BelongsTo
    {
        return $this->belongsTo(EmployeeCategory::class, 'employee_category_id');
    }
     public function payrolls(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payroll::class);
    }

}
