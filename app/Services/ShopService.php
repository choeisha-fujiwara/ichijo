<?php

namespace App\Services;

use App\Models\User;
use App\Models\Reservation;
use App\Models\Area;
use App\Models\State;

class ShopService
{
    public function shopsData($data)
    {
        $posts = Reservation::whereHas('state', function ($query) {
            $query->where('post_read->shop', '')
            ->orWhereNull('post_read');
            })
            ->select('shop_id')
            ->selectRaw('COUNT(shop_id) as count_shop')
            ->groupBy('shop_id')
            ->get();

        $actives = Reservation::whereHas('state', function ($query) {
            $query->where('post_active', 'active');
            })
            ->select('shop_id')
            ->selectRaw('COUNT(shop_id) as count_active')
            ->groupBy('shop_id')
            ->get();
        
        $approvals = Reservation::whereHas('state', function ($query) {
            $query->where('post_active', 'approval');
            })
            ->select('shop_id')
            ->selectRaw('COUNT(shop_id) as count_approval')
            ->groupBy('shop_id')
            ->get();
        
        foreach ($data as $datum) {
            foreach ($posts as $post) {
                $datum->unread = $datum->shop_id == $post->shop_id ? $post->count_shop : $datum->unread;
            }
            foreach ($actives as $active) {
                $datum->active = $datum->shop_id == $active->shop_id ? $active->count_active : $datum->active;
            }
            foreach ($approvals as $approval) {
                $datum->approval = $datum->shop_id == $approval->shop_id ? $approval->count_approval : $datum->approval;
            }
            $area = $datum->area->area_name;
            
            $amg = Area::where('area_name', $area)
                ->whereNull('block_name')
                ->first();
            $amg_mail = User::where('area_id', $amg->id)
                ->first();
            $mg_mail = User::where('area_id', $datum->area_id)
                ->where('role', 'manager')
                ->first();
            $datum->amg_mail = $amg_mail->email;
            $datum->mg_mail = $mg_mail->email;
        }

        return $data;

    }
}