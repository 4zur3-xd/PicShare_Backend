<?php

use App\Enum\Language;
use App\Enum\Role;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('google_id')->nullable();
            $table->string('url_avatar')->nullable();
            $table->rememberToken();
            $table->string('user_code')->unique()->nullable();  // Temporarily set nullable
            $table->string('fcm_token')->nullable();            // Temporarily set nullable
            $table->enum('role', Role::getValues())->default(Role::USER);
            $table->enum('language', Language::getValues())->default(Language::EN);
            $table->tinyInteger('status')->default(1)->comment('1: active; 0: banned;');
            $table->timestamps();
        });

         // create Indexes
         Schema::table('users', function (Blueprint $table) {
            $table->index('name');         
            $table->index('user_code');     
            $table->index('email');       
            $table->index('status');       
        });


        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
