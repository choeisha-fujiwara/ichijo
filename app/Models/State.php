<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class State extends Model
{
    protected $fillable = [
        'post_id',
        'post_read',
        'post_state',
        'post_active',
        'post_ng',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}