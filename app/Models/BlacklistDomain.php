<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlacklistDomain extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'category',
        'include_subdomains',
        'is_active',
        'source',
        'sync_status',
        'synced_at',
        'notes',
    ];

    protected $casts = [
        'include_subdomains' => 'boolean',
        'is_active' => 'boolean',
        'synced_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Sync Status
    |--------------------------------------------------------------------------
    */

    public const SYNC_PENDING = 'pending';

    public const SYNC_SYNCED = 'synced';

    public const SYNC_ERROR = 'error';
}
