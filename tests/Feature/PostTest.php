<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    const URL_HOSTNAME = 'http://kooomo-code-challenge.test';

    public function test_default_post_route_available()
    {
        $response = $this->get('/api/v1/posts');

        $response->assertStatus(200);
    }

    public function test_first_post_not_available()
    {
        $response = $this->get('/api/v1/posts/1');

        $response->assertStatus(404);
    }

    public function test_get_first_post() {
        $posts = Post::factory()
            ->count(2)
            ->for(User::factory()->state([
                'email' => 'test@test.com',
            ]), 'author')
            ->sequence(fn ($sequence) => [
                'title' => 'Title '.$sequence->index,
                'content' => 'Content '.$sequence->index,
                'slug' => 'slug-'.$sequence->index,
                'created_at' => '2022-01-01 00:00:01',
                'updated_at' => '2022-01-01 00:00:01',
            ])
            ->create([
                'is_published' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@test.com',
        ]);
        $this->assertDatabaseHas('posts', [
            'title' => 'Title 1',
        ]);

        $response = $this->getJson('/api/v1/posts');

        $response->assertStatus(200)
            ->assertExactJson([
                'jsonapi' => [
                    'version' => '1.0',
                ],
                'data' => [
                    [
                        'type' => 'posts',
                        'id' => '1',
                        'links' => [
                            'self' => self::URL_HOSTNAME . '/api/v1/posts/1',
                        ],
                        'attributes' => [
                            'title' => 'Title 0',
                            'slug' => 'slug-0',
                            'content' => 'Content 0',
                            'createdAt' => '2022-01-01T00:00:01.000000Z',
                            'updatedAt' => '2022-01-01T00:00:01.000000Z',
                        ]
                    ],
                    [
                        'type' => 'posts',
                        'id' => '2',
                        'links' => [
                            'self' => self::URL_HOSTNAME . '/api/v1/posts/2',
                        ],
                        'attributes' => [
                            'title' => 'Title 1',
                            'slug' => 'slug-1',
                            'content' => 'Content 1',
                            'createdAt' => '2022-01-01T00:00:01.000000Z',
                            'updatedAt' => '2022-01-01T00:00:01.000000Z',
                        ]
                    ]
                ],
            ]);
    }
}
