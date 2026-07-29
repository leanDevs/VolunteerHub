<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'title',
        'description',
        'location',
        'status',
        'start_time',
        'end_time',
        'capacity',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(User::class, 'organization_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }
}
