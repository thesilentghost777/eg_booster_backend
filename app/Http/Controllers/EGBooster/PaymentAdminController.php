<?php

namespace App\Http\Controllers\EGBooster;

use App\Http\Controllers\Controller;
use App\Models\EGBooster\EgbPayment;
use Illuminate\Http\Request;

class PaymentAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = EgbPayment::with('user')->latest();

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('external_id', 'like', "%{$search}%")
                  ->orWhere('freemopay_reference', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->paginate(20);

        // Statistiques
        $stats = [
            'total' => EgbPayment::count(),
            'pending' => EgbPayment::where('status', 'pending')->count(),
            'success' => EgbPayment::where('status', 'success')->count(),
            'failed' => EgbPayment::where('status', 'failed')->count(),
            'total_amount' => EgbPayment::where('status', 'success')->sum('amount_fcfa'),
        ];

        return view('egbooster.payments.index', compact('payments', 'stats'));
    }

    public function show($id)
    {
        $payment = EgbPayment::with('user')->findOrFail($id);
        return view('egbooster.payments.show', compact('payment'));
    }
}
