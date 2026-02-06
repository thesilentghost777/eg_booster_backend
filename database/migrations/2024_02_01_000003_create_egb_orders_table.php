<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('egb_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->constrained('egb_users')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('egb_services');
            $table->string('link'); // URL vers la vidéo/profil/page
            $table->integer('quantity');
            $table->integer('points_spent');
            $table->enum('status', ['en_attente', 'en_cours', 'termine', 'annule'])->default('en_attente');
            $table->boolean('is_free_gift')->default(false);
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('egb_orders');
    }
};
