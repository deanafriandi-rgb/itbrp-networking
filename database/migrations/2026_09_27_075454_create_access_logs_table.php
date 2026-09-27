<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();

            // Waktu transaksi dari Squid
            $table->dateTime('logged_at', 3)->index();

            // Client
            $table->string('client_ip', 45)->index();

            // Request
            $table->string('domain')->nullable();
            $table->text('url')->nullable();
            $table->string('method', 20)->nullable();

            // HTTP / HTTPS
            $table->string('protocol', 20)->nullable();

            // Hasil dari Squid
            // Contoh:
            // TCP_HIT
            // TCP_MISS
            // TCP_TUNNEL
            // TCP_DENIED
            // NONE_NONE
            $table->string('squid_code', 50)->nullable();

            $table->unsignedSmallInteger('http_code')->nullable();

            // Ukuran data yang diproses
            $table->unsignedBigInteger('bytes')->default(0);

            // Waktu proses/request Squid
            $table->unsignedBigInteger('elapsed_ms')->default(0);

            // Contoh:
            // ORIGINAL_DST/172.217.x.x
            // HIER_DIRECT/x.x.x.x
            $table->string('hierarchy', 150)->nullable();

            $table->string('destination_ip', 45)->nullable();
            $table->unsignedSmallInteger('destination_port')->nullable();

            $table->string('mime_type', 100)->nullable();

            // ALLOWED
            // BLOCKED
            // CACHE_HIT
            // CACHE_MISS
            // FAILED
            // OTHER
            $table->string('category', 30)
                ->default('OTHER');

            /*
            |--------------------------------------------------------------------------
            | Posisi sumber log
            |--------------------------------------------------------------------------
            |
            | Dipakai agar importer tidak memasukkan baris access.log yang sama
            | berulang kali.
            |
            */

            $table->string('source_file')->nullable();
            $table->string('source_inode', 100)->nullable();
            $table->unsignedBigInteger('source_offset')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Index Dashboard
            |--------------------------------------------------------------------------
            */

            $table->index(
                ['domain', 'logged_at'],
                'access_logs_domain_time_index'
            );

            $table->index(
                ['category', 'logged_at'],
                'access_logs_category_time_index'
            );

            $table->index(
                ['client_ip', 'logged_at'],
                'access_logs_client_time_index'
            );

            $table->index(
                ['squid_code', 'logged_at'],
                'access_logs_squid_code_time_index'
            );

            /*
            |--------------------------------------------------------------------------
            | Anti Duplicate
            |--------------------------------------------------------------------------
            */

            $table->unique(
                ['source_file', 'source_inode', 'source_offset'],
                'access_logs_source_position_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_logs');
    }
};
