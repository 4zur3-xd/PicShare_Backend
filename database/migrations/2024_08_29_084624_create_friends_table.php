<?php

use App\Enum\FriendStatus;
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
        Schema::create('friends', function (Blueprint $table) {
            $table->id();
            $table->enum('status', FriendStatus::getValues())->default(FriendStatus::PENDING);
            $table->timestamps();

            // Define foreign key constraints
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('friend_id')->constrained('users')->cascadeOnDelete();

            // Ensure that user_id and friend_id are not the same
            $table->unique(['user_id', 'friend_id']);

            // Optional: Ensure that the reverse relationship is also unique (e.g., friend_id and user_id as well)
            $table->unique(['friend_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friends');
    }
};
