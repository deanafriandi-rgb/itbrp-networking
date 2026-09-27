<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogImportState extends Model
{
    use HasFactory;

    protected $fillable = [
        'log_file',
        'file_inode',
        'byte_offset',
        'file_size',
        'file_modified_at',
        'last_import_at',
        'last_status',
        'last_error',
    ];

    protected $casts = [
        'byte_offset' => 'integer',
        'file_size' => 'integer',
        'file_modified_at' => 'datetime',
        'last_import_at' => 'datetime',
    ];
}
