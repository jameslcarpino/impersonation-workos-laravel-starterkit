<?php

use App\Http\Controllers\ImpersonationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('impersonation/status', [ImpersonationController::class, 'status'])
        ->name('impersonation.status');
    
    Route::post('impersonation/stop', [ImpersonationController::class, 'stop'])
        ->name('impersonation.stop');
    
    Route::get('impersonation/banner', [ImpersonationController::class, 'banner'])
        ->name('impersonation.banner');
}); 