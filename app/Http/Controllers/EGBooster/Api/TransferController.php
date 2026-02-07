<?php
namespace App\Http\Controllers\EGBooster;

use App\Models\EGBooster\EgbUser;
use App\Models\EGBooster\EgbTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class TransferController extends Controller
{
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_telephone' => 'required|string',
            'points' => 'required|integer|min:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $sender = auth()->user();
        $points = $request->points;

        // Vérifier que l'utilisateur a assez de points
        if ($sender->points_balance < $points) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant',
            ], 400);
        }

        // Trouver le destinataire
        $recipient = EgbUser::where('telephone', $request->recipient_telephone)->first();

        if (!$recipient) {
            return response()->json([
                'success' => false,
                'message' => 'Destinataire introuvable',
            ], 404);
        }

        // Vérifier qu'on ne s'envoie pas à soi-même
        if ($sender->id === $recipient->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas vous transférer des points à vous-même',
            ], 400);
        }

        // Vérifier que le destinataire n'est pas bloqué
        if ($recipient->is_blocked) {
            return response()->json([
                'success' => false,
                'message' => 'Le destinataire est bloqué',
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Sauvegarder les soldes avant
            $senderBalanceBefore = $sender->points_balance;
            $recipientBalanceBefore = $recipient->points_balance;

            // Débiter l'expéditeur
            $sender->points_balance -= $points;
            $sender->save();

            // Créditer le destinataire
            $recipient->points_balance += $points;
            $recipient->save();

            // Générer une référence unique
            $reference = EgbTransaction::generateReference();

            // Transaction pour l'expéditeur (débit)
            EgbTransaction::create([
                'user_id' => $sender->id,
                'type' => 'transfert_envoye',
                'points' => -$points,
                'balance_before' => $senderBalanceBefore,
                'balance_after' => $sender->points_balance,
                'reference' => $reference,
                'description' => "Transfert à {$recipient->prenom}",
                'metadata' => [
                    'recipient_id' => $recipient->id,
                    'recipient_name' => $recipient->prenom,
                    'recipient_telephone' => $recipient->telephone,
                ],
            ]);

            // Transaction pour le destinataire (crédit)
            EgbTransaction::create([
                'user_id' => $recipient->id,
                'type' => 'transfert_recu',
                'points' => $points,
                'balance_before' => $recipientBalanceBefore,
                'balance_after' => $recipient->points_balance,
                'reference' => $reference,
                'description' => "Transfert de {$sender->prenom}",
                'metadata' => [
                    'sender_id' => $sender->id,
                    'sender_name' => $sender->prenom,
                    'sender_telephone' => $sender->telephone,
                ],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Transfert de {$points} points effectué avec succès",
                'data' => [
                    'reference' => $reference,
                    'points' => $points,
                    'recipient' => [
                        'name' => $recipient->prenom,
                        'telephone' => $recipient->telephone,
                    ],
                    'new_balance' => $sender->points_balance,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du transfert: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function findRecipient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telephone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $recipient = EgbUser::where('telephone', $request->telephone)->first();

        if (!$recipient) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable',
            ], 404);
        }

        // Vérifier qu'on ne cherche pas soi-même
        if ($recipient->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas vous transférer des points à vous-même',
            ], 400);
        }

        if ($recipient->is_blocked) {
            return response()->json([
                'success' => false,
                'message' => 'Cet utilisateur est bloqué',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $recipient->id,
                'recipient_name' => $recipient->prenom,
                'telephone' => $recipient->telephone,
            ],
        ]);
    }

    public function history()
    {
        $user = auth()->user();

        $transactions = EgbTransaction::where('user_id', $user->id)
            ->whereIn('type', ['transfert_envoye', 'transfert_recu'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $transfers = $transactions->map(function ($transaction) {
            $metadata = $transaction->metadata ?? [];

            return [
                'reference' => $transaction->reference,
                'direction' => $transaction->type === 'transfert_envoye' ? 'envoyé' : 'reçu',
                'contact' => $transaction->type === 'transfert_envoye'
                    ? ($metadata['recipient_name'] ?? 'Inconnu')
                    : ($metadata['sender_name'] ?? 'Inconnu'),
                'points' => abs($transaction->points),
                'fees' => 0, // Pas de frais pour le moment
                'date' => $transaction->created_at->format('d/m/Y H:i'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transfers,
        ]);
    }
}
