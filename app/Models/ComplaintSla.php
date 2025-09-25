<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintSla extends Model
{
    use HasFactory;

    protected $fillable = [
        'complaint_id',
        'due_at',
        'escalated',
        'escalated_to',
        'remarks',
    ];

    protected $casts = [
        'escalated' => 'boolean',
        'due_at' => 'datetime',
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function escalatedTo()
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }
}
