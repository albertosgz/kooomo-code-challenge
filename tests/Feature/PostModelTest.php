<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @group post
     */
    public function test_get_author()
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create();

        $postFromDB = Post::find($post->id);

        $this->assertEquals($user->id, $postFromDB->author->id);
    }

    /**
     * @group post
     */
    public function test_get_comments()
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create();

        Comment::factory()
            ->count(4)
            ->for($user, 'author')
            ->for($post)
            ->create();

        $postFromDB = Post::find($post->id);

        $this->assertEquals(4, $postFromDB->comments->count());
    }
}
