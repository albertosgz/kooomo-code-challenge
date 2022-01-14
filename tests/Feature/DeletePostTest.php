<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class DeletePostTest extends TestCase
{
    use RefreshDatabase;

    const URL_HOSTNAME = 'http://kooomo-code-challenge.test';

    /**
     * @group post
     */
    public function test_cannot_delete_post_when_not_logged_in()
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'author')->create();

        $this
            ->jsonApi()
            ->expects('posts')
            ->delete('/api/v1/posts/' . $post->getRouteKey())
            ->assertStatus(401);
    }

    /**
     * @group post
     */
    public function test_able_to_delete_post_when_logged_in_and_author()
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'author')->create();

        $this
            ->actingAs($user)
            ->jsonApi()
            ->expects('posts')
            ->delete('/api/v1/posts/' . $post->getRouteKey())
            ->assertStatus(204);

        $this->assertDatabaseMissing('posts', [
            'id' => $post->getKey(),
        ]);
    }

    /**
     * @group post
     */
    public function test_cannot_delete_post_when_logged_in_and_different_author()
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $post = Post::factory()->for($user, 'author')->create();

        $this
            ->actingAs($anotherUser)
            ->jsonApi()
            ->expects('posts')
            ->delete('/api/v1/posts/' . $post->getRouteKey())
            ->assertStatus(403);

        $this->assertDatabaseHas('posts', [
            'id' => $post->getKey(),
        ]);
    }
}
