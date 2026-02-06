<?php

namespace App\Services\EGBooster;

use App\Models\EGBooster\EgbUser;
use App\Models\EGBooster\EgbReferral;
use App\Models\EGBooster\EgbSetting;

class ReferralService
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Traiter le parrainage lors de l'inscription
     */
    public function processReferral(EgbUser $newUser, ?string $referralCode): void
    {
        if (empty($referralCode)) {
            $referralCode = EgbSetting::get('default_referral_code', 'EGBOOST');
        }

        $referrer = EgbUser::where('referral_code', $referralCode)->first();
        if (!$referrer || $referrer->id === $newUser->id) return;

        // Lier le filleul au parrain
        $newUser->update(['referred_by' => $referrer->id]);

        // Créer la relation de parrainage
        $bonusNoDeposit = EgbSetting::get('referral_bonus_no_deposit', 1);

        EgbReferral::create([
            'referrer_id' => $referrer->id,
            'referred_id' => $newUser->id,
            'has_deposited' => false,
            'points_earned' => $bonusNoDeposit,
        ]);

        // Créditer le bonus inscription (sans dépôt)
        $this->walletService->credit(
            $referrer,
            $bonusNoDeposit,
            'bonus_parrainage',
            "Nouveau filleul inscrit: {$newUser->prenom}",
            ['referred_id' => $newUser->id]
        );
    }

    /**
     * Obtenir les statistiques de parrainage d'un utilisateur
     */
    public function getStats(EgbUser $user): array
    {
        $referrals = EgbReferral::where('referrer_id', $user->id)->get();

        return [
            'code' => $user->referral_code,
            'total_filleuls' => $referrals->count(),
            'filleuls_avec_depot' => $referrals->where('has_deposited', true)->count(),
            'filleuls_sans_depot' => $referrals->where('has_deposited', false)->count(),
            'total_points_gagnes' => $referrals->sum('points_earned'),
        ];
    }

    /**
     * Obtenir la liste des filleuls
     */
    public function getFilleuls(EgbUser $user)
    {
        return EgbReferral::where('referrer_id', $user->id)
            ->with('referred:id,prenom,telephone,created_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->referred->id,
                    'prenom' => $r->referred->prenom,
                    'telephone' => substr($r->referred->telephone, 0, 3) . '****' . substr($r->referred->telephone, -2),
                    'inscrit_le' => $r->created_at->format('d/m/Y'),
                    'a_depose' => $r->has_deposited,
                    'points_gagnes' => $r->points_earned,
                ];
            });
    }
}
