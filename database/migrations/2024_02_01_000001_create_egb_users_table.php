<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('egb_users', function (Blueprint $table) {
            $table->id();
            $table->string('prenom');
            $table->string('telephone')->unique();
            $table->string('code_pin');
            $table->string('email')->nullable();
            $table->integer('points_balance')->default(0);
            $table->string('referral_code')->unique();
            $table->foreignId('referred_by')->nullable()->constrained('egb_users')->nullOnDelete();
            $table->string('device_fingerprint')->nullable();
            $table->string('ip_address')->nullable();
            $table->boolean('free_views_claimed')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->index(['device_fingerprint', 'ip_address']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('egb_users');
    }
};
