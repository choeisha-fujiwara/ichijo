<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    private static array $titles = [
        '春の料理教室開催のお知らせ',
        '地域交流イベント「ふれあいフェスタ2026」',
        '子ども向けプログラミング体験ワークショップ',
        'シニア向け健康講座 〜元気に歩こう！〜',
        '伝統工芸体験：陶芸と絵付けを楽しもう',
        '親子で楽しむ自然観察会 in 市民の森',
        '夏祭り前夜祭 ライブ＆縁日コーナー',
        'ビジネスマナー研修セミナー（無料）',
        'マルシェde朝市 ─ 旬の野菜と手作り品が集合',
        '読書会：今月の課題本を語り合おう',
    ];

    private static array $categories = ['イベント', 'ワークショップ', 'セミナー', 'マルシェ', '体験', '講座'];

    private static array $bodies = [
        '<p>今年も恒例の料理教室を開催します。旬の食材を使ったヘルシーレシピを一緒に作りましょう。初心者も大歓迎です。定員20名、先着順のため早めのご予約をお待ちしております。</p>',
        '<p>地域のみなさんが一堂に会するふれあいフェスタ。ステージ発表や模擬店、体験コーナーなど盛りだくさんの内容でお届けします。家族みんなで楽しめる一日にしましょう。</p>',
        '<p>小学生を対象にしたプログラミング体験ワークショップを開催します。スクラッチを使ってゲームを作る楽しさを体感してください。保護者の見学も可能です。</p>',
        '<p>毎回好評のシニア向け健康講座。理学療法士を招き、自宅でできる筋力トレーニングと正しい歩き方を学びます。運動が苦手な方も安心して参加できます。</p>',
        '<p>地元の陶芸家を講師に迎え、ろくろ体験と絵付け体験の2コースをご用意しました。自分だけのオリジナル作品を完成させて持ち帰れます。</p>',
        '<p>自然豊かな市民の森で親子一緒に植物や昆虫を観察しましょう。専門家のガイドが同行するので安心です。動きやすい服装と飲み物をご持参ください。</p>',
        '<p>夏祭りの前夜を盛り上げる前夜祭イベント！地元バンドのライブ演奏のほか、射的・焼き鳥・かき氷など縁日コーナーも充実しています。浴衣での来場大歓迎です。</p>',
        '<p>社会人向けのビジネスマナー研修を無料で実施します。名刺交換・電話対応・メールの書き方など基礎から実践的なスキルまで丁寧に解説します。定員30名。</p>',
        '<p>毎月第2日曜日に開催する朝市。地元農家の新鮮野菜、ハンドメイド雑貨、焼き菓子など約40ブースが出店します。早起きは三文の徳、ぜひお越しください。</p>',
        '<p>今月の課題本は「博士の愛した数式」です。読後の感想や気になったシーンを自由に語り合う穏やかな読書会です。未読の方もテーマ「記憶と愛」で参加できます。</p>',
    ];

    public function definition(): array
    {
        static $index = 0;
        $i = $index % 10;
        $index++;

        $publishedAt = $this->faker->dateTimeBetween('2026-04-01', '2026-06-30');
        $unpublishedAt = (clone $publishedAt)->modify('+30 days');

        return [
            'user_id' => User::first()->id ?? 1,
            'title' => self::$titles[$i],
            'body' => self::$bodies[$i],
            'category' => $this->faker->randomElement(self::$categories),
            'status' => $this->faker->randomElement(['draft', 'draft', 'published']),
            'published_at' => $publishedAt,
            'unpublished_at' => $unpublishedAt,
            'freeword_1' => null,
            'freeword_2' => null,
            'header_image' => null,
            'body_image' => null,
            'memo' => $this->faker->boolean(40)
                ? $this->faker->realTextBetween(20, 60)
                : null,
            'emails' => $this->faker->boolean(50)
                ? [$this->faker->safeEmail(), $this->faker->safeEmail()]
                : null,
        ];
    }
}
