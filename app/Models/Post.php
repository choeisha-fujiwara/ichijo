<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $fillable = [
        'shop_id',
        'state_post',
        'state_comment',
        'state_ng',
        'comment_admin',
        'comment_manager',
        'comment_shop',
        'email',
        'gender',
        'age',
        'name',
        'tel',
        'zipcode',
        'address',
        'q01',
        'q02',
        'q03',
        'q04',
        'q05',
        'q06',
        'q07',
        'q08',
        'q09',
        'q10',
        'q11',
        'q12',
        'q13',
        'q14',
        'q15',
        'q16',
        'q17',
        'q18',
        'q19',
        'q20',
    ];

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function state(): HasOne
    {
        return $this->hasOne(State::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shop_id', 'shop_id');
    }
}
