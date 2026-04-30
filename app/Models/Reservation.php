<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'article_id',
        'reservation_slot_id',
        'reservation_datetime',
        'firstname',
        'lastname',
        'firstname_kana',
        'lastname_kana',
        'zipcode',
        'prefecture',
        'city',
        'address',
        'building',
        'phone',
        'email',
        'memo',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class)->withTrashed();
    }

    public function reservationSlot(): BelongsTo
    {
        return $this->belongsTo(ReservationSlot::class)->withTrashed();
    }
}
