<?php

use Illuminate\Support\Facades\Route;
use App\Mail\ResetPassword;
use App\Mail\VerifyEmail;
use App\Mail\Welcome;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Local dev Routes
|--------------------------------------------------------------------------
|
| Here is where you can register testing routes for your application. These
| routes are loaded by the RouteServiceProvider
|
*/

Route::prefix('email')->group(function () {

  Route::get('/verify-email', function () {
    $user = User::factory()->make();
    $button_url = '#';
    return new VerifyEmail($user, $button_url);
  })->name('local.email.verify-email');

  Route::get('/reset-password', function () {
    $user = User::factory()->make();
    $token = 'token';
    return new ResetPassword($user, $token);
  })->name('local.email.reset-password');

  Route::get('/welcome', function () {
    $user = User::factory()->make();
    $button_url = '#welcome';
    return new Welcome($user, $button_url);
  })->name('local.email.welcome');
});
