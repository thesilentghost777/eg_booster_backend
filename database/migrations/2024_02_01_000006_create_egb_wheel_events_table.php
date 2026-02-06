<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('egb_wheel_events', function (Blueprint $table) {
            $table->id();
            $table->dateTime('scheduled_at');
            $table->enum('status', ['en_attente', 'en_cours', 'termine'])->default('en_attente');
            $table->integer('total_pot')->default(0);
            $table->foreignId('winner_id')->nullable()->constrained('egb_users')->nullOnDelete();
            $table->boolean('is_manual_winner')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('egb_wheel_events');
    }
};
