<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PageView extends Model
{
    protected $fillable = ['article_id', 'date', 'view_count'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class)->withTrashed();
    }

    /**
     * 指定の article_id + 今日の日付で view_count を +1 する (upsert)
     */
    public static function recordView(int $articleId): void
    {
        static::upsert(
            [['article_id' => $articleId, 'date' => now()->toDateString(), 'view_count' => 1, 'created_at' => now(), 'updated_at' => now()]],
            ['article_id', 'date'],
            ['view_count' => DB::raw('view_count + 1'), 'updated_at' => now()]
        );
    }
}
