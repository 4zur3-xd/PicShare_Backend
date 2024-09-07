<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Post::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('reason');
            $table->foreignIdFor(User::class, 'reported_user')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(User::class, 'user_reporting')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
