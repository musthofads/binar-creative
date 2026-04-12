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
        Schema::create('strip_photo_originals', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->index();
            $table->string('storage_path');
            $table->string('filename');
            $table->string('thumbnail_path')->nullable();
            $table->string('package_id')->nullable();
            $table->boolean('paid')->default(false);
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->integer('queue_number')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strip_photo_originals');
    }
};
