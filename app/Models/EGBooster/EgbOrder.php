<?php

namespace App\Models\EGBooster;

use Illuminate\Database\Eloquent\Model;

class EgbOrder extends Model
{
    protected $table = 'egb_orders';

    protected $fillable = [
        'reference', 'user_id', 'service_id', 'link',
        'quantity', 'points_spent', 'status',
        'is_free_gift', 'admin_notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'points_spent' => 'integer',
        'is_free_gift' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(EgbUser::class, 'user_id');
    }

    public function service()
    {
        return $this->belongsTo(EgbService::class, 'service_id');
    }

    public static function generateReference(): string
    {
        return 'EGB-CMD-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    }
}
