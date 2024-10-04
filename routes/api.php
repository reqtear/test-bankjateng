<?php

use App\Http\Controllers\API\CardController;
use App\Http\Controllers\API\TransactionController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

use App\Http\Controllers\API\RegisterController;

Route::controller(RegisterController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::middleware('auth:sanctum')->group(function () {
    // Route::resource('products', ProductController::class);
    Route::post('/addCard', [CardController::class, 'addCard'])->name('addCard');
    Route::post('/getCardList', [CardController::class, 'getCardList'])->name('getCardList');
    Route::get('/getCardDetail/{id}', [CardController::class, 'getCardDetail'])->name('getCardDetail');
    Route::post('/setPin/{id}', [CardController::class, 'setPin'])->name('setPin');
    Route::delete('/deleteCard', [CardController::class, 'deleteCard'])->name('deleteCard');
    Route::get('/blockCard/{id}', [CardController::class, 'blockCard'])->name('blockCard');


    Route::post('/postingTransaction', [TransactionController::class, 'postingTransaction'])->name('postingTransaction');
    Route::get('/getUserTransactionList/{id}', [TransactionController::class, 'getUserTransactionList'])->name('getUserTransactionList');
    Route::get('/getCardTransactionList/{id}', [TransactionController::class, 'getCardTransactionList'])->name('getCardTransactionList');
    Route::get('/getTransactionDetail/{id}', [TransactionController::class, 'getTransactionDetail'])->name('getTransactionDetail');
});
