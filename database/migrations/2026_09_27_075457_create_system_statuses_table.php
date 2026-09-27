<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_statuses', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Service
            |--------------------------------------------------------------------------
            |
            | Contoh:
            | SQUID
            | MYSQL
            | SQUID_PORT_3128
            | SQUID_PORT_3129
            | SQUID_PORT_3130
            | HTTP_FAILOPEN
            | HTTPS_FAILOPEN
            | MIKROTIK
            | QUIC_POLICY
            |
            */

            $table->string('service')->index();

            // SQUID / DATABASE / MIKROTIK / PORT / FAILOPEN
            $table->string('component_type', 50)->nullable();

            // UP / DOWN / WARNING / UNKNOWN
            $table->string('status', 30)
                ->default('UNKNOWN');

            $table->unsignedInteger('response_time_ms')
                ->nullable();

            $table->text('message')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Metadata tambahan
            |--------------------------------------------------------------------------
            |
            | Contoh:
            |
            | {
            |   "port": 3130,
            |   "netwatch": "up",
            |   "pbr": "enabled"
            | }
            |
            */

            $table->json('meta')->nullable();

            $table->timestamp('checked_at')
                ->nullable()
                ->index();

            $table->timestamps();

            $table->index(
                ['service', 'checked_at'],
                'system_status_service_time_index'
            );

            $table->index(
                ['status', 'checked_at'],
                'system_status_status_time_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_statuses');
    }
};
