<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Demo1\Index as Demo1Index;

Route::get('/', function () {
    return redirect()->route('demo1.index');
});

// Demo1 routes
Route::get('/demo1', Demo1Index::class)->name('demo1.index');