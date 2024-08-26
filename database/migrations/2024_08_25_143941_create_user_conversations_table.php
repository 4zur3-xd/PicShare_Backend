<?php

use App\Models\Conversation;
use App\Models\User;
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
        Schema::create('user_conversations', function (Blueprint $table) {
            $table->foreignIdFor(User::class)
            ->constrained()
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
      
            $table->foreignIdFor(Conversation::class)
            ->constrained()
            ->cascadeOnDelete()
            ->cascadeOnUpdate();

            $table->primary(['user_id', 'conversation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_conversations');
    }
};
