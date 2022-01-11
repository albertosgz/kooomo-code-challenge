<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExceptionHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_500_error_is_returned()
    {
        Post::factory()
            ->for(User::factory(), 'author')
            ->create([
                'is_published' => true,
            ]);

        /**
         * Whithout a proper handler, this endpoint throws exception "No JSON API resource id set on route.", returning a 500 status by default.
         * With the right handler, a 400 status must be returned
         */
        $this->getJson('/api/v1/posts/0')
            ->assertStatus(400);
    }
}
