<?php

namespace App\Models\Panel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'avatar',
        'inviter',
    ];

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(Worker::class, 'inviter', 'id');
    }
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

   
}

