<?php

namespace App\Models\Panel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Promo extends Model
{
    use HasFactory;

    protected $fillable = [
        'promo_code',
        'user_id',
        'amount',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }
}
