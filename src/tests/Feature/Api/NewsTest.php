<?php

namespace Tests\Feature\Api;

use App\Models\Comment;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_news(): void
    {
        $newsData = [
            'title' => 'Test News from unittt',
            'slug' => 'test-news',
            'description' => 'test desc!!!',
            'is_published' => true,
        ];

        $response = $this->postJson('/api/news', $newsData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'description',
                    'is_published',
                ],
            ]);

        $this->assertDatabaseHas('news', [
            'title' => 'Test News from unittt',
            'slug' => 'test-news',
        ]);
    }

    public function test_can_get_list_of_news(): void
    {
        News::factory()->count(5)->create();

        $response = $this->getJson('/api/news');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'description',
                    ],
                ],
            ]);
    }

    public function test_can_get_single_news(): void
    {
        $news = News::factory()->create();

        $response = $this->getJson("/api/news/{$news->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'description',
                ],
                'comments' => [
                    'data',
                    'meta' => [
                        'next_cursor',
                        'has_more',
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $news->id,
                    'title' => $news->title,
                ],
            ]);
    }

    public function test_can_update_news(): void
    {
        $news = News::factory()->create([
            'title' => 'bad Title aefaoeuhgoai3uhgoaisudg',
            'slug' => 'original-slug',
        ]);

        $updateData = [
            'title' => 'Updated Title good',
            'slug' => 'updated-slug',
        ];

        $response = $this->putJson("/api/news/{$news->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $news->id,
                    'title' => 'Updated Title good',
                    'slug' => 'updated-slug',
                ],
            ]);

        $this->assertDatabaseHas('news', [
            'id' => $news->id,
            'title' => 'Updated Title good',
        ]);
    }

    public function test_can_delete_news(): void
    {
        $news = News::factory()->create();

        $response = $this->deleteJson("/api/news/{$news->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'News deleted successfully',
            ]);

        $this->assertSoftDeleted('news', [
            'id' => $news->id,
        ]);
    }

    public function test_cursor_pagination_for_comments(): void
    {
        $news = News::factory()->create();
        $user = User::factory()->create();

        // Create comments with different timestamps to ensure proper cursor pagination
        $baseTime = now()->subMinutes(25);
        for ($i = 0; $i < 25; $i++) {
            Comment::factory()->create([
                'commentable_type' => News::class,
                'commentable_id' => $news->id,
                'user_id' => $user->id,
                'created_at' => $baseTime->copy()->addMinutes($i),
                'updated_at' => $baseTime->copy()->addMinutes($i),
            ]);
        }

        $response = $this->getJson("/api/news/{$news->id}?limit=20");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'comments' => [
                    'data',
                    'meta' => [
                        'next_cursor',
                        'has_more',
                    ],
                ],
            ]);

        $commentsData = $response->json('comments');
        $this->assertCount(20, $commentsData['data']);
        $this->assertTrue($commentsData['meta']['has_more']);
        $this->assertNotNull($commentsData['meta']['next_cursor']);

        $nextCursor = $commentsData['meta']['next_cursor'];
        $response2 = $this->getJson("/api/news/{$news->id}?cursor={$nextCursor}&limit=20");

        $response2->assertStatus(200);
        $commentsData2 = $response2->json('comments');
        $this->assertCount(5, $commentsData2['data']);
        $this->assertFalse($commentsData2['meta']['has_more']);
    }

    public function test_comments_are_empty_when_no_comments_exist(): void
    {
        $news = News::factory()->create();

        $response = $this->getJson("/api/news/{$news->id}");

        $response->assertStatus(200);
        $commentsData = $response->json('comments');
        $this->assertCount(0, $commentsData['data']);
        $this->assertFalse($commentsData['meta']['has_more']);
        $this->assertNull($commentsData['meta']['next_cursor']);
    }
}
