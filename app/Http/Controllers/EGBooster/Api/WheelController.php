<?php

namespace App\Http\Controllers\EGBooster\Api;

use App\Http\Controllers\Controller;
use App\Services\EGBooster\WheelService;
use Illuminate\Http\Request;

class WheelController extends Controller
{
    protected WheelService $wheelService;

    public function __construct(WheelService $wheelService)
    {
        $this->wheelService = $wheelService;
    }

    /**
     * Événement actif (compte à rebours)
     */
    public function current()
    {
        $event = $this->wheelService->getCurrentEvent();

        if (!$event) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Aucun événement prévu pour le moment.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $event->id,
                'scheduled_at' => $event->scheduled_at->toIso8601String(),
                'total_pot' => $event->total_pot,
                'participants_count' => $event->participations_count,
                'status' => $event->status,
                'countdown_seconds' => max(0, now()->diffInSeconds($event->scheduled_at, false)),
            ],
        ]);
    }

    /**
     * Participer à la roue
     */
    public function participate(Request $request)
    {
        $user = $request->user();

        try {
            $participation = $this->wheelService->participate($user);

            return response()->json([
                'success' => true,
                'message' => '🎡 Vous participez à la Grande Roue! Bonne chance!',
                'data' => [
                    'points_bet' => $participation->points_bet,
                    'new_balance' => $user->fresh()->points_balance,
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Historique des tirages
     */
    public function history()
    {
        $events = $this->wheelService->getHistory();

        return response()->json([
            'success' => true,
            'data' => $events->map(fn($e) => [
                'id' => $e->id,
                'date' => $e->scheduled_at->format('d/m/Y'),
                'total_pot' => $e->total_pot,
                'participants_count' => $e->participations_count,
                'winner' => $e->winner ? $e->winner->prenom : null,
            ]),
        ]);
    }
}
