<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\ResetPasswordEnterEmail;
use App\Livewire\Auth\ResetPassword2fa;
use App\Livewire\Auth\ResetPasswordChangePassword;
use App\Livewire\Auth\ResetPasswordChanged;
use App\Livewire\Homepage\Index as HomepageIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('homepage.index');
});

Route::prefix('app')->get('/home', HomepageIndex::class)->name('homepage.index');
Route::prefix('app')->get('/login', Login::class)->name('login');
Route::prefix('app')->get('/reset-password/enter-email', ResetPasswordEnterEmail::class)->name('password.enter-email');
Route::prefix('app')->get('/reset-password/2fa', ResetPassword2fa::class)->name('password.2fa');
Route::prefix('app')->get('/reset-password/change-password', ResetPasswordChangePassword::class)->name('password.reset');
Route::prefix('app')->get('/reset-password/password-changed', ResetPasswordChanged::class)->name('password.changed');
