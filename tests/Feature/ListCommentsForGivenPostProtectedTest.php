<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ListCommentsForGivenPostProtectedTest extends TestCase
{
    use RefreshDatabase;

    const URL_HOSTNAME = 'http://kooomo-code-challenge.test';

    /**
     * @group post
     * @group comment
     */
    public function test_see_comments_of_protected_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create(['is_published' => false]);
        $comments = Comment::factory()
            ->count(10)
            ->for($user, 'author')
            ->for($post)
            ->sequence(fn($sequence) => [
                'is_published' => $sequence->index > 4,
            ])
            ->create();

        $this
            ->actingAs($user)
            ->jsonApi()
            ->expects('comments')
            ->get('/api/v1/posts/' . $post->getRouteKey() . '/relationships/comments')
            ->assertStatus(200)
            ->assertFetchedMany($comments);
    }

    /**
     * @group post
     * @group comment
     */
    public function test_see_comments_of_protected_post_paginated()
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create(['is_published' => false]);
        $comments = Comment::factory()
            ->count(4)
            ->for($user, 'author')
            ->for($post)
            ->create(['is_published' => false]);

        $this
            ->actingAs($user)
            ->jsonApi()
            ->expects('comments')
            ->get('/api/v1/posts/' . $post->getRouteKey() . '/relationships/comments?page[number]=2&page[size]=2')
            ->assertStatus(200)
            ->assertFetchedMany($comments->slice(2));
    }
}
