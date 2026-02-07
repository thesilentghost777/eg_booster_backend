<?php

namespace App\Services\EGBooster;

use App\Models\EGBooster\EgbUser;
use App\Models\EGBooster\EgbTransaction;
use App\Models\EGBooster\EgbSetting;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Créditer des points (dépôt FCFA -> points)
     */
    public function deposit(EgbUser $user, int $amountFcfa, string $description = 'Dépôt', array $metadata = []): EgbTransaction
    {
        $minDeposit = EgbSetting::get('min_deposit_fcfa', 500);
        if ($amountFcfa < $minDeposit) {
            throw new \InvalidArgumentException("Le dépôt minimum est de {$minDeposit} FCFA.");
        }

        // 1 FCFA = 1 point
        $points = $amountFcfa;

        return DB::transaction(function () use ($user, $amountFcfa, $points, $description, $metadata) {
            $balanceBefore = $user->points_balance;
            $user->increment('points_balance', $points);

            $transaction = EgbTransaction::create([
                'user_id' => $user->id,
                'type' => 'depot',
                'amount_fcfa' => $amountFcfa,
                'points' => $points,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore + $points,
                'reference' => EgbTransaction::generateReference(),
                'description' => $description,
                'metadata' => $metadata,
            ]);

            // Vérifier et créditer le bonus parrainage si premier dépôt
            $this->checkReferralBonus($user);

            return $transaction;
        });
    }

    /**
     * Débiter des points
     */
    public function debit(EgbUser $user, int $points, string $type, string $description = '', array $metadata = []): EgbTransaction
    {
        if ($user->points_balance < $points) {
            throw new \InvalidArgumentException('Solde insuffisant.');
        }

        return DB::transaction(function () use ($user, $points, $type, $description, $metadata) {
            $balanceBefore = $user->points_balance;
            $user->decrement('points_balance', $points);

            return EgbTransaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount_fcfa' => null,
                'points' => -$points,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore - $points,
                'reference' => EgbTransaction::generateReference(),
                'description' => $description,
                'metadata' => $metadata,
            ]);
        });
    }

    /**
     * Créditer des points (bonus, gain, etc.)
     */
    public function credit(EgbUser $user, int $points, string $type, string $description = '', array $metadata = []): EgbTransaction
    {
        return DB::transaction(function () use ($user, $points, $type, $description, $metadata) {
            $balanceBefore = $user->points_balance;
            $user->increment('points_balance', $points);

            return EgbTransaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount_fcfa' => null,
                'points' => $points,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore + $points,
                'reference' => EgbTransaction::generateReference(),
                'description' => $description,
                'metadata' => $metadata,
            ]);
        });
    }

    /**
     * Vérifier le bonus de parrainage lors du premier dépôt
     */
    private function checkReferralBonus(EgbUser $user): void
    {
        if (!$user->referred_by) return;

        $referral = \App\Models\EGBooster\EgbReferral::where('referred_id', $user->id)->first();
        if (!$referral || $referral->has_deposited) return;

        // Marquer que le filleul a fait un dépôt
        $referral->update(['has_deposited' => true]);

        // Récupérer le parrain
        $referrer = EgbUser::find($user->referred_by);
        if (!$referrer) return;

        // Compter le nombre de filleuls ayant fait un dépôt
        $referralsWithDeposit = \App\Models\EGBooster\EgbReferral::where('referrer_id', $referrer->id)
            ->where('has_deposited', true)
            ->count();

        // Créditer 1000 points si c'est le 5ème filleul qui dépose
        if ($referralsWithDeposit == 5) {
            $bonusAmount = EgbSetting::get('referral_bonus_fifth_deposit', 1000);

            $this->credit(
                $referrer,
                $bonusAmount,
                'bonus_parrainage',
                "Bonus: 5ème filleul ({$user->prenom}) a fait son premier dépôt",
                ['referred_id' => $user->id, 'milestone' => 5]
            );

            $referral->increment('points_earned', $bonusAmount);
        }
    }
}
