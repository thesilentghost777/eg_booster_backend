<?php

namespace App\Http\Controllers\EGBooster\Api;

use App\Http\Controllers\Controller;
use App\Models\EGBooster\EgbSupportTicket;
use App\Models\EGBooster\EgbSupportMessage;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    /**
     * Créer un nouveau ticket
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $user = $request->user();

        $ticket = EgbSupportTicket::create([
            'reference' => EgbSupportTicket::generateReference(),
            'user_id' => $user->id,
            'subject' => $validated['subject'],
            'status' => 'ouvert',
        ]);

        EgbSupportMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'user',
            'sender_id' => $user->id,
            'message' => $validated['message'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket créé. Notre équipe vous répondra rapidement.',
            'data' => [
                'reference' => $ticket->reference,
                'subject' => $ticket->subject,
                'status' => $ticket->status,
            ],
        ], 201);
    }

    /**
     * Liste de mes tickets
     */
    public function index(Request $request)
    {
        $tickets = EgbSupportTicket::where('user_id', $request->user()->id)
            ->with('latestMessage')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn($t) => [
                'reference' => $t->reference,
                'subject' => $t->subject,
                'status' => $t->status,
                'last_message' => $t->latestMessage?->message ? substr($t->latestMessage->message, 0, 80) . '...' : null,
                'last_message_from' => $t->latestMessage?->sender_type,
                'updated_at' => $t->updated_at->format('d/m/Y H:i'),
            ]);

        return response()->json([
            'success' => true,
            'data' => $tickets,
        ]);
    }

    /**
     * Messages d'un ticket
     */
    public function messages(string $reference)
    {
        $ticket = EgbSupportTicket::where('reference', $reference)->firstOrFail();

        $messages = EgbSupportMessage::where('ticket_id', $ticket->id)
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'sender_type' => $m->sender_type,
                'message' => $m->message,
                'is_read' => $m->is_read,
                'created_at' => $m->created_at->format('d/m/Y H:i'),
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'ticket' => [
                    'reference' => $ticket->reference,
                    'subject' => $ticket->subject,
                    'status' => $ticket->status,
                ],
                'messages' => $messages,
            ],
        ]);
    }

    /**
     * Répondre à un ticket
     */
    public function reply(Request $request, string $reference)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $ticket = EgbSupportTicket::where('reference', $reference)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($ticket->status === 'ferme') {
            return response()->json([
                'success' => false,
                'message' => 'Ce ticket est fermé.',
            ], 400);
        }

        EgbSupportMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'user',
            'sender_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        $ticket->touch();

        return response()->json([
            'success' => true,
            'message' => 'Message envoyé.',
        ]);
    }

    /**
     * Numéro WhatsApp du support
     */
    public function whatsapp()
    {
        $number = \App\Models\EGBooster\EgbSetting::get('whatsapp_number', '+237696087354');

        return response()->json([
            'success' => true,
            'data' => [
                'whatsapp_number' => $number,
                'whatsapp_link' => "https://wa.me/" . preg_replace('/[^0-9]/', '', $number),
            ],
        ]);
    }
}
