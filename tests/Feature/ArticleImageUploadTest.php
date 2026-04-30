<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleVenue;
use App\Models\Image;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleImageUploadTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['users', 'articles', 'images', 'article_venues'] as $table) {
            if (!Schema::hasTable($table)) {
                $this->markTestSkipped("Required table [{$table}] is not available in the test database.");
            }
        }
    }

    public function test_article_can_be_created_with_header_and_body_images(): void
    {
        Storage::fake('public');

        $user = $this->createUser('create-test@example.com');
        $venue = $this->createVenue();

        $response = $this->actingAs($user)->post(route('article.store'), [
            'title' => '画像付き記事',
            'body' => '<p>本文です</p>',
            'manager' => '担当者',
            'venue_id' => $venue->id,
            'header_image' => UploadedFile::fake()->image('header.jpg', 1200, 800),
            'body_image' => [
                UploadedFile::fake()->image('body.jpg', 1200, 800),
            ],
        ]);

        $response->assertRedirect(route('article.index', absolute: false));

        $article = Article::with('images')->latest('id')->firstOrFail();

        $this->assertNotNull($article->header_image);
        $this->assertIsArray($article->body_image);
        $this->assertCount(1, $article->body_image);
        $this->assertCount(2, $article->images);

        Storage::disk('public')->assertExists($article->header_image);
        Storage::disk('public')->assertExists($article->body_image[0]);
    }

    public function test_article_images_can_be_replaced_on_update(): void
    {
        Storage::fake('public');

        $user = $this->createUser('update-test@example.com');
        $venue = $this->createVenue();

        Storage::disk('public')->put('uploads/header/original-header.webp', 'old-header');
        Storage::disk('public')->put('uploads/body/original-body.webp', 'old-body');

        $article = Article::create([
            'user_id' => $user->id,
            'title' => '更新前記事',
            'body' => '<p>更新前</p>',
            'header_image' => 'uploads/header/original-header.webp',
            'body_image' => ['uploads/body/original-body.webp'],
            'manager' => '担当者',
            'venue_id' => $venue->id,
            'status' => 'draft',
        ]);

        Image::create([
            'article_id' => $article->id,
            'path' => 'uploads/header/original-header.webp',
            'original_name' => 'original-header.webp',
            'size' => 10,
            'mime_type' => 'image/webp',
            'sort_order' => 0,
        ]);

        Image::create([
            'article_id' => $article->id,
            'path' => 'uploads/body/original-body.webp',
            'original_name' => 'original-body.webp',
            'size' => 8,
            'mime_type' => 'image/webp',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)->put(route('article.update', $article), [
            'title' => '更新後記事',
            'body' => '<p>更新後</p>',
            'manager' => '担当者',
            'venue_id' => $venue->id,
            'header_image' => UploadedFile::fake()->image('new-header.jpg', 1200, 800),
            'body_image' => [
                UploadedFile::fake()->image('new-body.jpg', 1200, 800),
            ],
        ]);

        $response->assertRedirect(route('top.show', $article, absolute: false));

        $article->refresh();

        $this->assertSame('更新後記事', $article->title);
        $this->assertNotSame('uploads/header/original-header.webp', $article->header_image);
        $this->assertIsArray($article->body_image);
        $this->assertCount(1, $article->body_image);
        $this->assertNotSame('uploads/body/original-body.webp', $article->body_image[0]);

        Storage::disk('public')->assertExists($article->header_image);
        Storage::disk('public')->assertExists($article->body_image[0]);

        $this->assertDatabaseMissing('images', [
            'article_id' => $article->id,
            'path' => 'uploads/header/original-header.webp',
        ]);

        $this->assertDatabaseMissing('images', [
            'article_id' => $article->id,
            'path' => 'uploads/body/original-body.webp',
        ]);
    }

    private function createVenue(): ArticleVenue
    {
        return ArticleVenue::create([
            'venue_name' => 'テスト会場',
            'address' => '大阪市北区',
            'phone' => '06-0000-0000',
        ]);
    }

    private function createUser(string $email): User
    {
        return User::forceCreate([
            'name' => 'テストユーザー',
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'admin',
            'affiliation' => 'test',
        ]);
    }
}