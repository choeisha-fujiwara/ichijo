<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    public function user(): HasMany
    {
        return $this->hasMany(User::class, 'area_id', 'area_id');
    }
}
