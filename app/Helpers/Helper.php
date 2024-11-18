<?php

use App\Models\User;
use App\Models\Post;
use App\Models\State;
use Carbon\Carbon;

// アクティブ投稿件数
function activeCount($user)
{
    if ($user->role == 'admin') {
        $res = State::where('post_active', 'active')->count();

    } elseif ($user->role == 'area_manager') {
        $res = Post::with('user.area')
        ->whereHas('user.area', function ($query) use ($user) {
            $query->where('area_name', $user->area->area_name);
        })
        ->whereHas('state', function ($query) {
            $query->where('post_active', 'active');
        })->count();
    
    } elseif ($user->role == 'manager') {
        $res = Post::with('user.area')
        ->whereHas('user.area', function ($query) use ($user) {
            $query->where('area_name', $user->area->area_name)
            ->where('block_name', $user->area->block_name);
        })
        ->whereHas('state', function ($query) {
            $query->where('post_active', 'active');
        })->count();

    } else {
        $shop = $user->shop_id;
        $res = Post::where('shop_id', $shop)
        ->whereHas('state', function ($query) {
            $query->where('post_active', 'active');
        })->count();
    }

    return $res;
}

// 店名からID検索
if (! function_exists('findShopId')) {
    function findShopId($keyword)
    {
        $shop = User::where('name', 'like', "%{$keyword}%")
        ->first();

        $res = $shop->shop_id;

        return $res;
    }
}

// ハイフン処理
if (! function_exists('hyphenConvert')) {
    function hyphenConvert($value)
    {
        $hyphens = '/[\x{207B}\x{208B}\x{2010}\x{2012}\x{2013}\x{2014}\x{2015}\x{2212}\x{2500}\x{2501}\x{2796}\x{30FC}\x{3161}\x{FF0D}\x{FF70}]/u';
        $value = mb_convert_kana($value, "KVa");
        $res = preg_replace($hyphens, '-', $value);
        $res = str_replace('-', '', $res);
        return $res;
    }
}
if (! function_exists('hyphenChange')) {
    function hyphenChange($value)
    {
        $hyphens = '/[\x{207B}\x{208B}\x{2010}\x{2012}\x{2013}\x{2014}\x{2015}\x{2212}\x{2500}\x{2501}\x{2796}\x{30FC}\x{3161}\x{FF0D}\x{FF70}]/u';
        $value = mb_convert_kana($value, "KVa");
        $res = preg_replace($hyphens, '-', $value);
        return $res;
    }
}

// セレクト項目
if (! function_exists('selectArray')) {
    function selectArrays()
    {
        $res = [
            'q02',
            'q03',
            'q04',
            'q06',
            'q09',
            'q11',
            'q12',
        ];
        return $res;
    }
}

// コメント項目
if (! function_exists('commentArray')) {
    function commentArrays()
    {
        $res = [
            'q05',
            'q07',
            'q20',
        ];
        return $res;
    }
}

// 既読チェック
if (! function_exists('readOrUnread')) {
    function readOrUnread($user, $data)
    {
        foreach ($data as $datum) {
            $state = json_decode($datum->state->post_read, true);
            
            if (!$state) {
                $state = [
                    'admin' => '',
                    'area_manager' => '',
                    'manager' => '',
                    'shop' => '',
                ];
            }

            $datum->read = $state[$user->role] !== '' ? 'read' : null;
            $datum->readby = implode(' ', array_values($state));
        }

        return $data;
    }
}

// 既読化
if (! function_exists('alreadyRead')) {
    function alreadyRead($user, $data)
    {
        $read = json_decode($data->state->post_read, true);
        if (!$read) {
            $read = [
                'admin' => '',
                'area_manager' => '',
                'manager' => '',
                'shop' => '',
            ];
        }

        $read[$user->role] = $user->role;
        $read = json_encode($read);
        $data->state->update(['post_read' => $read]);

        return $data;
    }
}

// 日付表示調整
if (! function_exists('dateChange')) {
    function dateChange($value)
    {
        $date = $value->format('Y-n-j');
        $today = date('Y-n-j');

        $res = $date == $today ? $value->format('G:i') : $value->format('n月j日');
        $res = date('Y') - $value->format('Y') > 1 ? $value->format('Y/n/j') : $res;

        return $res;
    }
}

// 文字位置調整（〜）
if (! function_exists('tildeCheck')) {
    function tildeCheck($value)
    {
        $tilde = str_contains($value, '60代');
        $res = !empty($tilde) ? 'tilde' : 'notilde';
        return $res;
    }
}

