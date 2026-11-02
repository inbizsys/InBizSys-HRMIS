<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'is_required'];

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }
}
