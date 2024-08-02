<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskListController;
use App\Http\Controllers\TaskListStorageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::controller(AuthController::class)
    ->prefix('auth')
    ->group(function () {
        Route::post('login', 'login')->name('login');
        Route::post('register', 'register')->name('register');

        Route::middleware(['jwt.verify'])
            ->group(function () {
                Route::get('me', 'me')->name('me');
                Route::get('refresh', 'refresh')->name('refresh');
                Route::post('logout', 'logout')->name('logout');
            });
    });

/*
|--------------------------------------------------------------------------
| Task
|--------------------------------------------------------------------------
*/
Route::controller(TaskController::class)
    ->prefix('task')
    ->name('task.')
    ->middleware(['jwt.verify'])
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');

        Route::patch('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });


/*
|--------------------------------------------------------------------------
| Task List
|--------------------------------------------------------------------------
*/
Route::controller(TaskListController::class)
    ->prefix('task-list')
    ->name('task-list.')
    ->middleware(['jwt.verify'])
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');

        Route::patch('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

/*
|--------------------------------------------------------------------------
| Task List Storage
|--------------------------------------------------------------------------
*/
Route::controller(TaskListStorageController::class)
    ->prefix('task-list-storage')
    ->name('task-list-storage.')
    ->middleware(['jwt.verify'])
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');

        Route::patch('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });
