<?php

declare(strict_types=1);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Revolution\Ordering\Contracts\Actions\Api\MenusIndex;

Route::get('menus', MenusIndex::class)
     ->name('api.menus.index');
Route::middleware('api')->get('/status', function (Request $request) {
     return response()->json(['status' => 'ok']);
     });