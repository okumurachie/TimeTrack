<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'total_break',
        'total_work',
        'is_on_break',
        'has_request',
    ];

    protected $casts = [
        'work_date'  => 'date',
        'clock_in'   => 'datetime:H:i',
        'clock_out'  => 'datetime:H:i',
        'is_on_break' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function corrections()
    {
        return $this->hasMany(Correction::class);
    }
}
