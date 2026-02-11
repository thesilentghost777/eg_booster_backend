<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('egb_services', function (Blueprint $table) {
            $table->id();
            $table->enum('platform', ['tiktok', 'facebook', 'youtube', 'instagram', 'whatsapp']);
            $table->string('service_type'); // vues, commentaires, abonnes, likes, spectateurs, reactions, membres, reels
            $table->string('label'); // Label affiché au client
            $table->integer('quantity');
            $table->integer('price_points');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('egb_services');
    }
};