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
        \Log::info('=== ADMIN DASHBOARD REQUEST ===');

        try {
            $stats = [
                'total_users' => EgbUser::count(),
                'total_orders' => EgbOrder::count(),
                'total_deposits_fcfa' => EgbTransaction::where('type', 'depot')->sum('amount_fcfa'),
                'points_in_circulation' => EgbUser::sum('points_balance'),
                'pending_orders' => EgbOrder::where('status', 'en_attente')->count(),
                'in_progress_orders' => EgbOrder::where('status', 'en_cours')->count(),
                'completed_orders' => EgbOrder::where('status', 'termine')->count(),
                'open_tickets' => EgbSupportTicket::where('status', '!=', 'ferme')->count(),
            ];

            \Log::info('Stats calculated:', $stats);

            $recentOrders = EgbOrder::with(['user:id,prenom,telephone', 'service:id,label,platform,service_type'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'reference' => $order->reference,
                        'service' => [
                            'platform' => $order->service->platform,
                            'label' => $order->service->label,
                            'service_type' => $order->service->service_type,
                        ],
                        'link' => $order->link,
                        'quantity' => $order->quantity,
                        'points_spent' => $order->points_spent,
                        'status' => $order->status,
                        'status_label' => $this->getStatusLabel($order->status),
                        'is_free_gift' => $order->is_free_gift,
                        'created_at' => $order->created_at->format('d/m/Y H:i'),
                        'user' => [
                            'id' => $order->user->id,
                            'prenom' => $order->user->prenom,
                            'telephone' => $order->user->telephone,
                        ],
                    ];
                });

            \Log::info('Recent orders count:', ['count' => $recentOrders->count()]);

            // Structure CORRIGÉE pour matcher l'interface DashboardStats et RecentOrder[]
            $response = [
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_orders' => $recentOrders->toArray(),
                ],
            ];

            \Log::info('Response structure:', [
                'has_success' => isset($response['success']),
                'has_data' => isset($response['data']),
                'has_stats' => isset($response['data']['stats']),
                'has_recent_orders' => isset($response['data']['recent_orders']),
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('Dashboard error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getStatusLabel($status)
    {
        return match($status) {
            'en_attente' => 'En attente',
            'en_cours' => 'En cours',
            'termine' => 'Terminé',
            'annule' => 'Annulé',
            default => $status,
        };
    }

    // ==================== ORDERS ====================

    public function orders(Request $request)
    {
        $status = $request->query('status');
        $query = EgbOrder::with(['user:id,prenom,telephone', 'service:id,label,platform,service_type'])
            ->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->get()->map(function ($order) {
            return [
                'id' => $order->id,
                'reference' => $order->reference,
                'service' => [
                    'platform' => $order->service->platform,
                    'label' => $order->service->label,
                    'service_type' => $order->service->service_type,
                ],
                'link' => $order->link,
                'quantity' => $order->quantity,
                'points_spent' => $order->points_spent,
                'status' => $order->status,
                'status_label' => $this->getStatusLabel($order->status),
                'is_free_gift' => $order->is_free_gift,
                'admin_notes' => $order->admin_notes,
                'created_at' => $order->created_at->format('d/m/Y H:i'),
                'user' => [
                    'id' => $order->user->id,
                    'prenom' => $order->user->prenom,
                    'telephone' => $order->user->telephone,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $orders->toArray(),
        ]);
    }

    public function showOrder(int $id)
    {
        $order = EgbOrder::with(['user:id,prenom,telephone', 'service:id,label,platform,service_type'])
            ->findOrFail($id);

        $orderData = [
            'id' => $order->id,
            'reference' => $order->reference,
            'service' => [
                'platform' => $order->service->platform,
                'label' => $order->service->label,
                'service_type' => $order->service->service_type,
            ],
            'link' => $order->link,
            'quantity' => $order->quantity,
            'points_spent' => $order->points_spent,
            'status' => $order->status,
            'status_label' => $this->getStatusLabel($order->status),
            'is_free_gift' => $order->is_free_gift,
            'admin_notes' => $order->admin_notes,
            'created_at' => $order->created_at->format('d/m/Y H:i'),
            'user' => [
                'id' => $order->user->id,
                'prenom' => $order->user->prenom,
                'telephone' => $order->user->telephone,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $orderData,
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
                'achat',
                "Remboursement commande #{$order->reference}",
                ['order_id' => $order->id]
            );
        }

        $order->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? $order->admin_notes,
        ]);

        $order->load(['user:id,prenom,telephone', 'service:id,label,platform,service_type']);

        $orderData = [
            'id' => $order->id,
            'reference' => $order->reference,
            'service' => [
                'platform' => $order->service->platform,
                'label' => $order->service->label,
                'service_type' => $order->service->service_type,
            ],
            'link' => $order->link,
            'quantity' => $order->quantity,
            'points_spent' => $order->points_spent,
            'status' => $order->status,
            'status_label' => $this->getStatusLabel($order->status),
            'is_free_gift' => $order->is_free_gift,
            'admin_notes' => $order->admin_notes,
            'created_at' => $order->created_at->format('d/m/Y H:i'),
            'user' => [
                'id' => $order->user->id,
                'prenom' => $order->user->prenom,
                'telephone' => $order->user->telephone,
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour.',
            'data' => $orderData,
        ]);
    }

    // ==================== USERS ====================

    public function users(Request $request)
    {
        $query = EgbUser::withCount(['orders', 'referrals'])
            ->orderByDesc('created_at');

        if ($request->query('blocked') !== null) {
            $query->where('is_blocked', $request->query('blocked') == '1');
        }

        $paginatedData = $query->paginate(30);

        return response()->json([
            'success' => true,
            'data' => $paginatedData->items(),
            'pagination' => [
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
                'total' => $paginatedData->total(),
            ],
        ]);
    }

    public function showUser(int $id)
    {
        $user = EgbUser::withCount(['orders', 'referrals'])
            ->findOrFail($id);

        $user->load([
            'orders' => fn($q) => $q->with('service:id,label,platform,service_type')->latest()->limit(10)
        ]);

        $userData = [
            'id' => $user->id,
            'prenom' => $user->prenom,
            'telephone' => $user->telephone,
            'email' => $user->email,
            'points_balance' => $user->points_balance,
            'referral_code' => $user->referral_code,
            'free_views_claimed' => $user->free_views_claimed,
            'inscrit_le' => $user->created_at->format('d/m/Y H:i'),
            'is_admin' => $user->is_admin ?? false,
            'is_blocked' => $user->is_blocked ?? false,
            'device_fingerprint' => $user->device_fingerprint,
            'ip_address' => $user->ip_address,
            'orders_count' => $user->orders_count,
            'referrals_count' => $user->referrals_count,
            'orders' => $user->orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'reference' => $order->reference,
                    'service' => [
                        'platform' => $order->service->platform,
                        'label' => $order->service->label,
                        'service_type' => $order->service->service_type,
                    ],
                    'link' => $order->link,
                    'quantity' => $order->quantity,
                    'points_spent' => $order->points_spent,
                    'status' => $order->status,
                    'status_label' => $this->getStatusLabel($order->status),
                    'is_free_gift' => $order->is_free_gift,
                    'admin_notes' => $order->admin_notes,
                    'created_at' => $order->created_at->format('d/m/Y H:i'),
                ];
            })->toArray(),
        ];

        return response()->json([
            'success' => true,
            'data' => $userData,
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
        $services = EgbService::orderBy('platform')->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => $services->toArray(),
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
            'data' => $service->toArray(),
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
            'data' => $service->fresh()->toArray(),
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
        $paginatedData = EgbWheelEvent::with('winner:id,prenom')
            ->withCount('participations')
            ->orderByDesc('scheduled_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $paginatedData->items(),
            'pagination' => [
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
                'total' => $paginatedData->total(),
            ],
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
            'data' => $event->toArray(),
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
        $settings = EgbSetting::all()->groupBy('group');

        return response()->json([
            'success' => true,
            'data' => $settings->toArray(),
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

        if ($status) {
            $query->where('status', $status);
        }

        $paginatedData = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $paginatedData->items(),
            'pagination' => [
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
                'total' => $paginatedData->total(),
            ],
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
                'ticket' => $ticket->toArray(),
                'messages' => $messages->toArray(),
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
