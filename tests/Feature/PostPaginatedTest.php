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
            ->for(User::factory()->state([
                'email' => 'test@test.com',
                'name' => 'foo bar',
                'username' => 'foobar',
            ]), 'author')
            ->sequence(fn ($sequence) => [
                'title' => 'Title '.$sequence->index,
                'content' => 'Content '.$sequence->index,
                'slug' => 'slug-'.$sequence->index,
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
}
