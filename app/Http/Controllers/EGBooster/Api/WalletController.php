<?php

namespace App\Http\Controllers\EGBooster\Api;

use App\Http\Controllers\Controller;
use App\Models\EGBooster\EgbTransaction;
use App\Models\EGBooster\EgbPayment;
use App\Services\EGBooster\WalletService;
use App\Services\EGBooster\FreemopayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    protected WalletService $walletService;
    protected FreemopayService $freemopayService;

    public function __construct(WalletService $walletService, FreemopayService $freemopayService)
    {
        $this->walletService = $walletService;
        $this->freemopayService = $freemopayService;
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
                'equivalent_fcfa' => $user->points_balance,
            ],
        ]);
    }

    /**
     * Initialiser un dépôt (via Freemopay)
     */
    public function deposit(Request $request)
    {
        $validated = $request->validate([
            'amount_fcfa' => 'required|integer|min:200',
            'payment_method' => 'required|in:momo,om',
            'phone_number' => 'required|string|regex:/^237[0-9]{9}$/', // Format: 237XXXXXXXXX
        ]);

        $user = $request->user();
        $externalId = 'EGB-' . Str::uuid();

        try {
            // Créer l'enregistrement de paiement
            $payment = EgbPayment::create([
                'user_id' => $user->id,
                'external_id' => $externalId,
                'amount_fcfa' => $validated['amount_fcfa'],
                'phone_number' => $validated['phone_number'],
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
            ]);

            // Initialiser le paiement avec Freemopay
            $response = $this->freemopayService->initPayment(
                $validated['phone_number'],
                $validated['amount_fcfa'],
                $externalId,
                "Dépôt EGBooster - {$validated['amount_fcfa']} FCFA"
            );

            // Mettre à jour avec la référence Freemopay
            $payment->update([
                'freemopay_reference' => $response['reference'] ?? null,
                'metadata' => $response,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Paiement initié! Validez la demande sur votre téléphone ({$validated['phone_number']})",
                'data' => [
                    'payment_id' => $payment->id,
                    'external_id' => $externalId,
                    'freemopay_reference' => $response['reference'] ?? null,
                    'status' => 'pending',
                    'amount' => $validated['amount_fcfa'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'initialisation du paiement: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Vérifier le statut d'un paiement
     */
    public function checkPaymentStatus(Request $request, $externalId)
    {
        $user = $request->user();

        $payment = EgbPayment::where('external_id', $externalId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($payment->freemopay_reference) {
            try {
                $status = $this->freemopayService->checkPaymentStatus($payment->freemopay_reference);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'status' => $payment->status,
                        'freemopay_status' => $status['status'] ?? null,
                        'amount' => $payment->amount_fcfa,
                    ],
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la vérification du statut',
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $payment->status,
                'amount' => $payment->amount_fcfa,
            ],
        ]);
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
}
