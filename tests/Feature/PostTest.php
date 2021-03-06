<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    const URL_HOSTNAME = 'http://kooomo-code-challenge.test';

    /**
     * @group post
     */
    public function test_default_post_route_available()
    {
        $response = $this->get('/api/v1/posts');
        $response->assertStatus(200);
    }

    /**
     * @group post
     */
    public function test_first_post_not_available_when_db_empty()
    {
        $response = $this->get('/api/v1/posts/1');
        $response->assertStatus(404);
    }

    /**
     * @group post
     */
    public function test_get_index_of_posts_if_published() {
        Post::factory()
            ->count(2)
            ->for(User::factory()->state([
                'email' => 'test@test.com',
                'name' => 'foo bar',
                'username' => 'foobar',
            ]), 'author')
            ->sequence(fn ($sequence) => [
                'id' => $sequence->index+1,
                'title' => 'Title '.$sequence->index,
                'content' => 'Content '.$sequence->index,
                'slug' => 'slug-'.$sequence->index,
                'created_at' => '2022-01-01 00:00:01',
                'updated_at' => '2022-01-01 00:00:01',
            ])
            ->create([
                'is_published' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@test.com',
        ]);
        $this->assertDatabaseHas('posts', [
            'title' => 'Title 1',
        ]);

        $this->getJson('/api/v1/posts')
            ->assertStatus(200)
            ->assertExactJson([
                'jsonapi' => [
                    'version' => '1.0',
                ],
                'data' => [
                    [
                        'type' => 'posts',
                        'id' => '1',
                        'links' => [
                            'self' => self::URL_HOSTNAME . '/api/v1/posts/1',
                        ],
                        'attributes' => [
                            'title' => 'Title 0',
                            'slug' => 'slug-0',
                            'content' => 'Content 0',
                            'createdAt' => '2022-01-01T00:00:01.000000Z',
                            'is_published' => true,
                            'updatedAt' => '2022-01-01T00:00:01.000000Z',
                        ],
                        'relationships' => [
                            'author' => [
                                'links' => [
                                    'related' => self::URL_HOSTNAME . '/api/v1/posts/1/author',
                                    'self' => self::URL_HOSTNAME . '/api/v1/posts/1/relationships/author',
                                ],
                            ],
                            'comments' => [
                                'links' => [
                                    'related' => self::URL_HOSTNAME . '/api/v1/posts/1/comments',
                                    'self' => self::URL_HOSTNAME . '/api/v1/posts/1/relationships/comments',
                                ],
                            ]
                        ]
                    ],
                    [
                        'type' => 'posts',
                        'id' => '2',
                        'links' => [
                            'self' => self::URL_HOSTNAME . '/api/v1/posts/2',
                        ],
                        'attributes' => [
                            'title' => 'Title 1',
                            'slug' => 'slug-1',
                            'content' => 'Content 1',
                            'createdAt' => '2022-01-01T00:00:01.000000Z',
                            'is_published' => true,
                            'updatedAt' => '2022-01-01T00:00:01.000000Z',
                        ],
                        'relationships' => [
                            'author' => [
                                'links' => [
                                    'related' => self::URL_HOSTNAME . '/api/v1/posts/2/author',
                                    'self' => self::URL_HOSTNAME . '/api/v1/posts/2/relationships/author',
                                ],
                            ],
                            'comments' => [
                                'links' => [
                                    'related' => self::URL_HOSTNAME . '/api/v1/posts/2/comments',
                                    'self' => self::URL_HOSTNAME . '/api/v1/posts/2/relationships/comments',
                                ],
                            ]
                        ]
                    ]
                ],
            ]);
    }
    /**
     * @group post
     */
    public function test_read_a_post_when_is_published() {
        $post = Post::factory()
            ->for(User::factory(), 'author')
            ->sequence(fn ($sequence) => [
                'id' => $sequence->index+1,
                'title' => 'Title '.$sequence->index,
                'content' => 'Content '.$sequence->index,
                'slug' => 'slug-'.$sequence->index,
                'created_at' => '2022-01-01 00:00:01',
                'updated_at' => '2022-01-01 00:00:01',
            ])
            ->create([
                'is_published' => true,
            ]);

        $this->assertDatabaseHas('posts', [
            'id' => 1,
        ]);


        $this->getJson('/api/v1/posts/1')
            ->assertStatus(200)
            ->assertExactJson([
                'jsonapi' => [
                    'version' => '1.0',
                ],
                'data' => [
                    'type' => 'posts',
                    'id' => '1',
                    'links' => [
                        'self' => self::URL_HOSTNAME . '/api/v1/posts/1',
                    ],
                    'attributes' => [
                        'title' => 'Title 0',
                        'slug' => 'slug-0',
                        'content' => 'Content 0',
                        'createdAt' => '2022-01-01T00:00:01.000000Z',
                        'is_published' => true,
                        'updatedAt' => '2022-01-01T00:00:01.000000Z',
                    ],
                    'relationships' => [
                        'author' => [
                            'links' => [
                                'related' => self::URL_HOSTNAME . '/api/v1/posts/1/author',
                                'self' => self::URL_HOSTNAME . '/api/v1/posts/1/relationships/author',
                            ],
                        ],
                        'comments' => [
                            'links' => [
                                'related' => self::URL_HOSTNAME . '/api/v1/posts/1/comments',
                                'self' => self::URL_HOSTNAME . '/api/v1/posts/1/relationships/comments',
                            ],
                        ]
                    ]
                ],
                'links' => [
                    'self' =>  self::URL_HOSTNAME . '/api/v1/posts/1',
                ]
            ]);
    }

    /**
     * @group post
     */
    public function test_get_user_relationship_for_post_when_is_published() {
        $posts = Post::factory()
            ->count(1)
            ->for(User::factory()->state([
                'id' => 1,
                'email' => 'test@test.com',
                'name' => 'foo bar',
                'username' => 'foobar',
            ]), 'author')
            ->create([
                'id' => 1,
                'title' => 'Title 1',
                'is_published' => true,
            ]);

        $user = $posts->first()->author;

        $this->assertDatabaseHas('users', [
            'email' => 'test@test.com',
        ]);
        $this->assertDatabaseHas('posts', [
            'title' => 'Title 1',
        ]);

        $this->getJson('/api/v1/posts/1/author')
            ->assertStatus(200)
            ->assertJson([
                'jsonapi' => [
                    'version' => '1.0',
                ],
                'data' => [
                    'type' => 'users',
                    'id' => '1',
                    'links' => [
                        'self' => self::URL_HOSTNAME . '/api/v1/users/1',
                    ],
                    'attributes' => [
                        'name' => 'foo bar',
                        'username' => 'foobar',
                        'createdAt' => $user->created_at->jsonSerialize(),
                        'updatedAt' => $user->updated_at->jsonSerialize(),
                    ],
                ],
                'links' => [
                    'related' => self::URL_HOSTNAME . '/api/v1/posts/1/author',
                    'self' => self::URL_HOSTNAME . '/api/v1/posts/1/relationships/author',
                ],
            ]);
    }

    /**
     * @group post
     */
    public function test_get_comment_relationship_for_post_when_is_published() {

        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create([
                'id' => 1,
                'is_published' => true,
            ]);

        $comment = Comment::factory()
            ->for($post)
            ->for($user, 'author')
            ->create([
                'id' => 1,
                'content' => 'comment content...',
                'is_published' => true,
            ]);

        $this->assertDatabaseHas('comments', [
            'content' => 'comment content...',
        ]);

        $this->getJson('/api/v1/posts/1/comments')
            ->assertStatus(200)
            ->assertJson([
                'jsonapi' => [
                    'version' => '1.0',
                ],
                'data' => [
                    [
                        'type' => 'comments',
                        'id' => '1',
                        'links' => [
                            'self' => self::URL_HOSTNAME . '/api/v1/comments/1',
                        ],
                        'attributes' => [
                            'content' => 'comment content...',
                        ],
                    ]
                ],
                'links' => [
                    'related' => self::URL_HOSTNAME . '/api/v1/posts/1/comments',
                    'self' => self::URL_HOSTNAME . '/api/v1/posts/1/relationships/comments',
                ]
            ]);
    }

    /**
     * @group post
     */
    public function test_get_comment_relationship_for_post_when_is_published_has_no_attributes() {

        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create([
                'id' => 1,
                'is_published' => true,
            ]);

        $comment = Comment::factory()
            ->for($post)
            ->for($user, 'author')
            ->create([
                'id' => 1,
                'content' => 'comment content 2...',
                'is_published' => true,
            ]);

        $this->assertDatabaseHas('comments', [
            'content' => 'comment content 2...',
        ]);

        $this->getJson('/api/v1/posts/1/relationships/comments')
            ->assertStatus(200)
            ->assertJson([
                'jsonapi' => [
                    'version' => '1.0',
                ],
                'data' => [
                    [
                        'type' => 'comments',
                        'id' => '1',
                    ]
                ],
                'links' => [
                    'related' => self::URL_HOSTNAME . '/api/v1/posts/1/comments',
                    'self' => self::URL_HOSTNAME . '/api/v1/posts/1/relationships/comments',
                ]
            ]);
    }

    /**
     * @group post
     */
    public function test_including_comments_and_author_getting_post_when_are_published() {

        $user = User::factory()->create([
            'id' => 1,
            'username' => 'foobar',
            'name' => 'foo bar',
        ]);
        $post = Post::factory()
            ->for($user, 'author')
            ->create([
                'id' => 1,
                'is_published' => true,
                'slug' => 'slug-post',
                'title' => 'Post Title',
                'content' => 'Post content...',
            ]);

        $comment = Comment::factory()
            ->for($post)
            ->for($user, 'author')
            ->create([
                'id' => 1,
                'content' => 'comment content 2...',
                'is_published' => true,
            ]);

        $this->assertDatabaseHas('comments', [
            'content' => 'comment content 2...',
        ]);

        $this->getJson('/api/v1/posts/1?include=author,comments.author')
            ->assertStatus(200)
            ->assertJson([
                'jsonapi' => [
                    'version' => '1.0',
                ],
                'data' => [
                    'type' => 'posts',
                    'id' => '1',
                    'attributes' => [
                        'createdAt' => $post->created_at->jsonSerialize(),
                        'updatedAt' => $post->updated_at->jsonSerialize(),
                        'content' => 'Post content...',
                        'slug' => 'slug-post',
                        'title' => 'Post Title'
                    ],
                ],
                'included' =>[
                    [
                        'type' => 'users',
                        'id' => '1',
                        'attributes' => [
                            'username' => 'foobar',
                            'name' => 'foo bar'
                        ],
                        'relationships' => [
                            'posts' => [
                                'links' => [
                                    'related' => 'http://kooomo-code-challenge.test/api/v1/users/1/posts',
                                    'self' => 'http://kooomo-code-challenge.test/api/v1/users/1/relationships/posts'
                                ]
                            ],
                            'comments' => [
                                'links' => [
                                    'related' => 'http://kooomo-code-challenge.test/api/v1/users/1/comments',
                                    'self' => 'http://kooomo-code-challenge.test/api/v1/users/1/relationships/comments'
                                ]
                            ]
                        ],
                        'links' => [
                            'self' => 'http://kooomo-code-challenge.test/api/v1/users/1'
                        ]
                    ],
                    [
                        'type' => 'comments',
                        'id' => '1',
                        'attributes' => [
                            'content' => 'comment content 2...'
                        ],
                        'relationships' => [
                            'author' => [
                                'links' => [
                                    'related' => 'http://kooomo-code-challenge.test/api/v1/comments/1/author',
                                    'self' => 'http://kooomo-code-challenge.test/api/v1/comments/1/relationships/author'
                                ],
                                'data' => [
                                    'type' => 'users',
                                    'id' => '1'
                                ]
                            ]
                        ],
                        'links' => [
                            'self' => 'http://kooomo-code-challenge.test/api/v1/comments/1'
                        ]
                    ]
                ]
            ]);
    }
}
