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


    Route::prefix('v1')->group(function () {
        Route::post('login', [Controllers\AuthController::class, 'login']);
        Route::get('book/{id}', [Controllers\BookController::class, 'view'])->where('id', '[0-9]+');
        Route::group(['middleware' => 'api'], function () {
            Route::post('logout', [Controllers\AuthController::class, 'logout']);
            Route::post('shipping', [Controllers\ShopController::class, 'shipping']);
            Route::post('services', [Controllers\ShopController::class, 'services']);
        });
        Route::group(['middleware' => 'cors'], function () {
            Route::post('register', [Controllers\AuthController::class, 'register']);
            Route::get('categories', [Controllers\CategoryController::class, 'index']);
            Route::get('categories/random/{count}', [Controllers\CategoryController::class, 'random']);
            Route::get('categories/slug/{slug}', [Controllers\CategoryController::class, 'slug']);
            Route::get('books', [Controllers\BookController::class, 'index']);
            Route::get('books/top/{count}', [Controllers\BookController::class, 'top']);
            Route::get('books/slug/{slug}', [Controllers\BookController::class, 'slug']);
            Route::get('books/search/{keyword}', [Controllers\BookController::class, 'search']);
            Route::get('provinces', [Controllers\ShopController::class, 'provinces']);
            Route::get('cities', [Controllers\ShopController::class, 'cities']);
            Route::get('couriers', [Controllers\ShopController::class, 'couriers']);
        });
    });
