<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ListPostProtectedTest extends TestCase
{
    use RefreshDatabase;

    const URL_HOSTNAME = 'http://kooomo-code-challenge.test';

    public function test_see_on_published_posts_when_not_logged_in()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test');

        $posts = Post::factory()
            ->count(2)
            ->for($user, 'author')
            ->sequence(fn($sequence) => [
                'is_published' => (bool)$sequence->index,
            ])
            ->create();
        $postPublished = $posts->filter(fn ($post) => $post->is_published);

        $this->assertEquals($posts->count(), 2);
        $this->assertEquals($postPublished->count(), 1);

        $this
            ->jsonApi()
            ->expects('posts')
            ->get('/api/v1/posts')
            ->assertStatus(200)
            ->assertFetchedMany($postPublished);
    }

    public function test_see_all_posts_when_logged_in()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test');

        $posts = Post::factory()
            ->count(2)
            ->for($user, 'author')
            ->sequence(fn($sequence) => [
                'is_published' => (bool)$sequence->index,
            ])
            ->create();
        $postPublished = $posts->filter(fn ($post) => $post->is_published);

        $this->assertEquals($posts->count(), 2);
        $this->assertEquals($postPublished->count(), 1);

        $this
            ->jsonApi()
            ->expects('posts')
            ->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->get('/api/v1/posts')
            ->assertStatus(200)
            ->assertFetchedMany($posts);
    }

    public function test_see_all_posts_and_related_comments_when_logged_in()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test');

        $publishedPost = Post::factory()
            ->for($user, 'author')
            ->create(['is_published' => true]);
        $notPublishedPost = Post::factory()
            ->for($user, 'author')
            ->create(['is_published' => false]);
        $comment1 = Comment::factory()
            ->for($publishedPost)
            ->for($user, 'author')
            ->create(['is_published' => true]);
        $comment2 = Comment::factory()
            ->for($publishedPost)
            ->for($user, 'author')
            ->create(['is_published' => false]);
        $comment3 = Comment::factory()
            ->for($notPublishedPost)
            ->for($user, 'author')
            ->create(['is_published' => true]);
        $comment4 = Comment::factory()
            ->for($notPublishedPost)
            ->for($user, 'author')
            ->create(['is_published' => false]);

        $this
            ->jsonApi()
            ->expects('posts')
            ->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->get('/api/v1/posts?include=comments')
            ->assertStatus(200)
            ->assertFetchedMany([$publishedPost, $notPublishedPost])
            ->assertIncluded([
                ['type' => 'comments', 'id' => $comment1],
                ['type' => 'comments', 'id' => $comment2],
                ['type' => 'comments', 'id' => $comment3],
                ['type' => 'comments', 'id' => $comment4],
            ]);
    }

    public function test_see_published_posts_and_related_published_comments_when_not_logged_in()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test');

        $publishedPost = Post::factory()
            ->for($user, 'author')
            ->create(['is_published' => true]);
        $notPublishedPost = Post::factory()
            ->for($user, 'author')
            ->create(['is_published' => false]);
        $comment1 = Comment::factory()
            ->for($publishedPost)
            ->for($user, 'author')
            ->create(['is_published' => true]);
        $comment2 = Comment::factory()
            ->for($publishedPost)
            ->for($user, 'author')
            ->create(['is_published' => false]);
        $comment3 = Comment::factory()
            ->for($notPublishedPost)
            ->for($user, 'author')
            ->create(['is_published' => true]);
        $comment4 = Comment::factory()
            ->for($notPublishedPost)
            ->for($user, 'author')
            ->create(['is_published' => false]);

        $this
            ->jsonApi()
            ->expects('posts')
            ->get('/api/v1/posts?include=comments')
            ->assertStatus(200)
            ->assertFetchedMany([$publishedPost])
            ->assertIncluded([
                ['type' => 'comments', 'id' => $comment1], // The only published comment related with published post
            ]);
    }
}
