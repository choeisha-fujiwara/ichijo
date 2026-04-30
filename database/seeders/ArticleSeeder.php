<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            [
                'title' => '春の料理教室開催のお知らせ',
                'body' => '<p>今年も恒例の料理教室を開催します。旬の食材を使ったヘルシーレシピを一緒に作りましょう。初心者も大歓迎です。定員20名、先着順のため早めのご予約をお待ちしております。</p>',
                'category' => '講座',
                'status' => 'published',
                'published_at' => '2026-04-15',
                'unpublished_at' => '2026-05-15',
                'emails' => json_encode(['info@example.com']),
            ],
            [
                'title' => '地域交流イベント「ふれあいフェスタ2026」',
                'body' => '<p>地域のみなさんが一堂に会するふれあいフェスタ。ステージ発表や模擬店、体験コーナーなど盛りだくさんの内容でお届けします。家族みんなで楽しめる一日にしましょう。</p>',
                'category' => 'イベント',
                'status' => 'published',
                'published_at' => '2026-04-20',
                'unpublished_at' => '2026-05-20',
                'emails' => null,
            ],
            [
                'title' => '子ども向けプログラミング体験ワークショップ',
                'body' => '<p>小学生を対象にしたプログラミング体験ワークショップを開催します。スクラッチを使ってゲームを作る楽しさを体感してください。保護者の見学も可能です。</p>',
                'category' => 'ワークショップ',
                'status' => 'draft',
                'published_at' => '2026-05-01',
                'unpublished_at' => '2026-05-31',
                'emails' => json_encode(['workshop@example.com', 'support@example.com']),
            ],
            [
                'title' => 'シニア向け健康講座 〜元気に歩こう！〜',
                'body' => '<p>毎回好評のシニア向け健康講座。理学療法士を招き、自宅でできる筋力トレーニングと正しい歩き方を学びます。運動が苦手な方も安心して参加できます。</p>',
                'category' => '講座',
                'status' => 'published',
                'published_at' => '2026-04-25',
                'unpublished_at' => '2026-05-25',
                'emails' => null,
            ],
            [
                'title' => '伝統工芸体験：陶芸と絵付けを楽しもう',
                'body' => '<p>地元の陶芸家を講師に迎え、ろくろ体験と絵付け体験の2コースをご用意しました。自分だけのオリジナル作品を完成させて持ち帰れます。</p>',
                'category' => '体験',
                'status' => 'published',
                'published_at' => '2026-05-03',
                'unpublished_at' => '2026-06-03',
                'emails' => json_encode(['craft@example.com']),
            ],
            [
                'title' => '親子で楽しむ自然観察会 in 市民の森',
                'body' => '<p>自然豊かな市民の森で親子一緒に植物や昆虫を観察しましょう。専門家のガイドが同行するので安心です。動きやすい服装と飲み物をご持参ください。</p>',
                'category' => 'イベント',
                'status' => 'published',
                'published_at' => '2026-05-10',
                'unpublished_at' => '2026-06-10',
                'emails' => null,
            ],
            [
                'title' => '夏祭り前夜祭 ライブ＆縁日コーナー',
                'body' => '<p>夏祭りの前夜を盛り上げる前夜祭イベント！地元バンドのライブ演奏のほか、射的・焼き鳥・かき氷など縁日コーナーも充実しています。浴衣での来場大歓迎です。</p>',
                'category' => 'イベント',
                'status' => 'draft',
                'published_at' => '2026-07-15',
                'unpublished_at' => '2026-08-15',
                'emails' => json_encode(['festival@example.com', 'info@example.com']),
            ],
            [
                'title' => 'ビジネスマナー研修セミナー（無料）',
                'body' => '<p>社会人向けのビジネスマナー研修を無料で実施します。名刺交換・電話対応・メールの書き方など基礎から実践的なスキルまで丁寧に解説します。定員30名。</p>',
                'category' => 'セミナー',
                'status' => 'published',
                'published_at' => '2026-05-20',
                'unpublished_at' => '2026-06-20',
                'emails' => null,
            ],
            [
                'title' => 'マルシェde朝市 ─ 旬の野菜と手作り品が集合',
                'body' => '<p>毎月第2日曜日に開催する朝市。地元農家の新鮮野菜、ハンドメイド雑貨、焼き菓子など約40ブースが出店します。早起きは三文の徳、ぜひお越しください。</p>',
                'category' => 'マルシェ',
                'status' => 'published',
                'published_at' => '2026-05-08',
                'unpublished_at' => '2026-06-08',
                'emails' => json_encode(['marche@example.com']),
            ],
            [
                'title' => '読書会：今月の課題本を語り合おう',
                'body' => '<p>今月の課題本は「博士の愛した数式」です。読後の感想や気になったシーンを自由に語り合う穏やかな読書会です。未読の方もテーマ「記憶と愛」で参加できます。</p>',
                'category' => 'イベント',
                'status' => 'published',
                'published_at' => '2026-05-25',
                'unpublished_at' => '2026-06-25',
                'emails' => null,
            ],
        ];

        $userId = \App\Models\User::first()->id ?? 1;
        $now = now()->toDateTimeString();

        foreach ($records as $record) {
            Article::create(array_merge($record, [
                'user_id' => $userId,
            ]));
        }
    }
}
