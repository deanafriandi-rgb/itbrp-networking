<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blacklist_domains', function (Blueprint $table) {
            $table->id();

            $table->string('domain')->unique();

            // Contoh:
            // Social Media
            // Streaming
            // Messaging
            // Test
            $table->string('category', 100)->nullable();

            // Jika true:
            // facebook.com + seluruh subdomain facebook.com
            $table->boolean('include_subdomains')->default(true);

            $table->boolean('is_active')->default(true);

            // MANUAL / DEFAULT / IMPORT
            $table->string('source', 30)
                ->default('MANUAL');

            // PENDING / SYNCED / FAILED
            $table->string('sync_status', 30)
                ->default('PENDING');

            $table->timestamp('synced_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['is_active', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blacklist_domains');
    }
};
