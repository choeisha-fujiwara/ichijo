<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'shop_name',
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
}
