<?php

use App\Http\Controllers\EGBooster\Api\AuthController;
use App\Http\Controllers\EGBooster\Api\ServiceController;
use App\Http\Controllers\EGBooster\Api\OrderController;
use App\Http\Controllers\EGBooster\Api\WalletController;
use App\Http\Controllers\EGBooster\Api\ReferralController;
use App\Http\Controllers\EGBooster\Api\WheelController;
use App\Http\Controllers\EGBooster\Api\TransferController;
use App\Http\Controllers\EGBooster\Api\SupportController;
use App\Http\Controllers\EGBooster\Admin\AdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| EG Booster API Routes
|--------------------------------------------------------------------------
| Ajouter dans routes/api.php:
| require __DIR__.'/egbooster_api.php';
|--------------------------------------------------------------------------
*/

//route de test
Route::get('test', function () {
    return response()->json(['message' => 'Test route is working']);
});

Route::prefix('egbooster')->name('egbooster.')->group(function () {

    // ============================
    // ROUTES PUBLIQUES
    // ============================

    // Auth
    Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');
    Route::get('auth/default-referral', [AuthController::class, 'getDefaultReferralCode'])->name('auth.default-referral');

    // Services (catalogue public)
    Route::get('services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('services/platforms', [ServiceController::class, 'platforms'])->name('services.platforms');
    Route::get('services/{id}', [ServiceController::class, 'show'])->name('services.show');

    // Roue (infos publiques)
    Route::get('wheel/current', [WheelController::class, 'current'])->name('wheel.current');
    Route::get('wheel/history', [WheelController::class, 'history'])->name('wheel.history');

    // Support WhatsApp
    Route::get('support/whatsapp', [SupportController::class, 'whatsapp'])->name('support.whatsapp');

    // ============================
    // ROUTES PROTÉGÉES (AUTH)
    // ============================

    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('auth/profile', [AuthController::class, 'profile'])->name('auth.profile');
        Route::put('auth/profile', [AuthController::class, 'updateProfile'])->name('auth.profile.update');
        Route::put('auth/pin', [AuthController::class, 'updatePin'])->name('auth.pin.update');

        // Wallet
        Route::get('wallet/balance', [WalletController::class, 'balance'])->name('wallet.balance');
        Route::post('wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
        Route::get('wallet/transactions', [WalletController::class, 'transactions'])->name('wallet.transactions');

        // Orders
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
        Route::post('orders/free-views', [OrderController::class, 'claimFreeViews'])->name('orders.free-views');
        Route::get('orders/{reference}', [OrderController::class, 'show'])->name('orders.show');

        // Referral
        Route::get('referral/stats', [ReferralController::class, 'stats'])->name('referral.stats');
        Route::get('referral/filleuls', [ReferralController::class, 'filleuls'])->name('referral.filleuls');
        Route::get('referral/share-link', [ReferralController::class, 'shareLink'])->name('referral.share-link');

        // Wheel
        Route::post('wheel/participate', [WheelController::class, 'participate'])->name('wheel.participate');

        // Transfer
        Route::post('transfer/find-recipient', [TransferController::class, 'findRecipient'])->name('transfer.find');
        Route::post('transfer/send', [TransferController::class, 'send'])->name('transfer.send');
        Route::get('transfer/history', [TransferController::class, 'history'])->name('transfer.history');

        // Support
        Route::get('support/tickets', [SupportController::class, 'index'])->name('support.index');
        Route::post('support/tickets', [SupportController::class, 'store'])->name('support.store');
        Route::get('support/tickets/{reference}/messages', [SupportController::class, 'messages'])->name('support.messages');
        Route::post('support/tickets/{reference}/reply', [SupportController::class, 'reply'])->name('support.reply');
    });

    // ============================
    // ROUTES ADMIN
    // ============================

    Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum'])->group(function () {
        // TODO: Ajouter un middleware admin custom pour vérifier is_admin

        // Dashboard
        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Orders
        Route::get('orders', [AdminController::class, 'orders'])->name('orders.index');
        Route::get('orders/{id}', [AdminController::class, 'showOrder'])->name('orders.show');
        Route::patch('orders/{id}/status', [AdminController::class, 'updateOrderStatus'])->name('orders.update-status');

        // Users
        Route::get('users', [AdminController::class, 'users'])->name('users.index');
        Route::get('users/{id}', [AdminController::class, 'showUser'])->name('users.show');
        Route::post('users/{id}/toggle-block', [AdminController::class, 'toggleBlockUser'])->name('users.toggle-block');
        Route::post('users/{id}/credit', [AdminController::class, 'creditUser'])->name('users.credit');

        // Services
        Route::get('services', [AdminController::class, 'services'])->name('services.index');
        Route::post('services', [AdminController::class, 'storeService'])->name('services.store');
        Route::put('services/{id}', [AdminController::class, 'updateService'])->name('services.update');
        Route::delete('services/{id}', [AdminController::class, 'deleteService'])->name('services.delete');

        // Wheel
        Route::get('wheel/events', [AdminController::class, 'wheelEvents'])->name('wheel.events');
        Route::post('wheel/events', [AdminController::class, 'createWheelEvent'])->name('wheel.create');
        Route::post('wheel/events/{id}/draw', [AdminController::class, 'drawWheelWinner'])->name('wheel.draw');

        // Settings
        Route::get('settings', [AdminController::class, 'settings'])->name('settings.index');
        Route::put('settings', [AdminController::class, 'updateSettings'])->name('settings.update');

        // Support
        Route::get('support/tickets', [AdminController::class, 'supportTickets'])->name('support.index');
        Route::get('support/tickets/{reference}', [AdminController::class, 'supportTicketMessages'])->name('support.show');
        Route::post('support/tickets/{reference}/reply', [AdminController::class, 'supportReply'])->name('support.reply');
        Route::post('support/tickets/{reference}/close', [AdminController::class, 'closeTicket'])->name('support.close');
    });
});


Crée maintenant le frontend React PWA pour EG Booster avec les pages: Welcome (marketing), Inscription, Dashboard, Services, Portefeuille, Grande Roue et Parrainage sans oublier ls screen de l'admin
