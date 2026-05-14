<?php
// routes/technician.php

use App\Http\Controllers\Technician\TechnicianController;
use App\Http\Controllers\Technician\ServiceReportController;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', [TechnicianController::class, 'dashboard'])->name('dashboard');

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/',              [ServiceReportController::class, 'index'])->name('index');
    Route::get('/create',        [ServiceReportController::class, 'create'])->name('create');
    Route::get('/folio',         [ServiceReportController::class, 'generateFolio'])->name('folio');
    Route::post('/',             [ServiceReportController::class, 'store'])->name('store');
    Route::put('/{id}',          [ServiceReportController::class, 'update'])->name('update');
    Route::post('/{id}/complete',[ServiceReportController::class, 'complete'])->name('complete');
    Route::post('/{id}/fotos',  [ServiceReportController::class, 'uploadFotos'])->name('fotos');
    Route::get('/{id}',          [ServiceReportController::class, 'show'])->name('show');
    Route::delete('/{id}',       [ServiceReportController::class, 'destroy'])->name('destroy');
});
