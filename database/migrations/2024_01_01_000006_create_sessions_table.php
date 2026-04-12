<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('photobooth_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique()->index();
            $table->string('customer_name')->nullable();
            $table->string('package_type')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->string('qr_code_url')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('photo_count')->default(0);
            $table->boolean('strip_generated')->default(false);
            $table->string('strip_path')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photobooth_sessions');
    }
};
