<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class EditCommentTest extends TestCase
{
    use RefreshDatabase;

    const URL_HOSTNAME = 'http://kooomo-code-challenge.test';

    /**
     * @group comment
     */
    public function test_cannot_edit_comment_when_not_logged_in()
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create(['is_published' => true]);
        $comment = Comment::factory()
            ->for($user, 'author')
            ->for($post)
            ->create(['is_published' => true]);

        $this->assertModelExists($comment);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
        ]);


        $data = [
            'type' => 'comments',
            'id' => (string) $comment->getRouteKey(),
            'attributes' => [
                'content' => 'This is new comment content.',
            ],
        ];

        $this
            ->jsonApi()
            ->expects('comments')
            ->withData($data)
            ->patch('/api/v1/comments/' . $comment->getRouteKey())
            ->assertStatus(401);
    }

    /**
     * @group comment
     */
    public function test_able_to_edit_comment_when_logged_in_and_being_author()
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create();

        $comment = Comment::factory()
            ->for($user, 'author')
            ->for($post)
            ->create();

        $data = [
            'type' => 'comments',
            'id' => (string) $comment->getRouteKey(),
            'attributes' => [
                'content' => 'This is new comment content.',
            ],
        ];

        $response = $this
            ->actingAs($user)
            ->jsonApi()
            ->expects('comments')
            ->withData($data)
            ->patch('/api/v1/comments/' . $comment->getRouteKey())
            ->assertStatus(200);

        $response->assertFetchedOne($data);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->getKey(),
            'author_id' => $user->id,
            'content' => $data['attributes']['content'],
        ]);
    }

    /**
     * @group comment
     */
    public function test_cannot_edit_comment_when_logged_in_and_different_author()
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create();
        $comment = Comment::factory()
            ->for($user, 'author')
            ->for($post)
            ->create();

        $data = [
            'type' => 'comments',
            'id' => (string) $comment->getRouteKey(),
            'attributes' => [
                'content' => 'This is new comment content.',
            ],
        ];

        $this
            ->actingAs($anotherUser)
            ->jsonApi()
            ->expects('comments')
            ->withData($data)
            ->patch('/api/v1/comments/' . $comment->getRouteKey())
            ->assertStatus(403);
    }

    /**
     * @group comment
     */
    public function test_is_published_on_comment_cannot_be_null()
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create();
        $comment = Comment::factory()
            ->for($user, 'author')
            ->for($post)
            ->create();

        $data = [
            'type' => 'comments',
            'id' => (string) $comment->getRouteKey(),
            'attributes' => [
                'is_published' => null,
            ],
        ];

        $this
            ->actingAs($user)
            ->jsonApi()
            ->expects('comments')
            ->withData($data)
            ->patch('/api/v1/comments/' . $comment->getRouteKey())
            ->assertStatus(422);
    }
}
