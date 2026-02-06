<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('egb_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('egb_users')->cascadeOnDelete();
            $table->foreignId('referred_id')->constrained('egb_users')->cascadeOnDelete();
            $table->boolean('has_deposited')->default(false);
            $table->integer('points_earned')->default(0);
            $table->timestamps();

            $table->unique(['referrer_id', 'referred_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('egb_referrals');
    }
};
