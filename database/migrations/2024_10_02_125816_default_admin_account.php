<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $user = User::where('email', 'admin@picshare.com')->first();

        if(!$user){
            $user = User::create([
                'name' => 'Pic Share Admin',
                'email' => 'admin@picshare.com',
                'email_verified_at' => now(),
                'password' => 'administrator',
                'url_avatar' => '/images/pic_share_logo.png',
                'role' => 'admin'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        User::where('email', 'admin@picshare.com')->delete();
    }
};
