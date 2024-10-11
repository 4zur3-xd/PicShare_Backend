<?php

use App\Enum\SharedPostType;
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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();;
            $table->string('url_image')->nullable();
            $table->string('caption')->nullable();
            $table->integer('cmt_count')->default(0);
            $table->integer('like_count')->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->enum('type', SharedPostType::getValues())->default(SharedPostType::ALL_FRIENDS);
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
