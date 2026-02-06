<?php

namespace App\Models\EGBooster;

use Illuminate\Database\Eloquent\Model;

class EgbTransaction extends Model
{
    protected $table = 'egb_transactions';

    protected $fillable = [
        'user_id', 'type', 'amount_fcfa', 'points',
        'balance_before', 'balance_after',
        'reference', 'description', 'metadata',
    ];

    protected $casts = [
        'amount_fcfa' => 'integer',
        'points' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(EgbUser::class, 'user_id');
    }

    public static function generateReference(): string
    {
        return 'EGB-TXN-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    }
}
