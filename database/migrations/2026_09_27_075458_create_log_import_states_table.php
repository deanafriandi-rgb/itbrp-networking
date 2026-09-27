<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_import_states', function (Blueprint $table) {
            $table->id();

            // /var/log/squid/access.log
            $table->string('log_file')->unique();

            /*
            |--------------------------------------------------------------------------
            | File identification
            |--------------------------------------------------------------------------
            |
            | inode digunakan untuk mendeteksi ketika Squid melakukan log rotate.
            |
            */

            $table->string('file_inode', 100)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Posisi terakhir
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('byte_offset')
                ->default(0);

            $table->unsignedBigInteger('file_size')
                ->default(0);

            $table->timestamp('file_modified_at')
                ->nullable();

            $table->timestamp('last_import_at')
                ->nullable();

            $table->string('last_status', 30)
                ->default('READY');

            $table->text('last_error')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_import_states');
    }
};