// 60代〜チェック
if (! function_exists('hasTildeCheck')) {
    function hasTildeCheck($data)
    {
        $count = 0;
        
        foreach ($data as $datum) {
            $count = $datum->age == '60代〜' ? $count + 1 : $count;
        }

        $res = $count > 0 ? 'tilde' : 'notilde';
        
        return $res;
    }
}

// ネガティブ回答
if (! function_exists('negatives')) {
    function negatives()
    {
        $res = [
            '満足できなかった',
            'まったく満足できなかった',
            'もう来ないと思う',
            '絶対にもう来ないと思う',
            'そう思わない',
            '全く思わない',
        ];
        return $res;
    }
}

// ネガティブ回答チェック
if (! function_exists('negativeCheck')) {
    function negativeCheck($value)
    {
        $check = in_array($value, negatives());
        $res = $check > 0 ? 'warning' : null;

        return $res;
    }
}

// 質問項目
if (! function_exists('questions')) {
    function questions()
    {
        $res = [
            '',
            '本日ご注文したメニュー',
            'ラーメンの味について',
            'スープについて',
            'チャーシューの味について',
            'ラーメンについてお気づきの点',
            '炒飯について',
            '炒飯についてお気づきの点',
            '餃子の味について',
            '接客について',
            '接客の担当者',
            '店内の清潔感',
            '最近のご来店はいつでしたか？',
            '神座にまた来たいと思いますか？',
            '神座をあなたの友人や知人にお勧めしたいと思いますか？',
            '来店時間帯',
            'ご来店人数',
            '同伴者',
            '神座の公式SNSをフォローしていますか？',
            '公式アプリダウンロードの有無',
            'その他お気づきの点',
        ];
        return $res;
    }
}

// テキストエリア項目
if (! function_exists('textareaCheck')) {
    function textareaCheck($value)
    {
        $items = [
            '本日ご注文したメニュー',
            'ラーメンについてお気づきの点',
            '炒飯についてお気づきの点',
            'その他お気づきの点',
            '神座の公式SNSをフォローしていますか？',
            '神座をあなたの友人や知人にお勧めしたいと思いますか？',
        ];

        $check = in_array($value, $items);
        $res = $check > 0 ? 'textarea' : null;

        return $res;
    }
}

// エクスポート項目
if (! function_exists('exportItems')) {
    function exportItems()
    {
        $res = [
            'created_at',
            'shop_id',
            'age',
            'gender',
            'name',
            'tel',
            'zipcode',
            'address',
            'email',
        ];
        for ($i = 1; $i < 10; $i++) {
            $res[] = 'q0' . $i;
        }
        for ($i = 10; $i < 21; $i++) {
            $res[] = 'q' . $i;
        }

        return $res;
    }
}

// CSV項目
if (! function_exists('csvHeaders')) {
    function csvHeaders()
    {
        $res = [
            '日付',
            '店名',
            '年代',
            '性別',
            '名前',
            '電話番号',
            '郵便番号',
            '住所',
            'メールアドレス',
            '本日ご注文したメニューについて',
            'ラーメンの味について',
            'スープについて',
            'チャーシューの味について',
            'ラーメンについてお気づきの点',
            '炒飯について',
            '炒飯についてお気づきの点',
            '餃子の味について',
            '接客について',
            '接客の担当者',
            '店内の清潔感',
            '最近のご来店はいつでしたか？',
            '神座にまた来たいと思いますか？',
            '神座をあなたの友人や知人にお勧めしたいと思いますか？',
            '来店時間帯',
            'ご来店人数',
            '同伴者',
            '神座の公式SNSをフォローしていますか？',
            '公式アプリダウンロードの有無',
            'その他お気づきの点',
        ];

        return $res;
    }
}

// 個人情報削除
if (! function_exists('deletionPersonalData')) {
    function deletionPersonalData($data)
    {
        $items = [
            'email',
            'name',
            'tel',
            'zipcode',
            'address',
        ];

        foreach ($data as $datum) {
            foreach ($items as $item) {
                $datum->$item = null;
            }    
        }

        return $data;
    }
}

// 個人情報削除（find）
if (! function_exists('deletionPersonalFindData')) {
    function deletionPersonalFindData($data)
    {
        $items = [
            'email',
            'name',
            'tel',
            'zipcode',
            'address',
        ];

        foreach ($items as $item) {
            $data->$item = null;
        }    

        return $data;
    }
}

