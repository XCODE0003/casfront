<?php

namespace App\Models\Panel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingsWorker extends Model
{
    use HasFactory;

    protected $fillable = [
        'win_chance',
        'minimal_deposit',
        'min_withdraw_worker',
        'percent_profit_worker',
    ];
}
