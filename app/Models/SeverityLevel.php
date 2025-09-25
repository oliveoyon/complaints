<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeverityLevel extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'priority'];

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'severity_id');
    }
}
