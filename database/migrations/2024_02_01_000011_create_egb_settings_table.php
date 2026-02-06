<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('egb_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->string('group')->default('general');
            $table->string('label')->nullable();
            $table->timestamps();
        });

        // Insérer les paramètres par défaut
        DB::table('egb_settings')->insert([
            ['key' => 'default_referral_code', 'value' => 'EGBOOST', 'type' => 'string', 'group' => 'referral', 'label' => 'Code parrainage par défaut', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'referral_bonus_deposit', 'value' => '50', 'type' => 'integer', 'group' => 'referral', 'label' => 'Bonus parrainage (filleul a déposé)', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'referral_bonus_no_deposit', 'value' => '1', 'type' => 'integer', 'group' => 'referral', 'label' => 'Bonus parrainage (filleul sans dépôt)', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'transfer_fee_percent', 'value' => '2', 'type' => 'integer', 'group' => 'wallet', 'label' => 'Frais de transfert (%)', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'min_deposit_fcfa', 'value' => '500', 'type' => 'integer', 'group' => 'wallet', 'label' => 'Dépôt minimum (FCFA)', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'free_views_quantity', 'value' => '1000', 'type' => 'integer', 'group' => 'gift', 'label' => 'Vues gratuites offertes', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'whatsapp_number', 'value' => '+237696087354', 'type' => 'string', 'group' => 'contact', 'label' => 'Numéro WhatsApp support', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'admin_email', 'value' => 'admin@egbooster.com', 'type' => 'string', 'group' => 'contact', 'label' => 'Email admin notifications', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'wheel_participation_points', 'value' => '1', 'type' => 'integer', 'group' => 'wheel', 'label' => 'Points par participation roue', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'admin_account_id', 'value' => '1', 'type' => 'integer', 'group' => 'general', 'label' => 'ID compte administrateur', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('egb_settings');
    }
};
