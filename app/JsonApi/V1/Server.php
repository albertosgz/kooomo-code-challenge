<?php

namespace App\JsonApi\V1;

use App\JsonApi\V1\Comments\CommentPublishedScope;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use LaravelJsonApi\Core\Server\Server as BaseServer;

class Server extends BaseServer
{

    /**
     * The base URI namespace for this server.
     *
     * @var string
     */
    protected string $baseUri = '/api/v1';

    /**
     * Bootstrap the server when it is handling an HTTP request.
     *
     * @return void
     */
    public function serving(): void
    {
        Auth::shouldUse('sanctum'); // https://laraveljsonapi.io/docs/1.0/tutorial/05-creating-resources.html#authentication
        Comment::addGlobalScope(new CommentPublishedScope());
        Post::creating(static fn(Post $post) => $post->author()->associate(Auth::user()));
        Comment::creating(static fn(Comment $comment) => $comment->author()->associate(Auth::user()));
    }

    /**
     * Get the server's list of schemas.
     *
     * @return array
     */
    protected function allSchemas(): array
    {
        return [
            Posts\PostSchema::class,
            Comments\CommentSchema::class,
            Users\UserSchema::class,
        ];
    }
}
