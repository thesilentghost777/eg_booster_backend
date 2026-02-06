<?php

namespace App\Models\EGBooster;

use Illuminate\Database\Eloquent\Model;

class EgbWheelParticipation extends Model
{
    protected $table = 'egb_wheel_participations';

    protected $fillable = [
        'wheel_event_id', 'user_id', 'points_bet',
    ];

    protected $casts = [
        'points_bet' => 'integer',
    ];

    public function wheelEvent()
    {
        return $this->belongsTo(EgbWheelEvent::class, 'wheel_event_id');
    }

    public function user()
    {
        return $this->belongsTo(EgbUser::class, 'user_id');
    }
}
