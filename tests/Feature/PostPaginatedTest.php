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

    /**
     * @group post
     */
    public function test_cannot_see_not_publish_posts()
    {
        $user = User::factory()->create();
        $posts = Post::factory()
            ->count(2)
            ->for($user, 'author')
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

    /**
     * @group post
     */
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

    /**
     * @group post
     */
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


    /**
     * @group post
     */
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

    /**
     * @group post
     */
    public function test_get_last_5_comments_and_total_in_public_index()
    {
        $user = User::factory();
        $posts = Post::factory()
            ->count(20)
            ->for($user, 'author')
            ->create(['is_published' => true]);

        $comments = Comment::factory()
            ->count(7)
            ->for($user, 'author')
            ->for($posts->first())
            ->sequence(fn($sequence) => [
                'id' => $sequence->index + 1,
                'content' => 'Content ' . $sequence->index,
                'created_at' => '2001-01-01 00:00:0' . ($sequence->index+1),
                'updated_at' => '2001-01-01 00:00:01',
                'is_published' => $sequence->index < 5,
            ])
            ->create();

        $commentNoPublished = $comments->first(fn ($comment) => !$comment->is_published);

        $response = $this
            ->jsonApi()
            ->expects('posts')
            ->page(['number' => 1, 'size' => 2])
            ->get('/api/v1/posts');

        $response
            ->assertFetchedMany($posts->slice(0, 2))
            ->assertJsonMissingExact([
                'type' => 'comments',
                'id' => (string) $commentNoPublished->id,
            ]);

        $responseObject = json_decode($response->getContent(), true);
        $indexMetaJson = $responseObject['data'][0]['meta'];
        $metaJsonExpected = [
            'total_comments' => 5,
            'last_comments' => $comments
                ->filter(fn ($comment) => $comment->is_published)
                ->map(fn ($comment, $index) => [
                    'id' => $index + 1,
                    'type' => 'comments',
                    'attributes' => [
                        'content' => 'Content ' . $index,
                        'createdAt' => '2001-01-01T00:00:0' . ($index+1) . '.000000Z',
                        'updatedAt' => '2001-01-01T00:00:01.000000Z',
                    ],
                    'relationships' => [
                        'author' => [
                            'links' => [
                                'related' => self::URL_HOSTNAME . '/api/v1/comments/'.$comment->getRouteKey().'/author',
                                'self' => self::URL_HOSTNAME . '/api/v1/comments/'.$comment->getRouteKey().'/relationships/author'
                            ]
                        ]
                    ]
                ])
                ->toArray(),
        ];

        $this->assertEquals(json_encode($indexMetaJson), json_encode($metaJsonExpected));
    }
}
