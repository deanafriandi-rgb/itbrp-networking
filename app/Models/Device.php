<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'mac_address',
        'ip_address',
        'hostname',
        'device_name',
        'device_type',
        'operating_system',
        'interface',
        'segment',
        'location',
        'owner_name',
        'owner_type',
        'source',
        'is_online',
        'first_seen',
        'last_seen',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'first_seen' => 'datetime',
        'last_seen' => 'datetime',
    ];
}
