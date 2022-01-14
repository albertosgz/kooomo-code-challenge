<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ViewPostPublicTest extends TestCase
{
    use RefreshDatabase;

    const URL_HOSTNAME = 'http://kooomo-code-challenge.test';

    /**
     * @group post
     */
    public function test_cannot_view_not_published_post_when_not_logged_in()
    {
        $user = User::factory()->create();

        $post = Post::factory()
            ->for($user, 'author')
            ->create(['is_published' => false]);

        $this
            ->jsonApi()
            ->expects('posts')
            ->get('/api/v1/posts/'.$post->getRouteKey())
            ->assertStatus(401);
    }

    /**
     * @group post
     */
    public function test_able_to_view_not_published_post_when_logged_in()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test');

        $post = Post::factory()
            ->for($user, 'author')
            ->create(['is_published' => false]);

        $this
            ->jsonApi()
            ->expects('posts')
            ->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->get('/api/v1/posts/'.$post->getRouteKey())
            ->assertStatus(200)
            ->assertFetchedOne($post);
    }

    /**
     * @group post
     */
    public function test_cannot_view_comments_of_not_published_post_when_not_logged_in()
    {
        $user = User::factory()->create();

        $post = Post::factory()
            ->for($user, 'author')
            ->create(['is_published' => false]);

        Comment::factory()
            ->count(2)
            ->for($user, 'author')
            ->for($post)
            ->create(['is_published' => true]);

        $this
            ->jsonApi()
            ->expects('posts')
            ->get('/api/v1/posts/'.$post->getRouteKey().'/relationships/comments')
            ->assertStatus(401);
    }

    /**
     * @group post
     */
    public function test_view_comments_of_not_published_post_when_logged_in()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test');

        $post = Post::factory()
            ->for($user, 'author')
            ->create(['is_published' => false]);

        $comments = Comment::factory()
            ->count(2)
            ->for($user, 'author')
            ->for($post)
            ->create(['is_published' => true]);

        $this
            ->jsonApi()
            ->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->expects('comments')
            ->get('/api/v1/posts/'.$post->getRouteKey().'/relationships/comments')
            ->assertStatus(200)
            ->assertFetchedMany($comments);
    }

}
