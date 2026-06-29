<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ReservationSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GuestReservationCapacityTest extends TestCase
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

    public function test_guest_reservation_is_rejected_when_slot_capacity_is_zero(): void
    {
        $user = $this->createUser('guest-capacity-zero@example.com');
        $article = Article::create([
            'user_id' => $user->id,
            'title' => '予約受付テスト記事',
            'body' => '<p>本文</p>',
            'status' => 'publish',
            'published_at' => now()->subDay(),
            'unpublished_at' => now()->addDay(),
        ]);
        $slot = ReservationSlot::create([
            'article_id' => $article->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'capacity' => 0,
            'reserved_count' => 0,
        ]);

        $response = $this
            ->from(route('show.public', $article->public_token, absolute: false))
            ->post(route('reservation.store', absolute: false), $this->validPayload($article->id, $slot->id));

        $response->assertSessionHasErrors(['reservation_slot_id']);

        $this->assertDatabaseCount('reservations', 0);
        $this->assertDatabaseHas('reservation_slots', [
            'id' => $slot->id,
            'reserved_count' => 0,
        ]);
    }

    private function createUser(string $email): User
    {
        return User::forceCreate([
            'name' => '予約テストユーザー',
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'admin',
            'affiliation' => 'test',
        ]);
    }

    private function validPayload(int $articleId, int $slotId): array
    {
        return [
            'article_id' => $articleId,
            'reservation_slot_id' => $slotId,
            'reservation_datetime' => now()->addDay()->format('Y-m-d 10:00'),
            'first_name' => '太郎',
            'last_name' => '山田',
            'first_name_kana' => 'タロウ',
            'last_name_kana' => 'ヤマダ',
            'postal_code_1' => '100',
            'postal_code_2' => '0001',
            'address_prefectures' => '東京都',
            'address_municipalities' => '千代田区',
            'address_detail' => '1-1-1',
            'address_building' => 'テストビル',
            'phone-1' => '03',
            'phone-2' => '1234',
            'phone-3' => '5678',
            'email' => 'guest@example.com',
            'email_confirmation' => 'guest@example.com',
            'memo' => 'テスト予約',
            'privacy_policy' => '1',
        ];
    }
}