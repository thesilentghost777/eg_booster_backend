<?php

namespace App\Http\Controllers\EGBooster\Api;

use App\Http\Controllers\Controller;
use App\Models\EGBooster\EgbOrder;
use App\Models\EGBooster\EgbService;
use App\Models\EGBooster\EgbSetting;
use App\Services\EGBooster\WalletService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Passer une commande de boost
     */
    /**
 * Passer une commande de boost
 */
public function store(Request $request)
{
    $validated = $request->validate([
        'service_id' => 'required|exists:egb_services,id',
        'link' => 'required|url|max:1000',
        'quantity' => 'nullable|integer|min:1',
    ]);

    $user = $request->user();
    $service = EgbService::findOrFail($validated['service_id']);

    if (!$service->is_active) {
        return response()->json([
            'success' => false,
            'message' => 'Ce service n\'est plus disponible.',
        ], 400);
    }

    // NOUVELLE VÉRIFICATION : Bloquer le service ID 1 si free_views_claimed = true
    if ($service->id == 1 && $user->free_views_claimed) {
        return response()->json([
            'success' => false,
            'message' => 'Vous ne pouvez pas commander ce service car vous avez déjà utilisé votre cadeau de bienvenue.',
        ], 400);
    }

    $quantity = $validated['quantity'] ?? 1;
    $totalPoints = $service->price_points * $quantity;

    if ($user->points_balance < $totalPoints) {
        return response()->json([
            'success' => false,
            'message' => "Solde insuffisant. Vous avez {$user->points_balance} points, il vous faut {$totalPoints} points.",
            'data' => [
                'balance' => $user->points_balance,
                'required' => $totalPoints,
                'deficit' => $totalPoints - $user->points_balance,
            ],
        ], 400);
    }

    // Débiter les points
    $this->walletService->debit(
        $user,
        $totalPoints,
        'achat',
        "Achat: {$service->label} x{$quantity}",
        ['service_id' => $service->id]
    );

    // Créer la commande
    $order = EgbOrder::create([
        'reference' => EgbOrder::generateReference(),
        'user_id' => $user->id,
        'service_id' => $service->id,
        'link' => $validated['link'],
        'quantity' => $service->quantity * $quantity,
        'points_spent' => $totalPoints,
        'status' => 'en_attente',
        'is_free_gift' => false,
    ]);


    return response()->json([
        'success' => true,
        'message' => 'Commande passée avec succès! 🎉',
        'data' => $this->formatOrder($order->load('service')),
    ], 201);
}

    /**
     * Réclamer les 1000 vues gratuites TikTok
     */
    public function claimFreeViews(Request $request)
{
    $validated = $request->validate([
        'link' => 'required|url|max:1000',
    ]);

    $user = $request->user();

    if ($user->free_views_claimed) {
        return response()->json([
            'success' => false,
            'message' => 'Vous avez déjà utilisé votre cadeau de bienvenue.',
        ], 400);
    }

    $freeQuantity = (int) EgbSetting::get('free_views_quantity', 1000);

    // Chercher le service spécial "vues_gratuites"
    $service = EgbService::where('platform', 'tiktok')
        ->where('service_type', 'vues_gratuites')  // Service spécial
        ->first();

    // Si le service spécial n'existe pas, utiliser le premier service TikTok vues
    if (!$service) {
        $service = EgbService::where('platform', 'tiktok')
            ->where('service_type', 'vues')
            ->first();
    }

    if (!$service) {
        return response()->json([
            'success' => false,
            'message' => 'Service temporairement indisponible.',
        ], 500);
    }

    // Créer la commande avec la quantité du paramètre (1000)
    $order = EgbOrder::create([
        'reference' => EgbOrder::generateReference(),
        'user_id' => $user->id,
        'service_id' => $service->id,
        'link' => $validated['link'],
        'quantity' => $freeQuantity, // Toujours 1000 depuis le paramètre
        'points_spent' => 0,
        'status' => 'en_attente',
        'is_free_gift' => true,
    ]);

    $user->update(['free_views_claimed' => true]);

    return response()->json([
        'success' => true,
        'message' => "🎁 Félicitations! {$freeQuantity} vues TikTok gratuites en cours de traitement!",
        'data' => $this->formatOrder($order->load('service')),
    ], 201);
}
    /**
     * Historique des commandes
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->query('status');

        $query = EgbOrder::where('user_id', $user->id)
            ->with('service')
            ->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders->getCollection()->map(fn($o) => $this->formatOrder($o)),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Détail d'une commande
     */
    public function show(Request $request, string $reference)
    {
        $order = EgbOrder::where('reference', $reference)
            ->where('user_id', $request->user()->id)
            ->with('service')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $this->formatOrder($order),
        ]);
    }

    private function formatOrder(EgbOrder $order): array
    {
        return [
            'id' => $order->id,
            'reference' => $order->reference,
            'service' => $order->service ? [
                'platform' => $order->service->platform,
                'label' => $order->service->label,
                'service_type' => $order->service->service_type,
            ] : null,
            'link' => $order->link,
            'quantity' => $order->quantity,
            'points_spent' => $order->points_spent,
            'status' => $order->status,
            'status_label' => $this->getStatusLabel($order->status),
            'is_free_gift' => $order->is_free_gift,
            'admin_notes' => $order->admin_notes,
            'created_at' => $order->created_at->format('d/m/Y H:i'),
        ];
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'en_attente' => '⏳ En attente',
            'en_cours' => '🔄 En cours',
            'termine' => '✅ Terminé',
            'annule' => '❌ Annulé',
            default => $status,
        };
    }
}
