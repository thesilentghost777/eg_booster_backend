<?php

namespace App\Models\EGBooster;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class EgbUser extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'egb_users';

    protected $fillable = [
        'prenom', 'telephone', 'code_pin', 'email',
        'points_balance', 'referral_code', 'referred_by',
        'device_fingerprint', 'ip_address',
        'free_views_claimed', 'is_blocked', 'is_admin',
        'last_login_at',
    ];

    protected $hidden = ['code_pin'];

    protected $casts = [
        'points_balance' => 'integer',
        'free_views_claimed' => 'boolean',
        'is_blocked' => 'boolean',
        'is_admin' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(EgbOrder::class, 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany(EgbTransaction::class, 'user_id');
    }

    public function referrals()
    {
        return $this->hasMany(EgbReferral::class, 'referrer_id');
    }

    public function referrer()
    {
        return $this->belongsTo(self::class, 'referred_by');
    }

    public function filleuls()
    {
        return $this->hasMany(self::class, 'referred_by');
    }

    public function sentTransfers()
    {
        return $this->hasMany(EgbTransfer::class, 'sender_id');
    }

    public function receivedTransfers()
    {
        return $this->hasMany(EgbTransfer::class, 'receiver_id');
    }

    public function supportTickets()
    {
        return $this->hasMany(EgbSupportTicket::class, 'user_id');
    }

    public function wheelParticipations()
    {
        return $this->hasMany(EgbWheelParticipation::class, 'user_id');
    }

    public static function generateReferralCode(): string
    {
        do {
            $code = 'EGB-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }
}
