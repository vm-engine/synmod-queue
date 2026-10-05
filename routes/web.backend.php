<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::group([], function () {
    Route::livewire('/failed', 'queue::failed-job-list')->name('failed')
        ->middleware('can-access:queue.failed');
    Route::livewire('/pending', 'queue::pending-job-list')->name('pending')
        ->middleware('can-access:queue.pending');
    Route::livewire('/worker', 'queue::worker-control')->name('worker')
        ->middleware('can-access:queue.worker');
});
