<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'public_token',
        'user_id',
        'title',
        'body',
        'freeword_1',
        'freeword_2',
        'header_image',
        'body_image',
        'body_image_captions',
        'memo',
        'manager',
        'emails',
        'category',
        'published_at',
        'unpublished_at',
        'status',
        'venue_id',
    ];

    protected function casts(): array
    {
        return [
            'body_image' => 'array',
            'body_image_captions' => 'array',
            'emails' => 'array',
            'published_at' => 'datetime',
            'unpublished_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $article): void {
            if (empty($article->public_token)) {
                $article->public_token = (string) Str::uuid();
            }
        });
    }

    public function getTopListStatusLabelAttribute(): string
    {
        if (! $this->published_at) {
            return '下書き';
        }

        if ($this->status !== 'publish') {
            return '公開未設定';
        }

        $now = now();

        if ($now->lt($this->published_at)) {
            return '公開予約中';
        }

        if ($this->unpublished_at && $now->gt($this->unpublished_at)) {
            return '公開終了';
        }

        return '公開中';
    }

    public function getTopListStatusClassAttribute(): string
    {
        if (! $this->published_at) {
            return 'is-draft';
        }

        if ($this->status !== 'publish') {
            return 'is-unpublished';
        }

        $now = now();

        if ($now->lt($this->published_at)) {
            return 'is-scheduled';
        }

        if ($this->unpublished_at && $now->gt($this->unpublished_at)) {
            return 'is-ended';
        }

        return 'is-published-now';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(ArticleVenue::class, 'venue_id');
    }

    public function reservationSlots(): HasMany
    {
        return $this->hasMany(ReservationSlot::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }
}