// 回答構成比
if (! function_exists('compositionRatio')) {
    function compositionRatio($data)
    {
        $cat1 = [
            '非常に満足した',
            '満足した',
            'どちらでもない',
            '満足できなかった',
            'まったく満足できなかった',
        ];

        $cat2 = [
            '満足した',
            'どちらでもない',
            '満足できなかった',
        ];

        $sections = [
            'q02',
            'q03',
            'q04',
            'q06',
            'q08',
            'q09',
        ];

        $res[] = reportDataCreate($data, $sections, $cat1, 'top');
        $res[] = reportDataCreate($data, $sections, $cat2, 'sec');

        return $res;
    }
}

// レポートデータ作成
if (! function_exists('reportDataCreate'))
{
    function reportDataCreate($data, $sections, $cats, $belong)
    {
        $months = array_keys($data->groupBy(function ($row) {
            return $row->created_at->format('Y.m');
        })->all());
        foreach ($data as $datum) {
            $datum->month = $datum->created_at->format('Y.m');
        }

        foreach ($sections as $section) {
            foreach ($months as $month) {
                foreach ($cats as $cat) {
                    if ($belong == 'top') {
                        $filtered = $data->where($section, $cat);
                    } else {
                        $filtered = $data->filter(function ($record) use ($section, $cat) {
                            return strpos($record[$section], $cat) !== false;
                        });
                    }
                    $arrays[$section][$month][] = number_format(round($filtered
                    ->where('month', $month)->count() /
                    $data->where('month', $month)->count() * 100, 1), 1);
                    
                    $reals[$section][$month][] = $filtered->where('month', $month)->count();
                }
                $arrays[$section][$month][] = number_format(round(100 - array_sum($arrays[$section][$month]), 1), 1);
                array_unshift($arrays[$section][$month], $month);

                // $reals[$section][$month][] = $data->where($section, 'どちらでもない')->where('month', $month)->count();
                // array_unshift($reals[$section][$month], $month);
            }
            foreach ($arrays as $array) {
                $chart[$section] = array_values($array);
            }
            foreach ($reals as $real) {
                $tooltip[$section] = array_values($real);
            }
        }
        array_unshift($chart, $sections);
        array_unshift($tooltip, $sections);

        $res[0] = $chart;
        $res[1] = $tooltip;

        return $res;
    }
}

// 表示NGワード
if (! function_exists('ngWords')) {
    function ngWords($value)
    {
        $searches = [
            '混入',
            '異物',
            '虫',
            '蟲',
            'ムシ',
            '幼虫',
            'ようちゅう',
            '昆虫',
            'こんちゅう',
            '蜚蠊',
            '御器噛',
            '御器嚙',
            'ゴキブリ',
            'ゴキ',
            'ゴキカブリ',
            'G',
            'Ｇ',
            'ごきぶり',
            '油虫',
            'アブラムシ',
            'あぶらむし',
            '芥虫',
            '阿久多牟之',
            'あくたむし',
            '都乃牟之',
            'つのむし',
            'コックローチ',
            'こっくろーち',
            'COCKROACH',
            'Cockroach',
            'cockroach',
            '蝿',
            'ハエ',
            '小蝿',
            'コバエ',
            '蛆',
            'ウジ',
            '蜂',
            '蚊',
            '百足',
            '蜈蜙',
            '蜈蚣',
            '蝍蛆',
            '螏蟍',
            'ムカデ',
            '蜘蛛',
            'クモ',
            '守宮',
            '家守',
            'ヤモリ',
            'やもり',
            '井守',
            'イモリ',
            'いもり',
            '芋虫',
            'イモムシ',
            'いもむし',
            '蝉',
            'セミ',
            '青虫',
            'アオムシ',
            'あおむし',
            '蛞蝓',
            'ナメクジ',
            'なめくじ',
            '蝸牛',
            'カタツムリ',
            'かたつむり',
            '瓢虫',
            'テントウムシ',
            'てんとう虫',
            'てんとうむし',
            '蝶',
            'チョウチョ',
            'ちょうちょ',
            '亀虫',
            'カメムシ',
            'かめむし',
            '鼠',
            'ネズミ',
            'ねずみ',
            '蛙',
            'カエル',
            '寄生虫',
            'アニサキス',
            'ダニ',
            'ノミ',
            '針金',
            'ハリガネ',
            'はりがね',
            'ボルト',
            'ナット',
            'ワイヤー',
            'ブラシ',
            '螺子',
            'ネジ',
            '破片',
            '木片',
            '金属片',
            '樹脂',
            '糸屑',
            '糸クズ',
            '糸くず',
            '砂',
            'プラスチック',
            'ビニール',
            'ゴム',
            'ガラス',
            '絆創膏',
            'ばんそうこう',
            'サビオ',
            'カットバン',
            'バンドエイド',
            'キズバン',
            'リバテープ',
            '手袋',
            '髪',
            '爪',
            'ツメ',
            'ネイル',
            '睫毛',
            'マツ毛',
            'マツゲ',
            'まつ毛',
            'ツケマ',
            '眉毛',
            'マユ毛',
            'まゆ毛',
            '臑毛',
            '脇毛',
            'ワキ毛',
            'わき毛',
            'スネ毛',
            'すね毛',
            '腕毛',
            'ウデ毛',
            'うで毛',
            '胸毛',
            'ムナ毛',
            'むな毛',
            '陰毛',
            'ちじれた毛',
            'ちぢれた毛',
            'ちじれ毛',
            'ちぢれ毛',
            '髭',
            'ヒゲ',
            '皮膚',
            '人の歯',
            '入歯',
            '入れ歯',
            '金歯',
            '銀歯',
            '唾液',
            '煙草',
            'タバコ',
            'たばこ',
        ];
        foreach ($searches as $search) {
            $result = strpos($value, $search);
            if ($result !== false) {
                break;
            }
        };
        if($result !== false) {
            $res = 'NG';
        } else {
            $res = null;
        }
        return $res;
    }
}

