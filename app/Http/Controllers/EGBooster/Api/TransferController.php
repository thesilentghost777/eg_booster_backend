<?php

namespace App\Http\Controllers\EGBooster\Api;

use App\Http\Controllers\Controller;
use App\Models\EGBooster\EgbTransfer;
use App\Models\EGBooster\EgbUser;
use App\Models\EGBooster\EgbSetting;
use App\Services\EGBooster\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Rechercher un destinataire par numéro de téléphone
     */
    public function findRecipient(Request $request)
    {
        $request->validate([
            'telephone' => 'required|string',
        ]);

        $recipient = EgbUser::where('telephone', $request->telephone)->first();

        if (!$recipient) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun utilisateur trouvé avec ce numéro.',
            ], 404);
        }

        if ($recipient->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas vous transférer à vous-même.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $recipient->id,
                'prenom' => $recipient->prenom,
                'telephone' => substr($recipient->telephone, 0, 3) . '****' . substr($recipient->telephone, -2),
            ],
        ]);
    }

    /**
     * Effectuer un transfert
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'recipient_telephone' => 'required|string',
            'points' => 'required|integer|min:1',
        ]);

        $sender = $request->user();
        $recipient = EgbUser::where('telephone', $validated['recipient_telephone'])->first();

        if (!$recipient) {
            return response()->json([
                'success' => false,
                'message' => 'Destinataire introuvable.',
            ], 404);
        }

        if ($recipient->id === $sender->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas vous transférer à vous-même.',
            ], 400);
        }

        $feePercent = (int) EgbSetting::get('transfer_fee_percent', 2);
        $fees = (int) ceil($validated['points'] * $feePercent / 100);
        $totalDebit = $validated['points'] + $fees;
        $netPoints = $validated['points'];

        if ($sender->points_balance < $totalDebit) {
            return response()->json([
                'success' => false,
                'message' => "Solde insuffisant. Il vous faut {$totalDebit} points (dont {$fees} points de frais).",
            ], 400);
        }

        $transfer = DB::transaction(function () use ($sender, $recipient, $validated, $fees, $netPoints, $totalDebit) {
            // Débiter l'expéditeur
            $this->walletService->debit(
                $sender, $totalDebit, 'transfert_envoye',
                "Transfert à {$recipient->prenom}",
                ['recipient_id' => $recipient->id]
            );

            // Créditer le destinataire
            $this->walletService->credit(
                $recipient, $netPoints, 'transfert_recu',
                "Transfert reçu de {$sender->prenom}",
                ['sender_id' => $sender->id]
            );

            // Créditer les frais à l'admin
            $adminId = (int) EgbSetting::get('admin_account_id', 1);
            $admin = EgbUser::find($adminId);
            if ($admin && $fees > 0) {
                $this->walletService->credit(
                    $admin, $fees, 'frais_transfert',
                    "Frais transfert: {$sender->prenom} → {$recipient->prenom}",
                    ['sender_id' => $sender->id, 'recipient_id' => $recipient->id]
                );
            }

            return EgbTransfer::create([
                'reference' => EgbTransfer::generateReference(),
                'sender_id' => $sender->id,
                'receiver_id' => $recipient->id,
                'points' => $validated['points'],
                'fees_points' => $fees,
                'net_points' => $netPoints,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => "Transfert de {$netPoints} points effectué à {$recipient->prenom}! 💸",
            'data' => [
                'reference' => $transfer->reference,
                'points_envoyes' => $validated['points'],
                'frais' => $fees,
                'total_debite' => $totalDebit,
                'new_balance' => $sender->fresh()->points_balance,
            ],
        ]);
    }

    /**
     * Historique des transferts
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $transfers = EgbTransfer::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->with(['sender:id,prenom', 'receiver:id,prenom'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $transfers->getCollection()->map(function ($t) use ($user) {
                $isSender = $t->sender_id === $user->id;
                return [
                    'reference' => $t->reference,
                    'direction' => $isSender ? 'envoyé' : 'reçu',
                    'contact' => $isSender ? $t->receiver->prenom : $t->sender->prenom,
                    'points' => $isSender ? -($t->points + $t->fees_points) : $t->net_points,
                    'fees' => $isSender ? $t->fees_points : 0,
                    'date' => $t->created_at->format('d/m/Y H:i'),
                ];
            }),
            'pagination' => [
                'current_page' => $transfers->currentPage(),
                'last_page' => $transfers->lastPage(),
                'total' => $transfers->total(),
            ],
        ]);
    }
}
