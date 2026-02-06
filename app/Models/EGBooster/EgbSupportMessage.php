<?php

namespace App\Models\EGBooster;

use Illuminate\Database\Eloquent\Model;

class EgbSupportMessage extends Model
{
    protected $table = 'egb_support_messages';

    protected $fillable = [
        'ticket_id', 'sender_type', 'sender_id',
        'message', 'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function ticket()
    {
        return $this->belongsTo(EgbSupportTicket::class, 'ticket_id');
    }
}
