<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EgbServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            // Service spécial pour le cadeau gratuit (DOIT être en premier pour avoir ID=1)
            ['platform' => 'tiktok', 'service_type' => 'vues', 'label' => '1000 Vues TikTok (Cadeau)', 'quantity' => 1000, 'price_points' => 0, 'description' => 'Cadeau de bienvenue: 1000 vues TikTok gratuites', 'sort_order' => 0],
            // TikTok
            ['platform' => 'tiktok', 'service_type' => 'vues', 'label' => '5000 Vues TikTok', 'quantity' => 5000, 'price_points' => 500, 'description' => 'Obtenez 5000 vues sur votre vidéo TikTok', 'sort_order' => 1],
            ['platform' => 'tiktok', 'service_type' => 'commentaires', 'label' => '100 Commentaires TikTok', 'quantity' => 100, 'price_points' => 500, 'description' => 'Recevez 100 commentaires sur votre vidéo TikTok', 'sort_order' => 2],
            ['platform' => 'tiktok', 'service_type' => 'abonnes', 'label' => '1000 Abonnés TikTok', 'quantity' => 1000, 'price_points' => 3000, 'description' => 'Gagnez 1000 abonnés TikTok', 'sort_order' => 3],
            ['platform' => 'tiktok', 'service_type' => 'likes', 'label' => '1000 J\'aime TikTok', 'quantity' => 1000, 'price_points' => 500, 'description' => 'Obtenez 1000 likes sur votre vidéo TikTok', 'sort_order' => 4],
            ['platform' => 'tiktok', 'service_type' => 'spectateurs', 'label' => '1000 Spectateurs Live TikTok', 'quantity' => 1000, 'price_points' => 5000, 'description' => '1000 spectateurs pour votre direct TikTok', 'sort_order' => 5],

            // Facebook
            ['platform' => 'facebook', 'service_type' => 'abonnes', 'label' => '1000 Abonnés Facebook', 'quantity' => 1000, 'price_points' => 1000, 'description' => 'Gagnez 1000 abonnés Facebook', 'sort_order' => 1],
            ['platform' => 'facebook', 'service_type' => 'commentaires', 'label' => '10 Commentaires Facebook', 'quantity' => 10, 'price_points' => 1000, 'description' => 'Recevez 10 commentaires Facebook', 'sort_order' => 2],
            ['platform' => 'facebook', 'service_type' => 'likes', 'label' => '1000 Likes Publication Facebook', 'quantity' => 1000, 'price_points' => 500, 'description' => '1000 likes sur votre publication Facebook', 'sort_order' => 3],
            ['platform' => 'facebook', 'service_type' => 'reactions', 'label' => '1000 Réactions J\'aime Facebook', 'quantity' => 1000, 'price_points' => 1000, 'description' => '1000 réactions J\'aime sur votre publication', 'sort_order' => 4],
            ['platform' => 'facebook', 'service_type' => 'membres', 'label' => '1000 Membres Groupe Facebook', 'quantity' => 1000, 'price_points' => 1000, 'description' => '1000 nouveaux membres dans votre groupe', 'sort_order' => 5],
            ['platform' => 'facebook', 'service_type' => 'vues', 'label' => '5000 Vues Reel Facebook', 'quantity' => 5000, 'price_points' => 1000, 'description' => '5000 vues sur votre Reel Facebook', 'sort_order' => 6],

            // YouTube
            ['platform' => 'youtube', 'service_type' => 'vues', 'label' => '1000 Vues YouTube', 'quantity' => 1000, 'price_points' => 2500, 'description' => '1000 vues sur votre vidéo YouTube', 'sort_order' => 1],
            ['platform' => 'youtube', 'service_type' => 'likes', 'label' => '1000 Likes YouTube', 'quantity' => 1000, 'price_points' => 1000, 'description' => '1000 likes sur votre vidéo YouTube', 'sort_order' => 2],
            ['platform' => 'youtube', 'service_type' => 'commentaires', 'label' => '100 Commentaires YouTube', 'quantity' => 100, 'price_points' => 1500, 'description' => '100 commentaires sur votre vidéo YouTube', 'sort_order' => 3],
            ['platform' => 'youtube', 'service_type' => 'abonnes', 'label' => '100 Abonnés YouTube', 'quantity' => 100, 'price_points' => 5000, 'description' => '100 abonnés YouTube', 'sort_order' => 4],

            // Instagram
            ['platform' => 'instagram', 'service_type' => 'vues', 'label' => '100 Vues Reel Instagram', 'quantity' => 100, 'price_points' => 1000, 'description' => '100 vues sur votre Reel Instagram', 'sort_order' => 1],
            ['platform' => 'instagram', 'service_type' => 'abonnes', 'label' => '50 Abonnés Instagram', 'quantity' => 50, 'price_points' => 100000, 'description' => '50 abonnés Instagram', 'sort_order' => 2],
            ['platform' => 'instagram', 'service_type' => 'likes', 'label' => '10 Likes Instagram', 'quantity' => 10, 'price_points' => 5000, 'description' => '10 likes Instagram', 'sort_order' => 3],

            // WhatsApp
            ['platform' => 'whatsapp', 'service_type' => 'abonnes', 'label' => '100 Abonnés Chaîne WhatsApp', 'quantity' => 100, 'price_points' => 500, 'description' => '100 abonnés à votre chaîne WhatsApp', 'sort_order' => 1],
            ['platform' => 'whatsapp', 'service_type' => 'reactions', 'label' => '1000 Réactions Publication WhatsApp', 'quantity' => 1000, 'price_points' => 2000, 'description' => '1000 réactions sur une publication de votre chaîne', 'sort_order' => 2],
        ];

        foreach ($services as $service) {
            DB::table('egb_services')->insert(array_merge($service, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
