<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('egb_wheel_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wheel_event_id')->constrained('egb_wheel_events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('egb_users')->cascadeOnDelete();
            $table->integer('points_bet');
            $table->timestamps();

            $table->unique(['wheel_event_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('egb_wheel_participations');
    }
};
