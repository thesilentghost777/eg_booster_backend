<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('egb_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('sender_id')->constrained('egb_users');
            $table->foreignId('receiver_id')->constrained('egb_users');
            $table->integer('points');
            $table->integer('fees_points'); // 2% frais
            $table->integer('net_points'); // points - fees
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('egb_transfers');
    }
};
