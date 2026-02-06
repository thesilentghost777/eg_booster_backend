<?php

namespace App\Http\Controllers\EGBooster\Api;

use App\Http\Controllers\Controller;
use App\Services\EGBooster\ReferralService;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    protected ReferralService $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    /**
     * Mes statistiques de parrainage
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        $stats = $this->referralService->getStats($user);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Liste de mes filleuls
     */
    public function filleuls(Request $request)
    {
        $user = $request->user();
        $filleuls = $this->referralService->getFilleuls($user);

        return response()->json([
            'success' => true,
            'data' => $filleuls,
        ]);
    }

    /**
     * Mon lien de parrainage
     */
    public function shareLink(Request $request)
    {
        $user = $request->user();

        $message = "🚀 Rejoins EG Booster et obtiens 1000 vues TikTok GRATUITEMENT!\n\n"
            . "📲 Utilise mon code de parrainage: {$user->referral_code}\n\n"
            . "👉 Télécharge l'app maintenant et booste tes réseaux sociaux!";

        return response()->json([
            'success' => true,
            'data' => [
                'referral_code' => $user->referral_code,
                'share_message' => $message,
            ],
        ]);
    }
}
