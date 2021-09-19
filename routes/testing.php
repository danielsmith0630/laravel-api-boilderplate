<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Testing Routes
|--------------------------------------------------------------------------
|
| Here is where you can register testing routes for your application. These
| routes are loaded by the RouteServiceProvider
|
*/

Route::get('app-language-header', function () {
  return response()->json(['locale' => App::getLocale()]);
})->name('testing.app-language-header');
