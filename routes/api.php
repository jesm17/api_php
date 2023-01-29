<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('register/user', [UserController::class, 'store']);


Route::post('/user/login', [UserController::class, 'login']);
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('user/{id}', [UserController::class, 'index']);
    Route::post('user/add/favorites', [UserController::class, 'addFavorite']);
    Route::get('user/get/favorites/{id}', [UserController::class, 'myFavorites']);
    Route::delete('user/delete/favorites/{id}', [UserController::class, 'removeFavorite']);
    Route::put('user/update/addional-info/', [UserController::class, 'updateAditionalInfo']);
});
