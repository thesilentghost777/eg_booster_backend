<?php

namespace App\Models\EGBooster;

use Illuminate\Database\Eloquent\Model;

class EgbService extends Model
{
    protected $table = 'egb_services';

    protected $fillable = [
        'platform', 'service_type', 'label',
        'quantity', 'price_points', 'description',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price_points' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function orders()
    {
        return $this->hasMany(EgbOrder::class, 'service_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }
}
