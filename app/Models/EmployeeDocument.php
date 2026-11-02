<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'document_category_id', 'file_path'];

    
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

   
    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }
}
