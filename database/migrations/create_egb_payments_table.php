<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('egb_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('egb_users')->cascadeOnDelete();
            $table->string('external_id')->unique(); // Notre référence unique
            $table->string('freemopay_reference')->nullable()->unique(); // Référence Freemopay
            $table->integer('amount_fcfa');
            $table->string('phone_number');
            $table->enum('payment_method', ['momo', 'om']);
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->string('failure_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('external_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('egb_payments');
    }
};
