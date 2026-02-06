<?php

namespace App\Models\EGBooster;

use Illuminate\Database\Eloquent\Model;

class EgbTransfer extends Model
{
    protected $table = 'egb_transfers';

    protected $fillable = [
        'reference', 'sender_id', 'receiver_id',
        'points', 'fees_points', 'net_points',
    ];

    protected $casts = [
        'points' => 'integer',
        'fees_points' => 'integer',
        'net_points' => 'integer',
    ];

    public function sender()
    {
        return $this->belongsTo(EgbUser::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(EgbUser::class, 'receiver_id');
    }

    public static function generateReference(): string
    {
        return 'EGB-TRF-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    }
}
