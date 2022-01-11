<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PostPaginatedTest extends TestCase
{
    use RefreshDatabase;

    const URL_HOSTNAME = 'http://kooomo-code-challenge.test';

    public function test_cannot_see_not_publish_posts()
    {
        $posts = Post::factory()
            ->count(2)
            ->for(User::factory(), 'author')
            ->sequence(fn ($sequence) => [
                'is_published' => (bool) $sequence->index,
            ])
            ->create();

        $publishedPosts = $posts->filter(fn ($post) => $post->is_published);

        $this->getJson('/api/v1/posts')
            ->assertStatus(200);

        $response = $this
            ->jsonApi()
            ->expects('posts')
            ->get('/api/v1/posts');

        $response->assertFetchedMany($publishedPosts);
    }

    public function test_cannot_see_not_publish_included_comments()
    {
        $user = User::factory();
        $posts = Post::factory()
            ->count(2)
            ->for($user, 'author')
            ->sequence(fn ($sequence) => [
                'is_published' => (bool) $sequence->index,
            ])
            ->create();

        $publishedPosts = $posts->filter(fn ($post) => $post->is_published);
        $publishedPost = $publishedPosts->first();

        $comments = Comment::factory()
            ->count(2)
            ->for($user, 'author')
            ->for($publishedPost)
            ->sequence(fn ($sequence) => [
                'is_published' => (bool) $sequence->index,
            ])
            ->create();

        $publishedComments = $comments->filter(fn ($comment) => $comment->is_published);
        $publishedComment = $publishedComments->first();

        $this->getJson('/api/v1/posts')
            ->assertStatus(200);

        $response = $this
            ->jsonApi()
            ->expects('posts')
            ->includePaths('comments')
            ->get('/api/v1/posts');

        $response
            ->assertFetchedMany($publishedPosts)
            ->assertIncluded([
                ['type' => 'comments', 'id' => ''.$publishedComment->id],
            ]);
    }

    public function test_cannot_see_not_publish_related_comments()
    {
        $user = User::factory();
        $posts = Post::factory()
            ->count(2)
            ->for($user, 'author')
            ->sequence(fn ($sequence) => [
                'is_published' => (bool) $sequence->index,
            ])
            ->create();

        $publishedPosts = $posts->filter(fn ($post) => $post->is_published);
        $publishedPost = $publishedPosts->first();

        $comments = Comment::factory()
            ->count(2)
            ->for($user, 'author')
            ->for($publishedPost)
            ->sequence(fn ($sequence) => [
                'is_published' => (bool) $sequence->index,
            ])
            ->create();

        $publishedComments = $comments->filter(fn ($comment) => $comment->is_published);

        $this->getJson('/api/v1/posts')
            ->assertStatus(200);

        $response = $this
            ->jsonApi()
            ->expects('comments')
            ->get("/api/v1/posts/{$publishedPost->id}/comments");

        $response->assertFetchedMany($publishedComments);
    }


    public function test_get_second_page()
    {
        $user = User::factory();
        $posts = Post::factory()
            ->count(20)
            ->for($user, 'author')
            ->create(['is_published' => true]);

        $response = $this
            ->jsonApi()
            ->expects('posts')
            ->page(['number' => 1, 'size' => 10])
            ->get('/api/v1/posts');

        $response->assertJson([
            'meta' => [
                'page' => [
                    'currentPage' => 1,
                    'from' => 1,
                    'lastPage' => 2,
                    'perPage' => 10,
                    'to' => 10,
                    'total' => 20
                ]
            ]
        ]);

        $response->assertFetchedMany($posts->slice(0, 10));
    }
}
