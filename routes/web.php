<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Demo1\Index as Demo1Index;
use App\Livewire\Demo1\Login;
use App\Livewire\TestForm;

Route::get('/', function () {
    return redirect()->route('demo1.index');
});

// Demo1 routes
Route::prefix('app')->get('/demo1', Demo1Index::class)->name('demo1.index');
Route::prefix('app')->get('/login', Login::class)->name('login');