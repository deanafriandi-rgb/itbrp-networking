<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'logged_at',
        'client_ip',
        'domain',
        'url',
        'method',
        'protocol',
        'squid_code',
        'http_code',
        'bytes',
        'elapsed_ms',
        'hierarchy',
        'destination_ip',
        'destination_port',
        'mime_type',
        'category',
        'source_file',
        'source_inode',
        'source_offset',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
        'http_code' => 'integer',
        'bytes' => 'integer',
        'elapsed_ms' => 'integer',
        'destination_port' => 'integer',
        'source_offset' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Category Constants
    |--------------------------------------------------------------------------
    */

    public const CATEGORY_ALLOWED = 'ALLOWED';

    public const CATEGORY_BLOCKED = 'BLOCKED';

    public const CATEGORY_CACHE_HIT = 'CACHE_HIT';

    public const CATEGORY_CACHE_MISS = 'CACHE_MISS';

    public const CATEGORY_FAILED = 'FAILED';

    public const CATEGORY_OTHER = 'OTHER';
}
