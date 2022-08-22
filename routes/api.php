<?php

use App\Http\Controllers\CommentController;
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

Route::prefix('comment')->group(function () {
    Route::get('/', [CommentController::class, 'index'])->name('comment.getAll');
    Route::get('/{comment_id}', [CommentController::class, 'show'])->name('comment.get');
    Route::post('/', [CommentController::class, 'store'])->name('comment.store');
    Route::put('/{comment_id}', [CommentController::class, 'update'])->name('comment.update');
    Route::delete('/{comment_id}', [CommentController::class, 'destroy'])->name('comment.delete');
});
