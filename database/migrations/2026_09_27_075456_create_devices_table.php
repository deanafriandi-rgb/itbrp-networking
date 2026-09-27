<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();

            $table->string('mac_address', 30)
                ->nullable()
                ->unique();

            $table->string('ip_address', 45)
                ->nullable()
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Informasi Device
            |--------------------------------------------------------------------------
            */

            $table->string('hostname')->nullable();

            // Nama yang tampil di UI
            // misalnya Laptop-Dosen-01
            $table->string('device_name')->nullable();

            // Laptop / Smartphone / Desktop / Unknown
            $table->string('device_type', 50)->nullable();

            $table->string('operating_system', 100)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Informasi Jaringan
            |--------------------------------------------------------------------------
            */

            $table->string('interface')->nullable();

            // Gedung A / Gedung B / Akademik
            $table->string('segment')->nullable();

            $table->string('location')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Informasi User
            |--------------------------------------------------------------------------
            |
            | Ini optional/manual.
            | MikroTik belum tentu bisa memberikan semuanya.
            |
            */

            $table->string('owner_name')->nullable();

            // MAHASISWA / DOSEN / STAFF / TAMU / UNKNOWN
            $table->string('owner_type', 30)
                ->default('UNKNOWN');

            // DHCP / ARP / MANUAL
            $table->string('source', 30)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_online')->default(false);

            $table->timestamp('first_seen')->nullable();

            $table->timestamp('last_seen')->nullable();

            $table->timestamps();

            $table->index(['is_online', 'last_seen']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
