<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user = User::factory()->create();

        $posts = Post::factory()
            ->count(20)
            ->for($user, 'author')
            ->create();

        Comment::factory()
            ->count(20)
            ->for($user, 'author')
            ->for($posts->first())
            ->create();
    }
}
