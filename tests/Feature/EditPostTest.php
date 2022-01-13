<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class EditPostTest extends TestCase
{
    use RefreshDatabase;

    const URL_HOSTNAME = 'http://kooomo-code-challenge.test';

    public function test_cannot_edit_post_when_not_logged_in()
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create();

        $data = [
            'type' => 'posts',
            'id' => (string) $post->getRouteKey(),
            'attributes' => [
                'content' => 'This is new article content.',
                'slug' => 'article-is-updated',
                'title' => 'Updated Article',
            ],
        ];

        $this
            ->jsonApi()
            ->expects('posts')
            ->withData($data)
            ->patch('/api/v1/posts/' . $post->getRouteKey())
            ->assertStatus(401);
    }

    public function test_able_to_edit_post_when_logged_in_and_author()
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create();

        $data = [
            'type' => 'posts',
            'id' => (string) $post->getRouteKey(),
            'attributes' => [
                'content' => 'This is new article content.',
                'slug' => 'article-is-updated',
                'title' => 'Updated Article',
            ],
        ];

        $response = $this
            ->actingAs($user)
            ->jsonApi()
            ->expects('posts')
            ->withData($data)
            ->patch('/api/v1/posts/' . $post->getRouteKey())
            ->assertStatus(200);


        $response->assertFetchedOne($data);

        $this->assertDatabaseHas('posts', [
            'id' => $post->getKey(),
            'author_id' => $user->id,
            'content' => $data['attributes']['content'],
            'slug' => $data['attributes']['slug'],
            'title' => $data['attributes']['title'],
        ]);
    }

    public function test_cannot_edit_post_when_logged_in_and_different_author()
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create();

        $data = [
            'type' => 'posts',
            'id' => (string) $post->getRouteKey(),
            'attributes' => [
                'content' => 'This is new article content.',
                'slug' => 'article-is-updated',
                'title' => 'Updated Article',
            ],
        ];

        $this
            ->actingAs($anotherUser)
            ->jsonApi()
            ->expects('posts')
            ->withData($data)
            ->patch('/api/v1/posts/' . $post->getRouteKey())
            ->assertStatus(403);
    }

    public function test_cannot_edit_post_with_repeated_slug()
    {
        $user = User::factory()->create();

        $post = Post::factory()
            ->for($user, 'author')
            ->create();

        $post2 = Post::factory()
            ->for($user, 'author')
            ->create();

        $data = [
            'type' => 'posts',
            'id' => (string) $post->getRouteKey(),
            'attributes' => [
                'slug' => $post2->slug,
            ],
        ];

        $this
            ->actingAs($user)
            ->jsonApi()
            ->expects('posts')
            ->withData($data)
            ->patch('/api/v1/posts/' . $post->getRouteKey())
            ->assertStatus(422);
    }
}
