<?php

namespace App\Models\EGBooster;

use Illuminate\Database\Eloquent\Model;

class EgbPayment extends Model
{
    protected $fillable = [
        'user_id',
        'external_id',
        'freemopay_reference',
        'amount_fcfa',
        'phone_number',
        'payment_method',
        'status',
        'failure_message',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(EgbUser::class, 'user_id');
    }
}
