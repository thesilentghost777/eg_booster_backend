<?php

namespace App\Services\EGBooster;

use App\Models\EGBooster\EgbWheelEvent;
use App\Models\EGBooster\EgbWheelParticipation;
use App\Models\EGBooster\EgbUser;
use App\Models\EGBooster\EgbSetting;
use Illuminate\Support\Facades\DB;

class WheelService
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Participer à la roue de la semaine
     */
    public function participate(EgbUser $user): EgbWheelParticipation
    {
        $event = EgbWheelEvent::active()->first();
        if (!$event) {
            throw new \InvalidArgumentException("Aucun événement de roue actif pour le moment.");
        }

        // Vérifier si déjà participant
        $existing = EgbWheelParticipation::where('wheel_event_id', $event->id)
            ->where('user_id', $user->id)->first();
        if ($existing) {
            throw new \InvalidArgumentException("Vous participez déjà à cette roue.");
        }

        $pointsBet = (int) EgbSetting::get('wheel_participation_points', 1);

        if ($user->points_balance < $pointsBet) {
            throw new \InvalidArgumentException("Solde insuffisant pour participer.");
        }

        return DB::transaction(function () use ($user, $event, $pointsBet) {
            // Débiter le point
            $this->walletService->debit(
                $user, $pointsBet, 'participation_roue',
                "Participation à la Grande Roue #{$event->id}",
                ['wheel_event_id' => $event->id]
            );

            // Ajouter au pot
            $event->increment('total_pot', $pointsBet);

            return EgbWheelParticipation::create([
                'wheel_event_id' => $event->id,
                'user_id' => $user->id,
                'points_bet' => $pointsBet,
            ]);
        });
    }

    /**
     * Tirer le gagnant de la roue
     */
    public function drawWinner(EgbWheelEvent $event, ?int $manualWinnerId = null): EgbUser
    {
        if ($event->status === 'termine') {
            throw new \InvalidArgumentException("Cet événement est déjà terminé.");
        }

        $participations = $event->participations()->with('user')->get();
        if ($participations->isEmpty()) {
            throw new \InvalidArgumentException("Aucun participant pour cet événement.");
        }

        return DB::transaction(function () use ($event, $participations, $manualWinnerId) {
            $winner = null;

            if ($manualWinnerId) {
                // Gagnant choisi par l'admin
                $winner = $participations->firstWhere('user_id', $manualWinnerId)?->user;
                if (!$winner) {
                    throw new \InvalidArgumentException("L'utilisateur spécifié ne participe pas à cet événement.");
                }
                $event->is_manual_winner = true;
            } else {
                // Tirage aléatoire
                $winner = $participations->random()->user;
                $event->is_manual_winner = false;
            }

            // Créditer le gagnant
            $this->walletService->credit(
                $winner,
                $event->total_pot,
                'gain_roue',
                "🎉 Gagnant de la Grande Roue! Pot: {$event->total_pot} points",
                ['wheel_event_id' => $event->id]
            );

            $event->update([
                'status' => 'termine',
                'winner_id' => $winner->id,
                'is_manual_winner' => $event->is_manual_winner,
            ]);

            return $winner;
        });
    }

    /**
     * Créer l'événement de la semaine
     */
    public function createWeeklyEvent(): EgbWheelEvent
    {
        // Prochain vendredi 20h
        $nextFriday = now()->next('Friday')->setTime(20, 0, 0);

        return EgbWheelEvent::create([
            'scheduled_at' => $nextFriday,
            'status' => 'en_attente',
            'total_pot' => 0,
        ]);
    }

    /**
     * Obtenir l'événement actif
     */
    public function getCurrentEvent(): ?EgbWheelEvent
    {
        return EgbWheelEvent::active()
            ->withCount('participations')
            ->first();
    }

    /**
     * Historique des événements
     */
    public function getHistory(int $limit = 10)
    {
        return EgbWheelEvent::where('status', 'termine')
            ->with('winner:id,prenom')
            ->withCount('participations')
            ->orderByDesc('scheduled_at')
            ->limit($limit)
            ->get();
    }
}
