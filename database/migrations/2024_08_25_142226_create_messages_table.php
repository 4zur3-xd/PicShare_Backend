<?php

use App\Enum\MessageType;
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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(Conversation::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('text')->nullable();
            $table->string('url_image')->nullable();
            $table->enum('message_type',MessageType::getValues())->default(MessageType::TEXT);
            $table->double('height')->nullable();
            $table->double('width')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
