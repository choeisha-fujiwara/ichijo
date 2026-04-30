<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleVenue;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VenueDeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['users', 'articles', 'article_venues'] as $table) {
            if (!Schema::hasTable($table)) {
                $this->markTestSkipped("Required table [{$table}] is not available in the test database.");
            }
        }
    }

    public function test_unused_venue_can_be_soft_deleted(): void
    {
        $user = $this->createUser('venue-delete-ok@example.com');
        $venue = $this->createVenue();

        $response = $this->actingAs($user)->delete(route('venue.destroy', $venue));

        $response->assertRedirect(route('venue.index', absolute: false));
        $response->assertSessionHas('msg', '会場情報を削除しました');

        $this->assertSoftDeleted('article_venues', [
            'id' => $venue->id,
        ]);
    }

    public function test_used_venue_cannot_be_deleted(): void
    {
        $user = $this->createUser('venue-delete-ng@example.com');
        $venue = $this->createVenue();

        Article::create([
            'user_id' => $user->id,
            'title' => '利用中会場の記事',
            'body' => '<p>本文</p>',
            'venue_id' => $venue->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->delete(route('venue.destroy', $venue));

        $response->assertRedirect(route('venue.show', $venue, absolute: false));
        $response->assertSessionHas('msg', '利用中の記事があるため削除できません。');

        $this->assertDatabaseHas('article_venues', [
            'id' => $venue->id,
            'deleted_at' => null,
        ]);
    }

    private function createVenue(): ArticleVenue
    {
        return ArticleVenue::create([
            'venue_name' => '削除検証会場',
            'address' => '東京都千代田区',
            'phone' => '03-0000-0000',
        ]);
    }

    private function createUser(string $email): User
    {
        return User::forceCreate([
            'name' => '会場テストユーザー',
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'admin',
            'affiliation' => 'test',
        ]);
    }
}
