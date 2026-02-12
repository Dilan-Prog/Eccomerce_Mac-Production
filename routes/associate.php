<?php
/**Associate Panel Routes */

use App\Http\Controllers\Backend\AssociateController;
use App\Http\Controllers\Backend\AssociateProductController;
use Illuminate\Support\Facades\Route;


Route::get('dashboard', [AssociateController::class, 'dashboard'])->name('dashboard');

/**Product Panel */
Route::resource('products',AssociateProductController::class);
