<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CommentModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @group comment
     */
    public function test_get_author()
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create();
        $comment = Comment::factory()
            ->for($user, 'author')
            ->for($post)
            ->create();

        $commentFromDB = Comment::find($comment->id);

        $this->assertEquals($user->id, $commentFromDB->author->id);
    }

    /**
     * @group comment
     */
    public function test_get_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create();

        $comment = Comment::factory()
            ->for($user, 'author')
            ->for($post)
            ->create();

        $commentFromDB = Comment::find($comment->id);

        $this->assertEquals($post->id, $commentFromDB->post->id);
    }

}
