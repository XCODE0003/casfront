<?php

namespace App\Models\Panel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotifySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notify_new_visit',
        'notify_activate_promo',
        'notify_new_payment',
        'notify_new_order',
        'bot_token'
    ];
}
