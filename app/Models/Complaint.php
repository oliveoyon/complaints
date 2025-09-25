<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants (use these when updating/validating status)
    public const STATUS_RECEIVED = 'received';
    public const STATUS_UNDER_CONSIDERATION = 'under_consideration';
    public const STATUS_SENT = 'sent';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_ABANDONED = 'abandoned';

    public static array $statuses = [
        self::STATUS_RECEIVED,
        self::STATUS_UNDER_CONSIDERATION,
        self::STATUS_SENT,
        self::STATUS_RESOLVED,
        self::STATUS_ABANDONED,
    ];

    protected $fillable = [
        'user_id',
        'category_id',
        'severity_id',
        'title',
        'description',
        'status',
        'assigned_to',
        'department_id',
        'is_anonymous',
        'attachments',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'attachments' => 'array',
    ];

    /* ---------------------------
     | Relationships
     | --------------------------- */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function category()
    {
        return $this->belongsTo(ComplaintCategory::class, 'category_id');
    }

    public function severity()
    {
        return $this->belongsTo(SeverityLevel::class, 'severity_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(ComplaintStatusHistory::class);
    }

    public function feedback()
    {
        return $this->hasOne(ComplaintFeedback::class);
    }

    /* ---------------------------
     | Scopes (helpful shortcuts)
     | --------------------------- */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function user()
    {
        return $this->reporter();
    }
}
