<?php

namespace App\JsonApi\V1;

use App\JsonApi\V1\Comments\CommentPublishedScope;
use App\Models\Comment;
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
        Comment::addGlobalScope(new CommentPublishedScope());
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
            Posts\PostSchema::class,
            Users\UserSchema::class,
        ];
    }
}
