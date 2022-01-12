<?php

namespace App\JsonApi\V1\Posts;

use App\JsonApi\V1\Comments\CommentResource;
use App\JsonApi\V1\Comments\CommentSchema;
use App\Models\Post;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Core\Resources\JsonApiResource;

class PostResource extends JsonApiResource
{
    /**
     * Get the resource's attributes.
     *
     * @param Request|null $request
     * @return iterable
     */
//    public function attributes($request): iterable
//    {
//        return [
//            'createdAt' => $this->created_at,
//            'updatedAt' => $this->updated_at,
//        ];
//    }

    /**
     * Get the resource's relationships.
     *
     * @param Request|null $request
     * @return iterable
     */
//    public function relationships($request): iterable
//    {
//        return [
//        ];
//    }


    /**
     * Get the resource's meta.
     *
     * @param \Illuminate\Http\Request|null $request
     * @return iterable
     */
    public function meta($request): iterable
    {
        $user = optional($request)->user();
        if (!$user) {
            /** @var Post $post */
            $post = $this->resource;
            $totalComments = $post->comments()->where('is_published', true)->count();
            if ($totalComments > 0) {
                $hostname = $request->getSchemeAndHttpHost();
                return [
                    'total_comments' => $totalComments,
                    'last_comments' => $post->comments()
                        ->where('is_published', true)
                        ->orderBy('created_at', 'asc')
                        ->limit(5)
                        ->get()
                        ->map(function ($comment) use ($hostname, $request) {
                            $id = $comment->id;
                            return [
                                'id' => $id,
                                'type' => 'comments',
                                'attributes' => [
                                    'content' => $comment->content,
                                    'createdAt' => $comment->created_at,
                                    'updatedAt' => $comment->updated_at,
                                ],
                                'relationships' => [
                                    'author' => [
                                        'links' => [
                                            'related' => "$hostname/api/v1/comments/$id/author",
                                            'self' => "$hostname/api/v1/comments/$id/relationships/author",
                                        ]
                                    ],
                                ],
                            ];
                        }),
                ];
            }
        }
        return [];
    }
}
