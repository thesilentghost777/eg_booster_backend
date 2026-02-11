<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EgbServiceSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $services = [
            // CADEAU DE BIENVENUE (toujours en premier)
            [
                'platform' => 'tiktok',
                'service_type' => 'vues',
                'label' => '1000 Vues TikTok (Cadeau)',
                'quantity' => 1000,
                'price_points' => 0,
                'description' => 'Cadeau de bienvenue: 1000 vues TikTok gratuites',
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // ========== TIKTOK ==========
            [
                'platform' => 'tiktok',
                'service_type' => 'vues',
                'label' => '10000 Vues TikTok Monétisables',
                'quantity' => 10000,
                'price_points' => 168,
                'description' => '10000 vues monétisables pour TikTok',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'tiktok',
                'service_type' => 'likes',
                'label' => '1000 J\'aime TikTok',
                'quantity' => 1000,
                'price_points' => 295,
                'description' => '1000 j\'aime sur votre vidéo TikTok',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'tiktok',
                'service_type' => 'likes',
                'label' => '100 Likes Italie TikTok',
                'quantity' => 100,
                'price_points' => 35,
                'description' => '100 likes provenant d\'Italie',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'tiktok',
                'service_type' => 'favoris',
                'label' => '100 Favoris TikTok',
                'quantity' => 100,
                'price_points' => 23,
                'description' => '100 ajouts aux favoris',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'tiktok',
                'service_type' => 'partages',
                'label' => '100 Partages TikTok',
                'quantity' => 100,
                'price_points' => 225,
                'description' => '100 partages de votre vidéo',
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'tiktok',
                'service_type' => 'commentaires',
                'label' => '100 Commentaires TikTok',
                'quantity' => 100,
                'price_points' => 160,
                'description' => '100 commentaires sur votre vidéo',
                'is_active' => true,
                'sort_order' => 6,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'tiktok',
                'service_type' => 'abonnes',
                'label' => '100 Abonnés TikTok (100% Réels)',
                'quantity' => 100,
                'price_points' => 250,
                'description' => '100 abonnés 100% réels',
                'is_active' => true,
                'sort_order' => 7,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'tiktok',
                'service_type' => 'partages',
                'label' => '1000 Partages Flash TikTok',
                'quantity' => 1000,
                'price_points' => 67,
                'description' => '1000 partages flash',
                'is_active' => true,
                'sort_order' => 8,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'tiktok',
                'service_type' => 'abonnes',
                'label' => '100 Abonnés Allemagne TikTok',
                'quantity' => 100,
                'price_points' => 360,
                'description' => '100 abonnés provenant d\'Allemagne',
                'is_active' => true,
                'sort_order' => 9,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'tiktok',
                'service_type' => 'vues',
                'label' => '1000 Vues TikTok Canada',
                'quantity' => 1000,
                'price_points' => 25,
                'description' => '1000 vues provenant du Canada',
                'is_active' => true,
                'sort_order' => 10,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'tiktok',
                'service_type' => 'vues',
                'label' => '1000 Vues TikTok Cameroun',
                'quantity' => 1000,
                'price_points' => 25,
                'description' => '1000 vues provenant du Cameroun',
                'is_active' => true,
                'sort_order' => 11,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'tiktok',
                'service_type' => 'vues',
                'label' => '100 Vues TikTok France',
                'quantity' => 100,
                'price_points' => 40,
                'description' => '100 vues provenant de France',
                'is_active' => true,
                'sort_order' => 12,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'tiktok',
                'service_type' => 'vues',
                'label' => '100 Vues TikTok Allemagne',
                'quantity' => 100,
                'price_points' => 27,
                'description' => '100 vues provenant d\'Allemagne',
                'is_active' => true,
                'sort_order' => 13,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'tiktok',
                'service_type' => 'vues',
                'label' => '100 Vues Live TikTok',
                'quantity' => 100,
                'price_points' => 450,
                'description' => '100 vues sur votre live TikTok',
                'is_active' => true,
                'sort_order' => 14,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'tiktok',
                'service_type' => 'likes',
                'label' => '1000 Likes Live TikTok',
                'quantity' => 1000,
                'price_points' => 25,
                'description' => '1000 likes pendant votre live',
                'is_active' => true,
                'sort_order' => 15,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // ========== YOUTUBE ==========
            [
                'platform' => 'youtube',
                'service_type' => 'vues',
                'label' => '100 Vues YouTube (15-30 min)',
                'quantity' => 100,
                'price_points' => 2000,
                'description' => '100 vues pour vidéo entre 15 et 30 minutes',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'youtube',
                'service_type' => 'vues',
                'label' => '100 Vues YouTube (30-40 min)',
                'quantity' => 100,
                'price_points' => 3000,
                'description' => '100 vues pour vidéo entre 30 et 40 minutes',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'youtube',
                'service_type' => 'vues',
                'label' => '100 Vues YouTube (+40 min)',
                'quantity' => 100,
                'price_points' => 5000,
                'description' => '100 vues pour vidéo de plus de 40 minutes',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'youtube',
                'service_type' => 'likes',
                'label' => '1000 J\'aime YouTube',
                'quantity' => 1000,
                'price_points' => 1300,
                'description' => '1000 j\'aime sur votre vidéo YouTube',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'youtube',
                'service_type' => 'likes',
                'label' => '1000 J\'aime Short YouTube',
                'quantity' => 1000,
                'price_points' => 1225,
                'description' => '1000 j\'aime sur un short YouTube',
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'youtube',
                'service_type' => 'vues',
                'label' => '1000 Vues Short + Likes Bonus',
                'quantity' => 1000,
                'price_points' => 1625,
                'description' => '1000 vues sur short avec likes bonus',
                'is_active' => true,
                'sort_order' => 6,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'youtube',
                'service_type' => 'abonnes',
                'label' => '100 Abonnés YouTube',
                'quantity' => 100,
                'price_points' => 3400,
                'description' => '100 abonnés YouTube',
                'is_active' => true,
                'sort_order' => 7,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'youtube',
                'service_type' => 'vues',
                'label' => '1000 Vues YouTube Suggérées',
                'quantity' => 1000,
                'price_points' => 2100,
                'description' => '1000 vues suggérées YouTube',
                'is_active' => true,
                'sort_order' => 8,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'youtube',
                'service_type' => 'commentaires',
                'label' => '1000 Commentaires YouTube',
                'quantity' => 1000,
                'price_points' => 2800,
                'description' => '1000 commentaires sur votre vidéo',
                'is_active' => true,
                'sort_order' => 9,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'youtube',
                'service_type' => 'vues',
                'label' => '10000 Vues YouTube Monétisables Premium',
                'quantity' => 10000,
                'price_points' => 22000,
                'description' => '10000 vues YouTube monétisables avec boost du contenu vers utilisateurs réels',
                'is_active' => true,
                'sort_order' => 10,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // ========== FACEBOOK ==========
            [
                'platform' => 'facebook',
                'service_type' => 'membres',
                'label' => '1000 Membres Groupe Facebook',
                'quantity' => 1000,
                'price_points' => 500,
                'description' => '1000 membres pour votre groupe Facebook',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'facebook',
                'service_type' => 'vues',
                'label' => '1000 Vues Story Facebook',
                'quantity' => 1000,
                'price_points' => 375,
                'description' => '1000 vues sur votre story Facebook',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'facebook',
                'service_type' => 'reactions',
                'label' => '1000 Réactions Story Facebook',
                'quantity' => 1000,
                'price_points' => 450,
                'description' => '1000 réactions sur votre story',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'facebook',
                'service_type' => 'likes',
                'label' => '1000 J\'aime Vidéo Facebook',
                'quantity' => 1000,
                'price_points' => 450,
                'description' => '1000 j\'aime sur votre vidéo Facebook',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'facebook',
                'service_type' => 'commentaires',
                'label' => '100 Commentaires Vidéo Facebook',
                'quantity' => 100,
                'price_points' => 355,
                'description' => '100 commentaires sur votre vidéo',
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'facebook',
                'service_type' => 'vues',
                'label' => '1000 Vues Facebook (Tous liens)',
                'quantity' => 1000,
                'price_points' => 56,
                'description' => 'Vues Facebook pour tous les types de liens',
                'is_active' => true,
                'sort_order' => 6,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'facebook',
                'service_type' => 'partages',
                'label' => '100 Partages Facebook',
                'quantity' => 100,
                'price_points' => 100,
                'description' => '100 partages de votre publication',
                'is_active' => true,
                'sort_order' => 7,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'facebook',
                'service_type' => 'reactions',
                'label' => '100 Réactions Post Facebook',
                'quantity' => 100,
                'price_points' => 275,
                'description' => '100 réactions sur votre post',
                'is_active' => true,
                'sort_order' => 8,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'facebook',
                'service_type' => 'likes',
                'label' => '1000 J\'aime Publication Facebook',
                'quantity' => 1000,
                'price_points' => 280,
                'description' => '1000 j\'aime sur votre publication',
                'is_active' => true,
                'sort_order' => 9,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'facebook',
                'service_type' => 'abonnes',
                'label' => '1000 J\'aime et Abonnés Page Facebook',
                'quantity' => 1000,
                'price_points' => 850,
                'description' => 'J\'aime et abonnés pour votre page Facebook',
                'is_active' => true,
                'sort_order' => 10,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // ========== INSTAGRAM ==========
            [
                'platform' => 'instagram',
                'service_type' => 'vues',
                'label' => '100 Vues Reel Instagram',
                'quantity' => 100,
                'price_points' => 1000,
                'description' => '100 vues sur votre Reel Instagram',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'instagram',
                'service_type' => 'abonnes',
                'label' => '50 Abonnés Instagram',
                'quantity' => 50,
                'price_points' => 100000,
                'description' => '50 abonnés Instagram',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'instagram',
                'service_type' => 'likes',
                'label' => '10 Likes Instagram',
                'quantity' => 10,
                'price_points' => 5000,
                'description' => '10 likes Instagram',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // ========== WHATSAPP ==========
            [
                'platform' => 'whatsapp',
                'service_type' => 'abonnes',
                'label' => '100 Abonnés Chaîne WhatsApp',
                'quantity' => 100,
                'price_points' => 500,
                'description' => '100 abonnés à votre chaîne WhatsApp',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'platform' => 'whatsapp',
                'service_type' => 'reactions',
                'label' => '1000 Réactions Publication WhatsApp',
                'quantity' => 1000,
                'price_points' => 2000,
                'description' => '1000 réactions sur une publication de votre chaîne',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];

        DB::table('egb_services')->insert($services);
    }
}
