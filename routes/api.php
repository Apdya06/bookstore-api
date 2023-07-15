<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;

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
//Route add by default
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {

    //public
    Route::post('login', [Controllers\AuthController::class, 'login']);
    Route::post('register', [Controllers\AuthController::class, 'register']);


    // private
    Route::group(['middleware' => 'cors'], function () {
        Route::post('logout', [Controllers\AuthController::class, 'logout']);
        Route::get('categories', [Controllers\CategoryController::class, 'index']);
        Route::get('categories/random/{count}', [Controllers\CategoryController::class, 'random']);
        Route::get('books', [Controllers\BookController::class, 'index']);
        Route::get('books/top/{count}', [Controllers\BookController::class, 'top']);
    });

    Route::get('book/{id}', [Controllers\BookController::class, 'view'])->where('id', '[0-9]+');
});


//string syntax method
// Route::middleware(['cors'])->group(function () {
//     Route::get('book/{title}', [Controllers\AuthController::class, 'print']);
// });

//fully qualified class method
// use App\Http\Controllers\BookController;
// Route::get('buku/{title}', [BookController::class, 'print']);

//third method add namespace in RouteServiceProvider