// 投稿内容ネガティブワード
if (! function_exists('wordCheck')) {
    function wordCheck($value)
    {
        $searches = [
            '不味い',
            'まずい',
            'マズイ',
            'マズい',
            '不味かった',
            'まずかった',
            'マズかった',
            '美味しく無い',
            '美味しくない',
            'おいしく無い',
            'おいしくない',
            '美味しく無かった',
            '美味しくなかった',
            'おいしく無かった',
            'おいしくなかった',
            '美味く無い',
            '美味くない',
            'うまく無い',
            'うまくない',
            '駄目',
            'ダメ',
            '悪い',
            'わるい',
            '汚い',
            'キタナイ',
            'きたない',
            '臭い',
            'クサイ',
            'くさい',
            '気になる',
            '気に入らない',
            '気にいらない',
            'イマイチ',
            'いまいち',
            'ムカツク',
            'ムカつく',
            'むかつく',
            '腹立たしい',
            '腹がたつ',
            '気に食わない',
            '気にくわない',
            'ガッカリ',
            'がっかり',
            '残念',
            'ザンネン',
            'ざんねん',
            '遅い',
            'おそい',
            '態度が',
            '従業員の',
            '店員の',
            '悪質',
            '悪辣',
            '嘲る',
            '侮る',
            '意気消沈',
            '違反',
            '違法',
            '嫌い',
            'キライ',
            'きらい',
            '嫌がらせ',
            'いやがらせ',
            '嫌気',
            '横柄',
            '過度',
            '悲しい',
            'かなしい',
            '軽々しい',
            '偽装',
            '恐怖',
            '空虚',
            '苦情',
            '苦渋',
            'くだらない',
            '屈辱',
            '愚鈍',
            '愚弄',
            '軽視',
            '軽率',
            '下衆',
            'ゲス',
            '下賤',
            'くだらない',
            '下品',
            '下劣',
            '心無い',
            'こころない',
            '最低',
            '詐欺',
            '蔑む',
            '差別',
            'さもしい',
            '惨憺',
            '嫌悪',
            '失意',
            '失礼',
            '醜態',
            '粗悪',
            '俗悪',
            '損なう',
            '粗末',
            '怠惰',
            '怠慢',
            'たわけ',
            '短慮',
            '稚拙',
            '恥辱',
            '拙い',
            'つたない',
            'つまらない',
            '低俗',
            '鈍感',
            '鈍い',
            'ネガティブ',
            '馬鹿',
            'バカ',
            'はた迷惑',
            '迷惑',
            '非合法',
            '悲惨',
            '非礼',
            '貧相',
            '無愛想',
            '不安',
            '不恰好',
            '不潔',
            'フケツ',
            'ふけつ',
            '不細工',
            'ブサイク',
            'ぶさいく',
            '無作法',
            '無様',
            '不躾',
            '不当',
            '腐敗',
            '侮蔑',
            '軽蔑',
            '侮辱',
            '不法',
            '下手',
            '蔑視',
            '間抜け',
            'マヌケ',
            '見下す',
            '惨め',
            'みじめ',
            '未熟',
            '醜い',
            '無気力',
            '無視',
            'マイナス',
            '虫',
            '蝿',
            'ハエ',
            'ゴキブリ',
            '蟻',
            '蜂',
            'ムシ',
            '混入',
            'ぬるい',
            'ぬるかった',
        ];

        foreach ($searches as $search) {
            $result = strpos($value, $search);
            if ($result !== false) {
                break;
            }
        };
        $res =  $result !== false ? 'warning' : ' good';

        return $res;
    }
}