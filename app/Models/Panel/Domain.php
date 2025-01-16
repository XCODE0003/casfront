<?php

namespace App\Models\Panel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'user_id',
        'ns_records',
        'title',
        'win_chance',
        'cloudflare_zone_id',
        'status',
    ];

    protected $casts = [
        'ns_records' => 'array',
        
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'cloudflare_zone_id',
        'ns_records',
        'user_id',
        'status',
        'id',
    ];
}
