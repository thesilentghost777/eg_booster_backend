<?php

namespace App\Http\Controllers\EGBooster\Api;

use App\Http\Controllers\Controller;
use App\Models\EGBooster\EgbTransaction;
use App\Services\EGBooster\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Obtenir le solde
     */
    public function balance(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'points_balance' => $user->points_balance,
                'equivalent_fcfa' => $user->points_balance, // 1 point = 1 FCFA
            ],
        ]);
    }

    /**
     * Effectuer un dépôt (via API de paiement)
     */
    public function deposit(Request $request)
    {
        $validated = $request->validate([
            'amount_fcfa' => 'required|integer|min:500',
            'payment_method' => 'required|in:momo,om',
            'payment_reference' => 'nullable|string',
        ]);

        $user = $request->user();

        try {
            $transaction = $this->walletService->deposit(
                $user,
                $validated['amount_fcfa'],
                "Dépôt via {$this->getPaymentMethodLabel($validated['payment_method'])}",
                [
                    'payment_method' => $validated['payment_method'],
                    'payment_reference' => $validated['payment_reference'] ?? null,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Dépôt de {$validated['amount_fcfa']} FCFA effectué! +{$validated['amount_fcfa']} points 🎉",
                'data' => [
                    'transaction' => $this->formatTransaction($transaction),
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
     * Historique des transactions
     */
    public function transactions(Request $request)
    {
        $user = $request->user();
        $type = $request->query('type');

        $query = EgbTransaction::where('user_id', $user->id)
            ->orderByDesc('created_at');

        if ($type) {
            $query->where('type', $type);
        }

        $transactions = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $transactions->getCollection()->map(fn($t) => $this->formatTransaction($t)),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    private function formatTransaction(EgbTransaction $t): array
    {
        return [
            'id' => $t->id,
            'type' => $t->type,
            'type_label' => $this->getTypeLabel($t->type),
            'amount_fcfa' => $t->amount_fcfa,
            'points' => $t->points,
            'balance_before' => $t->balance_before,
            'balance_after' => $t->balance_after,
            'reference' => $t->reference,
            'description' => $t->description,
            'created_at' => $t->created_at->format('d/m/Y H:i'),
        ];
    }

    private function getTypeLabel(string $type): string
    {
        return match ($type) {
            'depot' => '💰 Dépôt',
            'achat' => '🛒 Achat',
            'transfert_envoye' => '📤 Transfert envoyé',
            'transfert_recu' => '📥 Transfert reçu',
            'bonus_parrainage' => '🎁 Bonus parrainage',
            'gain_roue' => '🎰 Gain Grande Roue',
            'frais_transfert' => '💸 Frais de transfert',
            'cadeau_bienvenue' => '🎁 Cadeau bienvenue',
            'participation_roue' => '🎡 Participation roue',
            default => $type,
        };
    }

    private function getPaymentMethodLabel(string $method): string
    {
        return match ($method) {
            'momo' => 'MTN Mobile Money',
            'om' => 'Orange Money',
            default => $method,
        };
    }
}
