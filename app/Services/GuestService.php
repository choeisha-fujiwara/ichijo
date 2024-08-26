<?php

namespace App\Services;

use App\Models\User;
use App\Models\Customer;

class GuestService
{
    public function requestConvert($request, $data) {

        $postItems = [
            'shop_id',
            'shop_name',
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

        $comments = [
            'comment_admin',
            'comment_manager',
            'comment_shop',
        ];

        $request['q01'] = !empty($request['q01']) ? implode(',', $request['q01']) : null;
        $request['name'] = !empty($request['name']) ? preg_replace("/\s|　/", "", $request['name']) : null;
        $request['tel'] = !empty($request['tel']) ? hyphenConvert($request['tel']) : null;
        $request['tel'] = !empty($request['zipcode']) ? hyphenConvert($request['zipcode']) : null;

        foreach ($postItems as $postItem) {
            $data->{$postItem} = $request[$postItem];
        }

        foreach ($comments as $comment) {
            $data->{$comment} = 'まだコメントはありません';
        }

        return $data;
    }
}