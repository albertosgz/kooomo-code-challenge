<?php

namespace App\JsonApi\V1\Comments;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class CommentPublishedScope implements Scope
{
    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, Model $model)
    {
        /**
         * If there is no authenticated user, then we just
         * need to ensure only published posts are returned.
         */
        if (Auth::guest()) {
            $builder->where(
                $model->qualifyColumn('is_published'),
                true
            );
        }
    }
}
