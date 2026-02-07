<?php

namespace App\Services\EGBooster;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FreemopayService
{
    private string $baseUrl;
    private string $appKey;
    private string $secretKey;
    private string $webhookUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.freemopay.base_url');
        $this->appKey = config('services.freemopay.app_key');
        $this->secretKey = config('services.freemopay.secret_key');
        $this->webhookUrl = config('services.freemopay.webhook_url');
    }

    /**
     * Initialiser un paiement
     */
    public function initPayment(string $phoneNumber, int $amount, string $externalId, string $description = 'Dépôt'): array
    {
        try {
            $response = Http::withBasicAuth($this->appKey, $this->secretKey)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->baseUrl}/api/v2/payment", [
                    'payer' => $phoneNumber,
                    'amount' => (string) $amount,
                    'externalId' => $externalId,
                    'description' => $description,
                    'callback' => $this->webhookUrl,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Freemopay init payment error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \Exception('Erreur lors de l\'initialisation du paiement');
        } catch (\Exception $e) {
            Log::error('Freemopay exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Vérifier le statut d'un paiement
     */
    public function checkPaymentStatus(string $reference): array
    {
        try {
            $response = Http::withBasicAuth($this->appKey, $this->secretKey)
                ->get("{$this->baseUrl}/api/v2/payment/{$reference}");

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('Erreur lors de la vérification du statut');
        } catch (\Exception $e) {
            Log::error('Freemopay status check exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }
}
