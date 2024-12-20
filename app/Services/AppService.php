<?php

namespace App\Services;

use App\Models\User;
use App\Models\Post;
use App\Models\Area;
use App\Models\State;
use App\Models\Comment;

class AppService
{
    // 一覧データ抽出
    public function dataExtraction($user)
    {
        // 管理者
        if ($user->role == 'admin') {
            $data = Post::orderBy('created_at', 'desc')
            ->paginate(100)
            ->withQueryString();
        
        // エリアマネージャー
        } elseif ($user->role == 'area_manager') {
            $data = Post::with('user.area')
            ->whereHas('user.area', function ($query) use ($user) {
                $query->where('area_name', $user->area->area_name);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(100)
            ->withQueryString();    
        
        // マネージャー
        } elseif ($user->role == 'manager') {
            $data = Post::with('user.area')
            ->whereHas('user.area', function ($query) use ($user) {
                $query->where('area_name', $user->area->area_name)
            ->where('block_name', $user->area->block_name);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(100)
            ->withQueryString();    

        // 店舗
        } else {
            $shop = $user->shop_id;
            $data = Post::with('state')
            ->where('shop_id', $shop)
            ->whereHas('state', function ($query) {
                $query->where('post_ng', 'OK');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(100)
            ->withQueryString();
            $data = deletionPersonalData($data);
        }

        $data = readOrUnread($user, $data);

        return $data;
    }

    // 検索データ抽出
    public function search($user, $keyword)
    {
        // 管理者
        if ($user->role == 'admin') {
            $data = Post::orderBy('created_at', 'desc')
            ->whereHas('user', function ($query) use ($keyword) {
                $query->where('name', 'LIKE', "%{$keyword}%");
            })
            ->orWhere('q20', 'LIKE', "%{$keyword}%")
            ->orWhere('email', 'LIKE', "%{$keyword}%")
            ->paginate(100)
            ->appends(['keyword' => $keyword]);

        // エリアマネージャー
        } elseif ($user->role == 'area_manager') {
            $data = Post::with('user.area')
            ->whereHas('user.area', function ($query) use ($user) {
                $query->where('area_name', $user->area->area_name);
            })
            ->whereHas('user', function ($query) use ($keyword) {
                return $query->where('name', 'LIKE', "%{$keyword}%");
            })
            ->orWhere('q20', 'LIKE', "%{$keyword}%")
            ->orWhere('email', 'LIKE', "%{$keyword}%")
            ->orderBy('created_at', 'desc')
            ->paginate(100)
            ->appends(['keyword' => $keyword]);
            
        // マネージャー
        } elseif ($user->role == 'manager') {
            $data = Post::with('user.area')
            ->whereHas('user.area', function ($query) use ($user) {
                $query->where('area_name', $user->area->area_name)
            ->where('block_name', $user->area->block_name);
            })
            ->whereHas('user', function ($query) use ($keyword) {
                $query->where('name', 'LIKE', "%{$keyword}%");
            })
            ->orWhere('q20', 'LIKE', "%{$keyword}%")
            ->orWhere('email', 'LIKE', "%{$keyword}%")
            ->orderBy('created_at', 'desc')
            ->paginate(100)
            ->appends(['keyword' => $keyword]);
        
        // 店舗
        } else {
            $shop = $user->shop_id;
            $data = Post::with('state')
            ->where('shop_id', $shop)
            ->whereHas('user', function ($query) use ($keyword) {
                $query->where('name', 'LIKE', "%{$keyword}%");
            })
            ->whereHas('state', function ($query) {
                $query->where('post_ng', 'OK');
            })
            ->orWhere('q20', 'LIKE', "%{$keyword}%")
            ->orderBy('created_at', 'desc')
            ->paginate(100)
            ->appends(['keyword' => $keyword]);
            $data = deletionPersonalData($data);
        }

        $data = readOrUnread($user, $data);

        return $data;
    }

    // Postの状態で抽出
    public function filter($user, $state, $keyword)
    {
        $column_1st = $state == 'active' || $state == 'approval' ? 'post_active' : 'post_state';
        $column_1st = $state == 'ng' ? 'post_ng' : $column_1st;
        $column_2nd = $state == 'negative' ? 'post_active' : 'id';
        $column_3rd = $state == 'negative' ? 'post_ng' : 'id';
        $terms_1 = $state == 'negative' ? '=' : '!=';
        $shop = findShopId($keyword);
        $terms_2 = $shop !== null ? '=' : '!=';

        // 管理者
        if ($user->role == 'admin') {
            $data = Post::orderBy('created_at', 'desc')
            ->whereHas('state', function ($query) use ($column_1st, $column_2nd, $column_3rd, $terms_1, $state) {
                $query->where($column_1st, $state)
                ->where($column_2nd, $terms_1, null)
                ->where($column_3rd, $terms_1, 'OK');
            })
            ->where('shop_id', $terms_2, $shop)
            ->paginate(100)
            ->appends(['state' => $state]);

        // エリアマネージャー
        } elseif ($user->role == 'area_manager') {
            $data = Post::with('user.area')
            ->whereHas('user.area', function ($query) use ($user) {
                $query->where('area_name', $user->area->area_name);
            })
            ->whereHas('state', function ($query) use ($column_1st, $column_2nd, $column_3rd, $terms_1, $state) {
                $query->where($column_1st, $state)
                ->where($column_2nd, $terms_1, null)
                ->where($column_3rd, $terms_1, 'OK');
            })
            ->where('shop_id', $terms_2, $shop)
            ->orderBy('created_at', 'desc')
            ->paginate(100)
            ->appends(['state' => $state]);
            
        // マネージャー
        } elseif ($user->role == 'manager') {
            $data = Post::with('user.area')
            ->whereHas('user.area', function ($query) use ($user) {
                $query->where('area_name', $user->area->area_name)
            ->where('block_name', $user->area->block_name);
            })
            ->whereHas('state', function ($query) use ($column_1st, $column_2nd, $column_3rd, $terms_1, $state) {
                $query->where($column_1st, $state)
                ->where($column_2nd, $terms_1, null)
                ->where($column_3rd, $terms_1, 'OK');
            })
            ->where('shop_id', $terms_2, $shop)
            ->orderBy('created_at', 'desc')
            ->paginate(100)
            ->appends(['state' => $state]);
        
        // 店舗
        } else {
            $shop = $user->shop_id;
            $data = Post::where('shop_id', $shop)
            ->whereHas('state', function ($query) use ($column_1st, $column_2nd, $column_3rd, $terms_1, $state) {
                $query->where($column_1st, $state)
                ->where($column_2nd, $terms_1, null)
                ->where($column_3rd, $terms_1, 'OK');
            })
            ->where('shop_id', '=', $shop)
            ->orderBy('created_at', 'desc')
            ->paginate(100)
            ->appends(['state' => $state]);
            $data = deletionPersonalData($data);
        }

        $data = readOrUnread($user, $data);

        return $data;
    }
    
    // 未読の抽出
    public function unreadFilter($user, $state, $keyword)
    {
        $shop = findShopId($keyword);
        $terms = $shop !== null ? '=' : '!=';

        if ($user->role == 'admin') {
            $data = Post::orderBy('created_at', 'desc')
            ->whereHas('state', function ($query) {
                $query->where('post_read->shop', '=', '')
                    ->orWhereNull('post_read');
            })
            ->whereHas('state', function ($query) {
                $query->where('post_ng', 'OK');
            })
            ->where('shop_id', $terms, $shop)
            ->paginate(100)
            ->appends(['state' => $state]);

        // エリアマネージャー
        } elseif ($user->role == 'area_manager') {
            $data = Post::with('user.area')
            ->whereHas('user.area', function ($query) use ($user) {
                $query->where('area_name', $user->area->area_name);
            })
            ->whereHas('state', function ($query) {
                $query->where('post_read->shop', '=', '')
                    ->orWhereNull('post_read');
            })
            ->whereHas('state', function ($query) {
                $query->where('post_ng', 'OK');
            })
            ->where('shop_id', $terms, $shop)
            ->orderBy('created_at', 'desc')
            ->paginate(100)
            ->appends(['state' => $state]);
            
        // マネージャー
        } elseif ($user->role == 'manager') {
            $data = Post::with('user.area')
            ->whereHas('user.area', function ($query) use ($user) {
                $query->where('area_name', $user->area->area_name)
            ->where('block_name', $user->area->block_name);
            })
            ->whereHas('state', function ($query) {
                $query->where('post_read->shop', '=', '')
                    ->orWhereNull('post_read');
            })
            ->whereHas('state', function ($query) {
                $query->where('post_ng', 'OK');
            })
            ->where('shop_id', $terms, $shop)
            ->orderBy('created_at', 'desc')
            ->paginate(100)
            ->appends(['state' => $state]);
        
        // 店舗
        } else {
            $shop = $user->shop_id;
            $data = Post::where('shop_id', $shop)
            ->whereHas('state', function ($query) {
                $query->where('post_read->shop', '=', '')
                    ->orWhereNull('post_read');
            })
            ->whereHas('state', function ($query) {
                $query->where('post_ng', 'OK');
            })
            ->where('shop_id', '=', $shop)
            ->orderBy('created_at', 'desc')
            ->paginate(100)
            ->appends(['state' => $state]);
            $data = deletionPersonalData($data);
        }

        $data = readOrUnread($user, $data);

        return $data;
    }

    // 本人未読の抽出
    public function myUnreadFilter($user, $state, $keyword)
    {
        $shop = findShopId($keyword);
        $terms = $shop !== null ? '=' : '!=';

        if ($user->role == 'admin') {
            $data = Post::orderBy('created_at', 'desc')
            ->whereHas('state', function ($query) {
                $query->whereNull('post_read')
                ->orWhereNull(['post_read->admin']);
            })
            ->where('shop_id', $terms, $shop)
            ->paginate(100)
            ->appends(['state' => $state]);

        // エリアマネージャー
        } elseif ($user->role == 'area_manager') {
            $data = Post::with('user.area')
            ->whereHas('state', function ($query) {
                $query->whereNull('post_read')
                ->orWhereNull(['post_read->area_manager']);
            })
            ->where('shop_id', $terms, $shop)
            ->orderBy('created_at', 'desc')
            ->paginate(100)
            ->appends(['state' => $state]);
            
        // マネージャー
        } elseif ($user->role == 'manager') {
            $data = Post::with('user.area')
            ->whereHas('state', function ($query) {
                $query->whereNull('post_read')
                ->orWhereNull(['post_read->manager']);
            })
            ->where('shop_id', $terms, $shop)
            ->orderBy('created_at', 'desc')
            ->paginate(100)
            ->appends(['state' => $state]);
        
        // 店舗
        } else {
            $shop = $user->shop_id;
            $data = Post::where('shop_id', $shop)
            ->whereHas('state', function ($query) {
                $query->whereNull('post_read')
                ->orWhereNull(['post_read->shop']);
            })
            ->where('shop_id', '=', $shop)
            ->orderBy('created_at', 'desc')
            ->paginate(100)
            ->appends(['state' => $state]);
            $data = deletionPersonalData($data);
        }

        $data = readOrUnread($user, $data);

        return $data;
    }    
}