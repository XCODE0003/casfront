<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Chat;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'chat_id',
        'message',
        'is_read',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_read' => 'boolean'
    ];

    protected $appends = ['formatted_time'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function getFormattedTimeAttribute()
    {
        $userTimezone = request()->header('Timezone') 
            ?? request()->get('timezone')
            ?? auth('api')->user()->timezone 
            ?? config('app.timezone');
        
        return $this->created_at
            ->setTimezone($userTimezone)
            ->format('H:i');
    }

    

}
