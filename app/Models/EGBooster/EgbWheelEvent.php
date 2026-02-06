<?php

namespace App\Models\EGBooster;

use Illuminate\Database\Eloquent\Model;

class EgbWheelEvent extends Model
{
    protected $table = 'egb_wheel_events';

    protected $fillable = [
        'scheduled_at', 'status', 'total_pot',
        'winner_id', 'is_manual_winner',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'total_pot' => 'integer',
        'is_manual_winner' => 'boolean',
    ];

    public function winner()
    {
        return $this->belongsTo(EgbUser::class, 'winner_id');
    }

    public function participations()
    {
        return $this->hasMany(EgbWheelParticipation::class, 'wheel_event_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'en_attente');
    }

    public function scopeCurrent($query)
    {
        return $query->where('status', '!=', 'termine')->orderBy('scheduled_at', 'desc');
    }
}
