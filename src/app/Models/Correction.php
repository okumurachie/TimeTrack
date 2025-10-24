<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Correction extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'admin_id',
        'status',
        'reason',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    protected $appends = ['status_label'];

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => '承認待ち',
            'approved' => '承認済み',
            'rejected' => '却下',
        ];
        $raw = $this->attributes['status'] ?? null;
        return $labels[$raw] ?? $raw;
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
