<?php

namespace App\Models\EGBooster;

use Illuminate\Database\Eloquent\Model;

class EgbSupportTicket extends Model
{
    protected $table = 'egb_support_tickets';

    protected $fillable = [
        'reference', 'user_id', 'subject', 'status',
    ];

    public function user()
    {
        return $this->belongsTo(EgbUser::class, 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(EgbSupportMessage::class, 'ticket_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(EgbSupportMessage::class, 'ticket_id')->latestOfMany();
    }

    public static function generateReference(): string
    {
        return 'EGB-TKT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
    }
}
