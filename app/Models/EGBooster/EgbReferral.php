<?php

namespace App\Models\EGBooster;

use Illuminate\Database\Eloquent\Model;

class EgbReferral extends Model
{
    protected $table = 'egb_referrals';

    protected $fillable = [
        'referrer_id', 'referred_id',
        'has_deposited', 'points_earned',
    ];

    protected $casts = [
        'has_deposited' => 'boolean',
        'points_earned' => 'integer',
    ];

    public function referrer()
    {
        return $this->belongsTo(EgbUser::class, 'referrer_id');
    }

    public function referred()
    {
        return $this->belongsTo(EgbUser::class, 'referred_id');
    }
}
