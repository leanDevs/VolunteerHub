<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordArchive extends Model
{
    use HasFactory;

    // The database table doesn't have standard timestamps (only created_at is managed by migration defaults)
    const UPDATED_AT = null;

    protected $fillable = [
        'table_name',
        'record_id',
        'original_data',
        'archived_by',
        'reason',
    ];

    protected $casts = [
        'original_data' => 'array',
    ];

    public function archiver()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }
}
