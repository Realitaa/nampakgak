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
        Schema::create('found_notifications', function (Blueprint $table) {
            $table->id();
            // The original lost report this notification is about. Deleting the
            // report removes its notifications, which belong to it alone.
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            // The user who says they found the item. Deleting their account
            // removes only the notifications they authored.
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->text('note');
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->index('item_id');
            // One notification per user per lost report: stops the same reporter
            // from resubmitting while leaving the domain rule simple.
            $table->unique(['item_id', 'reporter_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('found_notifications');
    }
};
