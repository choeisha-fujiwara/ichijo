<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArticleVenue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'venue_name',
        'address',
        'phone',
        'fax',
        'image',
        'access',
        'map_url',
        'manager',
        'notes',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'venue_id');
    }
}
