<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'service',
        'component_type',
        'status',
        'response_time_ms',
        'message',
        'meta',
        'checked_at',
    ];

    protected $casts = [
        'response_time_ms' => 'integer',
        'meta' => 'array',
        'checked_at' => 'datetime',
    ];

    public const STATUS_UP = 'UP';

    public const STATUS_DOWN = 'DOWN';

    public const STATUS_WARNING = 'WARNING';

    public const STATUS_UNKNOWN = 'UNKNOWN';
}
