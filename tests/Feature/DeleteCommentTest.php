<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class DeleteCommentTest extends TestCase
{
    use RefreshDatabase;

    const URL_HOSTNAME = 'http://kooomo-code-challenge.test';

    /**
     * @group comment
     */
    public function test_cannot_delete_comment_when_not_logged_in()
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create(['is_published' => true]);
        $comment = Comment::factory()
            ->for($user, 'author')
            ->for($post)
            ->create(['is_published' => true]);

        $this
            ->jsonApi()
            ->expects('comments')
            ->delete('/api/v1/comments/' . $comment->getRouteKey())
            ->assertStatus(401);
    }

    /**
     * @group comment
     */
    public function test_able_to_delete_comment_when_logged_in_and_author()
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'author')->create();
        $comment = Comment::factory()
            ->for($user, 'author')
            ->for($post)
            ->create();

        $this
            ->actingAs($user)
            ->jsonApi()
            ->expects('comments')
            ->delete('/api/v1/comments/' . $comment->getRouteKey())
            ->assertStatus(204);

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->getKey(),
        ]);
    }

    /**
     * @group comment
     */
    public function test_cannot_delete_comment_when_logged_in_and_different_author()
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $post = Post::factory()->for($user, 'author')->create();
        $comment = Comment::factory()
            ->for($user, 'author')
            ->for($post)
            ->create();

        $this
            ->actingAs($anotherUser)
            ->jsonApi()
            ->expects('comments')
            ->delete('/api/v1/comments/' . $comment->getRouteKey())
            ->assertStatus(403);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->getKey(),
        ]);
    }
}
