<?php

namespace App\Http\Controllers\EGBooster\Api;

use App\Http\Controllers\Controller;
use App\Models\EGBooster\EgbUser;
use App\Models\EGBooster\EgbSetting;
use App\Services\EGBooster\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request, ReferralService $referralService)
    {
        $validated = $request->validate([
            'prenom' => 'required|string|max:255',
            'telephone' => 'required|string|max:20|unique:egb_users,telephone',
            'code_pin' => 'required|string|size:6',
            'code_pin_confirmation' => 'required|same:code_pin',
            'email' => 'nullable|email',
            'referral_code' => 'nullable|string',
            'cf_turnstile_token' => 'nullable|string', // Cloudflare Turnstile
        ]);

        // Vérification anti-multi-comptes
        $fingerprint = $request->header('X-Device-Fingerprint');
        $ip = $request->ip();

        if ($fingerprint && $ip) {
            $existingAccount = EgbUser::where('device_fingerprint', $fingerprint)
                ->where('ip_address', $ip)
                ->first();

            if ($existingAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un compte existe déjà sur cet appareil.',
                ], 403);
            }
        }

        $user = EgbUser::create([
            'prenom' => $validated['prenom'],
            'telephone' => $validated['telephone'],
            'code_pin' => Hash::make($validated['code_pin']),
            'email' => $validated['email'] ?? null,
            'referral_code' => EgbUser::generateReferralCode(),
            'device_fingerprint' => $fingerprint,
            'ip_address' => $ip,
            'points_balance' => 0,
        ]);

        // Traiter le parrainage
        $referralService->processReferral($user, $validated['referral_code'] ?? null);

        $token = $user->createToken('egb_auth')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Inscription réussie! Bienvenue sur EG Booster 🚀',
            'data' => [
                'user' => $this->formatUser($user->fresh()),
                'token' => $token,
                'free_views_available' => !$user->free_views_claimed,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'telephone' => 'required|string',
            'code_pin' => 'required|string',
        ]);

        $user = EgbUser::where('telephone', $request->telephone)->first();

        if (!$user || !Hash::check($request->code_pin, $user->code_pin)) {
            throw ValidationException::withMessages([
                'telephone' => ['Identifiants incorrects.'],
            ]);
        }

        if ($user->is_blocked) {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte a été bloqué. Contactez le support.',
            ], 403);
        }

        $user->update(['last_login_at' => now()]);
        $token = $user->createToken('egb_auth')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie!',
            'data' => [
                'user' => $this->formatUser($user),
                'token' => $token,
                'free_views_available' => !$user->free_views_claimed,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie.',
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => $this->formatUser($user),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'prenom' => 'sometimes|string|max:255',
            'email' => 'sometimes|nullable|email',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour.',
            'data' => $this->formatUser($user->fresh()),
        ]);
    }

    public function updatePin(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_pin' => 'required|string',
            'new_pin' => 'required|string|size:6',
            'new_pin_confirmation' => 'required|same:new_pin',
        ]);

        if (!Hash::check($request->current_pin, $user->code_pin)) {
            throw ValidationException::withMessages([
                'current_pin' => ['Code PIN actuel incorrect.'],
            ]);
        }

        $user->update(['code_pin' => Hash::make($request->new_pin)]);

        return response()->json([
            'success' => true,
            'message' => 'Code PIN mis à jour.',
        ]);
    }

    public function getDefaultReferralCode()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'referral_code' => EgbSetting::get('default_referral_code', 'EGBOOST'),
            ],
        ]);
    }

    private function formatUser(EgbUser $user): array
    {
        return [
            'id' => $user->id,
            'prenom' => $user->prenom,
            'telephone' => $user->telephone,
            'email' => $user->email,
            'points_balance' => $user->points_balance,
            'referral_code' => $user->referral_code,
            'free_views_claimed' => $user->free_views_claimed,
            'inscrit_le' => $user->created_at->format('d/m/Y H:i'),
        ];
    }
}
