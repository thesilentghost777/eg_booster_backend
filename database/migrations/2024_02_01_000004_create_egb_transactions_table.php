<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('egb_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('egb_users')->cascadeOnDelete();
            $table->enum('type', [
                'depot',
                'achat',
                'transfert_envoye',
                'transfert_recu',
                'bonus_parrainage',
                'gain_roue',
                'frais_transfert',
                'cadeau_bienvenue',
                'participation_roue',
            ]);
            $table->integer('amount_fcfa')->nullable(); // Montant en FCFA pour les dépôts
            $table->integer('points'); // Positif = crédit, Négatif = débit
            $table->integer('balance_before');
            $table->integer('balance_after');
            $table->string('reference')->unique();
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('egb_transactions');
    }
};
