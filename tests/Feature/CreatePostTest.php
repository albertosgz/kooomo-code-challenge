<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    use RefreshDatabase;

    const URL_HOSTNAME = 'http://kooomo-code-challenge.test';

    /**
     * @group post
     */
    public function test_cannot_create_post_when_not_logged_in()
    {
        $this
            ->jsonApi()
            ->expects('posts')
            ->withJson([
                'data' => [
                    'type' => 'posts',
                    'attributes' => [
                        'content' => 'Content foo bar',
                        'is_published' => false,
                        'slug' => 'creating-jsonapi-resources',
                        'title' => 'Title'
                    ],
                ]
            ])
            ->post('/api/v1/posts/')
            ->assertStatus(401);
    }

    /**
     * @group post
     */
    public function test_able_to_create_post_when_logged_in()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test');

        $post = Post::factory()->make();

        $data = [
            'data' => [
                'type' => 'posts',
                'attributes' => [
                    'content' => $post->content,
                    'slug' => $post->slug,
                    'title' => $post->title,
                    'is_published' => false,
                ],
            ]
        ];

        $response = $this
            ->jsonApi()
            ->expects('posts')
            ->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->withJson($data)
            ->post('/api/v1/posts/')
            ->assertStatus(201);

        $id = $response->id();

        $this->assertDatabaseHas('posts', [
            'id' => $id,
            'content' => $post->content,
            'title' => $post->title,
            'slug' => $post->slug,
            'is_published' => false,
            'author_id' => $user->id,
        ]);
    }

    /**
     * @group post
     */
    public function test_cannot_create_post_with_repeated_slug()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test');

        $this
            ->jsonApi()
            ->expects('posts')
            ->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->withJson([
                'data' => [
                    'type' => 'posts',
                    'attributes' => [
                        'content' => 'Content foo bar',
                        'is_published' => false,
                        'slug' => 'creating-jsonapi-resources',
                        'title' => 'Title foo bar'
                    ],
                ]
            ])
            ->post('/api/v1/posts/')
            ->assertStatus(201);

        $this
            ->jsonApi()
            ->expects('posts')
            ->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->withJson([
                'data' => [
                    'type' => 'posts',
                    'attributes' => [
                        'content' => 'Content foo bar',
                        'is_published' => false,
                        'slug' => 'creating-jsonapi-resources',
                        'title' => 'Title foo bar'
                    ],
                ]
            ])
            ->post('/api/v1/posts/')
            ->assertStatus(422);
    }
}
