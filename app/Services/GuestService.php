<?php

namespace App\Services;

use App\Models\User;
use App\Models\Post;
use App\Models\Area;
use App\Models\State;

class GuestService
{
    public function requestConvert($request)
    {
        // $request['q01'] = !empty($request['q01']) ? implode('・', $request['q01']) : null;
        $request['q06'] = $request['q06'] !== '未選択' ? $request['q06'] : null;
        $request['q08'] = $request['q08'] !== '未選択' ? $request['q08'] : null;
        $request['q17'] = !empty($request['q17']) ? implode('・', $request['q17']) : null;
        $request['name'] = !empty($request['name']) ? preg_replace("/\s|　/", "", $request['name']) : null;
        $request['tel'] = !empty($request['tel']) ? hyphenConvert($request['tel']) : null;
        $request['zipcode'] = !empty($request['zipcode']) ? hyphenConvert($request['zipcode']) : null;
        $request['address'] = !empty($request['address']) ? hyphenChange($request['address']) : null;

        return $request;
    }

    function stateCheck($data)
    {
        $negatives = negatives();
        $status = 0;
        foreach (selectArrays() as $select) {
            $status = in_array($data->{$select}, $negatives) ? $status + 1 : $status;
        }
        $status = $status > 0 ? 'negative' : 'positive';
        
        $ng = null;
        foreach (commentArrays() as $comment) {
            if($ng !== 1) {
                $ng = ngWords($data->{$comment}) !== null ? 1 : null;
            }
        }
        $ng = $ng == 1 ? 'NG' : 'OK';
        
        $state = new State();
        $state->post_id = $data->id;
        $state->post_state = $status;
        $state->post_ng = $ng;
        $state->save();

        return $data;
    }

    function mailList($data) {

        $shop = User::where('shop_id', $data->shop_id)->first();

        $amg = Area::where('area_name', $shop->area->area_name)
            ->where('block_name', null)
            ->first();

        $mng = Area::where('area_name', $shop->area->area_name)
            ->where('block_name', $shop->area->block_name)
            ->first();
        
        if ($data->state->post_ng !== 'NG') {
            $users = User::where('role', 'admin')
                ->orWhere('shop_id', $data->shop_id)
                ->orWhere('area_id', $amg->id)
                ->orWhere('area_id', $mng->id)
                ->where('role', 'manager')
                ->get();
        } else {
            $users = User::where('role', 'admin')
                ->orWhere('area_id', $amg->id)
                ->orWhere('area_id', $mng->id)
                ->where('role', 'manager')
                ->get();
        }

        return $users;
    }
}