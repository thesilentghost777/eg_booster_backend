<?php

namespace App\Http\Controllers\EGBooster\Api;

use App\Http\Controllers\Controller;
use App\Models\EGBooster\EgbService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Liste tous les services actifs groupés par plateforme
     */
    public function index(Request $request)
    {
        $platform = $request->query('platform');

        $query = EgbService::active()->orderBy('sort_order');

        if ($platform) {
            $query->byPlatform($platform);
        }

        $services = $query->get();

        if (!$platform) {
            // Grouper par plateforme
            $grouped = $services->groupBy('platform')->map(function ($items, $platform) {
                return [
                    'platform' => $platform,
                    'icon' => $this->getPlatformIcon($platform),
                    'services' => $items->map(fn($s) => $this->formatService($s)),
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $grouped,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $services->map(fn($s) => $this->formatService($s)),
        ]);
    }

    /**
     * Détails d'un service
     */
    public function show(int $id)
    {
        $service = EgbService::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->formatService($service),
        ]);
    }

    /**
     * Liste des plateformes disponibles
     */
    public function platforms()
    {
        $platforms = EgbService::active()
            ->select('platform')
            ->distinct()
            ->get()
            ->map(fn($s) => [
                'name' => $s->platform,
                'icon' => $this->getPlatformIcon($s->platform),
                'label' => ucfirst($s->platform),
            ]);

        return response()->json([
            'success' => true,
            'data' => $platforms,
        ]);
    }

    private function formatService(EgbService $service): array
    {
        return [
            'id' => $service->id,
            'platform' => $service->platform,
            'service_type' => $service->service_type,
            'label' => $service->label,
            'quantity' => $service->quantity,
            'price_points' => $service->price_points,
            'price_fcfa' => $service->price_points, // 1 point = 1 FCFA
            'description' => $service->description,
        ];
    }

    private function getPlatformIcon(string $platform): string
    {
        return match ($platform) {
            'tiktok' => '🎵',
            'facebook' => '📘',
            'youtube' => '▶️',
            'instagram' => '📸',
            'whatsapp' => '💬',
            default => '🌐',
        };
    }
}
