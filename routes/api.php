<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
use LaravelJsonApi\Laravel\Http\Controllers\JsonApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

JsonApiRoute::server('v1')->prefix('v1')->resources(function ($server) {
    $server->resource('posts', JsonApiController::class)
        ->relationships(function ($relations) {
            $relations->hasOne('author')->readOnly();
            $relations->hasMany('comments')->readOnly();
        });
    $server->resource('users', JsonApiController::class)
        ->only('show')
        ->relationships(function ($relations) {
            $relations->hasMany('comments')->readOnly();
        });
    $server->resource('comments', JsonApiController::class)
        ->only('store', 'update', 'destroy')
        ->relationships(function ($relations) {
            $relations->hasOne('author')->readOnly();
            $relations->hasOne('post')->readOnly();
        });
});
