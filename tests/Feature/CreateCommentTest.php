<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateCommentTest extends TestCase
{
    use RefreshDatabase;

    const URL_HOSTNAME = 'http://kooomo-code-challenge.test';

    /**
     * @group comment
     */
    public function test_cannot_create_comment_when_not_logged_in()
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'author')->create();

        $this
            ->jsonApi()
            ->expects('comments')
            ->withJson([
                'data' => [
                    'type' => 'comments',
                    'attributes' => [
                        'content' => 'foo bar',
                        'is_published' => false,
                    ],
                    'relationships' => [
                        'post' => [
                            'data' => [
                                'id' => (string) $post->getRouteKey(),
                                'type' => 'posts',
                            ]
                        ],
                    ]
                ]
            ])
            ->post('/api/v1/comments/')
            ->assertStatus(401);
    }

    /**
     * @group comment
     */
    public function test_able_to_create_comment_when_logged_in()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test');
        $post = Post::factory()->for($user, 'author')->create();

        $response = $this
            ->jsonApi()
            ->expects('comments')
            ->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->withJson([
                'data' => [
                    'type' => 'comments',
                    'attributes' => [
                        'content' => 'foo bar',
                        'is_published' => false,
                    ],
                    'relationships' => [
                        'post' => [
                            'data' => [
                                'id' => (string) $post->id,
                                'type' => 'posts',
                            ]
                        ],
                    ]
                ]
            ])
            ->post('/api/v1/comments/')
            ->assertStatus(201);

        $id = $response->id();

        $this->assertDatabaseHas('comments', [
            'id' => $id,
            'content' => 'foo bar',
            'author_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }
}
