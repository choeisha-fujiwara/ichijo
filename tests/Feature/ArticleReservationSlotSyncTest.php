<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Reservation;
use App\Models\ReservationSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ArticleReservationSlotSyncTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['users', 'articles', 'reservation_slots', 'reservations'] as $table) {
            if (! Schema::hasTable($table)) {
                $this->markTestSkipped("Required table [{$table}] is not available in the test database.");
            }
        }
    }

    public function test_updating_article_slots_keeps_existing_slot_id_for_linked_reservations(): void
    {
        $user = $this->createUser('article-slot-sync@example.com');

        $article = Article::create([
            'user_id' => $user->id,
            'title' => '枠更新テスト',
            'body' => '<p>本文</p>',
            'status' => 'draft',
        ]);

        $slot = ReservationSlot::create([
            'article_id' => $article->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'capacity' => 5,
            'reserved_count' => 1,
        ]);

        $reservation = Reservation::create([
            'article_id' => $article->id,
            'reservation_slot_id' => $slot->id,
            'reservation_datetime' => now()->addDay()->format('Y-m-d 10:00'),
            'firstname' => '太郎',
            'lastname' => '山田',
            'firstname_kana' => 'タロウ',
            'lastname_kana' => 'ヤマダ',
            'zipcode' => '100-0001',
            'prefecture' => '東京都',
            'city' => '千代田区',
            'address' => '1-1-1',
            'building' => null,
            'phone' => '03-1234-5678',
            'email' => 'slot-sync-guest@example.com',
            'memo' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('article.update', $article, absolute: false), [
                'title' => '枠更新テスト（更新）',
                'body' => '<p>更新本文</p>',
                'slots' => [
                    [
                        'id' => $slot->id,
                        'date' => now()->addDay()->toDateString(),
                        'start_hour' => '11',
                        'start_minute' => '00',
                        'end_hour' => '12',
                        'end_minute' => '00',
                        'capacity' => 8,
                    ],
                ],
            ]);

        $response->assertRedirect(route('top.show', $article, absolute: false));
        $response->assertSessionHasNoErrors();

        $reservation->refresh();
        $this->assertSame($slot->id, (int) $reservation->reservation_slot_id);

        $this->assertDatabaseHas('reservation_slots', [
            'id' => $slot->id,
            'article_id' => $article->id,
            'start_time' => '11:00:00',
            'end_time' => '12:00:00',
            'capacity' => 8,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseCount('reservation_slots', 1);
    }

    private function createUser(string $email): User
    {
        return User::forceCreate([
            'name' => '記事枠同期テストユーザー',
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'admin',
            'affiliation' => 'test',
        ]);
    }
}