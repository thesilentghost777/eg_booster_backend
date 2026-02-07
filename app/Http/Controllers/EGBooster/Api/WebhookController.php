<?php

namespace App\Http\Controllers\EGBooster\Api;

use App\Http\Controllers\Controller;
use App\Models\EGBooster\EgbPayment;
use App\Services\EGBooster\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Webhook Freemopay
     */
    public function freemopay(Request $request)
    {
        Log::info('Freemopay webhook received', $request->all());

        $data = $request->validate([
            'status' => 'required|in:SUCCESS,FAILED',
            'reference' => 'required|string',
            'amount' => 'required|numeric',
            'externalId' => 'required|string',
            'message' => 'nullable|string',
        ]);

        try {
            $payment = EgbPayment::where('external_id', $data['externalId'])->first();

            if (!$payment) {
                Log::error('Payment not found for webhook', ['externalId' => $data['externalId']]);
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            // Si déjà traité, ignorer
            if ($payment->status !== 'pending') {
                Log::info('Payment already processed', ['externalId' => $data['externalId'], 'status' => $payment->status]);
                return response()->json(['success' => true, 'message' => 'Already processed'], 200);
            }

            if ($data['status'] === 'SUCCESS') {
                // Créditer le compte
                $transaction = $this->walletService->deposit(
                    $payment->user,
                    $payment->amount_fcfa,
                    "Dépôt via " . strtoupper($payment->payment_method),
                    [
                        'payment_method' => $payment->payment_method,
                        'freemopay_reference' => $data['reference'],
                        'external_id' => $data['externalId'],
                    ]
                );

                $payment->update([
                    'status' => 'success',
                    'freemopay_reference' => $data['reference'],
                ]);

                Log::info('Payment success', ['externalId' => $data['externalId'], 'amount' => $payment->amount_fcfa]);
            } else {
                // Marquer comme échoué
                $payment->update([
                    'status' => 'failed',
                    'failure_message' => $data['message'] ?? 'Transaction annulée',
                    'freemopay_reference' => $data['reference'],
                ]);

                Log::warning('Payment failed', ['externalId' => $data['externalId'], 'message' => $data['message']]);
            }

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'externalId' => $data['externalId'],
                'error' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'Processing error'], 500);
        }
    }
}
