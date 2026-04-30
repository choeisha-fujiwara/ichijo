<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservationSlot extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'article_id',
        'date',
        'start_time',
        'end_time',
        'capacity',
        'reserved_count',
        'memo',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
