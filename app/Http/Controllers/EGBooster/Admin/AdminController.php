<?php

namespace App\Http\Controllers\EGBooster\Admin;

use App\Http\Controllers\Controller;
use App\Models\EGBooster\EgbUser;
use App\Models\EGBooster\EgbOrder;
use App\Models\EGBooster\EgbTransaction;
use App\Models\EGBooster\EgbService;
use App\Models\EGBooster\EgbSetting;
use App\Models\EGBooster\EgbWheelEvent;
use App\Models\EGBooster\EgbSupportTicket;
use App\Models\EGBooster\EgbSupportMessage;
use App\Services\EGBooster\WalletService;
use App\Services\EGBooster\WheelService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // ==================== DASHBOARD ====================

    public function dashboard()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => EgbUser::count(),
                'active_users' => EgbUser::where('is_blocked', false)->count(),
                'blocked_users' => EgbUser::where('is_blocked', true)->count(),
                'total_orders' => EgbOrder::count(),
                'pending_orders' => EgbOrder::where('status', 'en_attente')->count(),
                'in_progress_orders' => EgbOrder::where('status', 'en_cours')->count(),
                'completed_orders' => EgbOrder::where('status', 'termine')->count(),
                'total_deposits_fcfa' => EgbTransaction::where('type', 'depot')->sum('amount_fcfa'),
                'total_points_in_circulation' => EgbUser::sum('points_balance'),
                'open_tickets' => EgbSupportTicket::where('status', '!=', 'ferme')->count(),
                'recent_orders' => EgbOrder::with(['user:id,prenom,telephone', 'service:id,label,platform'])
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->get(),
            ],
        ]);
    }

    // ==================== ORDERS ====================

    public function orders(Request $request)
    {
        $status = $request->query('status');
        $query = EgbOrder::with(['user:id,prenom,telephone', 'service:id,label,platform,service_type'])
            ->orderByDesc('created_at');

        if ($status) $query->where('status', $status);

        return response()->json([
            'success' => true,
            'data' => $query->paginate(30),
        ]);
    }

    public function showOrder(int $id)
    {
        $order = EgbOrder::with(['user', 'service'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    public function updateOrderStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:en_attente,en_cours,termine,annule',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $order = EgbOrder::findOrFail($id);

        // Si annulation, rembourser les points
        if ($validated['status'] === 'annule' && $order->status !== 'annule' && !$order->is_free_gift) {
            $walletService = app(WalletService::class);
            $walletService->credit(
                $order->user,
                $order->points_spent,
                'achat', // type remboursement
                "Remboursement commande #{$order->reference}",
                ['order_id' => $order->id]
            );
        }

        $order->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? $order->admin_notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour.',
            'data' => $order->fresh(),
        ]);
    }

    // ==================== USERS ====================

    public function users(Request $request)
    {
        $query = EgbUser::withCount(['orders', 'referrals'])
            ->orderByDesc('created_at');

        if ($request->query('blocked')) {
            $query->where('is_blocked', true);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(30),
        ]);
    }

    public function showUser(int $id)
    {
        $user = EgbUser::withCount(['orders', 'referrals'])
            ->findOrFail($id);

        $user->load(['orders' => fn($q) => $q->latest()->limit(10), 'orders.service:id,label,platform']);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function toggleBlockUser(int $id)
    {
        $user = EgbUser::findOrFail($id);
        $user->update(['is_blocked' => !$user->is_blocked]);

        return response()->json([
            'success' => true,
            'message' => $user->is_blocked ? 'Utilisateur bloqué.' : 'Utilisateur débloqué.',
            'data' => ['is_blocked' => $user->is_blocked],
        ]);
    }

    public function creditUser(Request $request, int $id)
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $user = EgbUser::findOrFail($id);
        $walletService = app(WalletService::class);

        $walletService->credit(
            $user,
            $validated['points'],
            'cadeau_bienvenue',
            $validated['description'] ?? 'Crédit admin',
        );

        return response()->json([
            'success' => true,
            'message' => "{$validated['points']} points crédités à {$user->prenom}.",
            'data' => ['new_balance' => $user->fresh()->points_balance],
        ]);
    }

    // ==================== SERVICES ====================

    public function services()
    {
        return response()->json([
            'success' => true,
            'data' => EgbService::orderBy('platform')->orderBy('sort_order')->get(),
        ]);
    }

    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|in:tiktok,facebook,youtube,instagram,whatsapp',
            'service_type' => 'required|string|max:50',
            'label' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'price_points' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer',
        ]);

        $service = EgbService::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Service créé.',
            'data' => $service,
        ], 201);
    }

    public function updateService(Request $request, int $id)
    {
        $service = EgbService::findOrFail($id);

        $validated = $request->validate([
            'label' => 'sometimes|string|max:255',
            'quantity' => 'sometimes|integer|min:1',
            'price_points' => 'sometimes|integer|min:1',
            'description' => 'nullable|string|max:500',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $service->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Service mis à jour.',
            'data' => $service->fresh(),
        ]);
    }

    public function deleteService(int $id)
    {
        $service = EgbService::findOrFail($id);

        if ($service->orders()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce service a des commandes associées et ne peut pas être supprimé.',
            ], 400);
        }

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service supprimé.',
        ]);
    }

    // ==================== WHEEL ====================

    public function wheelEvents()
    {
        return response()->json([
            'success' => true,
            'data' => EgbWheelEvent::with('winner:id,prenom')
                ->withCount('participations')
                ->orderByDesc('scheduled_at')
                ->paginate(20),
        ]);
    }

    public function createWheelEvent(Request $request)
    {
        $validated = $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);

        $event = EgbWheelEvent::create([
            'scheduled_at' => $validated['scheduled_at'],
            'status' => 'en_attente',
            'total_pot' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Événement créé.',
            'data' => $event,
        ], 201);
    }

    public function drawWheelWinner(Request $request, int $id)
    {
        $event = EgbWheelEvent::findOrFail($id);
        $manualWinnerId = $request->input('winner_id');

        $wheelService = app(WheelService::class);

        try {
            $winner = $wheelService->drawWinner($event, $manualWinnerId);

            return response()->json([
                'success' => true,
                'message' => "🎉 {$winner->prenom} a gagné {$event->total_pot} points!",
                'data' => [
                    'winner' => ['id' => $winner->id, 'prenom' => $winner->prenom],
                    'total_pot' => $event->total_pot,
                    'is_manual' => (bool) $manualWinnerId,
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // ==================== SETTINGS ====================

    public function settings()
    {
        return response()->json([
            'success' => true,
            'data' => EgbSetting::all()->groupBy('group'),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
        ]);

        foreach ($validated['settings'] as $setting) {
            $existing = EgbSetting::where('key', $setting['key'])->first();
            if ($existing) {
                EgbSetting::set($setting['key'], $setting['value'], $existing->type, $existing->group, $existing->label);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Paramètres mis à jour.',
        ]);
    }

    // ==================== SUPPORT ====================

    public function supportTickets(Request $request)
    {
        $status = $request->query('status');
        $query = EgbSupportTicket::with(['user:id,prenom,telephone', 'latestMessage'])
            ->orderByDesc('updated_at');

        if ($status) $query->where('status', $status);

        return response()->json([
            'success' => true,
            'data' => $query->paginate(20),
        ]);
    }

    public function supportTicketMessages(string $reference)
    {
        $ticket = EgbSupportTicket::where('reference', $reference)
            ->with('user:id,prenom')
            ->firstOrFail();

        $messages = EgbSupportMessage::where('ticket_id', $ticket->id)
            ->orderBy('created_at')
            ->get();

        // Marquer comme lus
        EgbSupportMessage::where('ticket_id', $ticket->id)
            ->where('sender_type', 'user')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'data' => [
                'ticket' => $ticket,
                'messages' => $messages,
            ],
        ]);
    }

    public function supportReply(Request $request, string $reference)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $ticket = EgbSupportTicket::where('reference', $reference)->firstOrFail();

        EgbSupportMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'admin',
            'sender_id' => $request->user()->id ?? 0,
            'message' => $validated['message'],
        ]);

        $ticket->update(['status' => 'en_cours']);

        return response()->json([
            'success' => true,
            'message' => 'Réponse envoyée.',
        ]);
    }

    public function closeTicket(string $reference)
    {
        $ticket = EgbSupportTicket::where('reference', $reference)->firstOrFail();
        $ticket->update(['status' => 'ferme']);

        return response()->json([
            'success' => true,
            'message' => 'Ticket fermé.',
        ]);
    }
}
