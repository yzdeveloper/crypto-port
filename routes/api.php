<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CashController;
use App\Http\Controllers\HoldingController;

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

Route::match(['get'], '/cash', [CashController::class, 'cash']);
Route::match(['post'], '/addCash', [CashController::class, 'addCash']);

Route::prefix('holdings')->group(function () {
    // Get holdings with optional filtering and sorting
    Route::get('/', [HoldingController::class, 'get']);
    
    // Update purchase quantity and price (bought
    Route::post('/bought', [HoldingController::class, 'bought']);
    
    // Update sell quantity and price (sold)
    Route::post('/sold', [HoldingController::class, 'sold']);
});