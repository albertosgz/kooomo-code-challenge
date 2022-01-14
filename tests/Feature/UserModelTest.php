<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @group user
     */
    public function test_get_posts()
    {
        $user = User::factory()->create();
        Post::factory()
            ->count(3)
            ->for($user, 'author')
            ->create();

        $userFromDB = User::find($user->id);

        $this->assertEquals(3, $userFromDB->posts->count());
    }

    /**
     * @group user
     */
    public function test_get_comments()
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create();

        Comment::factory()
            ->count(7)
            ->for($user, 'author')
            ->for($post)
            ->create();

        $userFromDB = User::find($user->id);

        $this->assertEquals(7, $userFromDB->comments->count());
    }
}
