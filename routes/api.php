<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// public routes
Route::post('/register', 'AuthController@register')->name('register');

Route::get('/email/verify/{id}', 'VerificationController@verify')
    ->middleware(['signed'])->name('verification.verify');

Route::post('/users/{id}/resend-email-verification', 'VerificationController@resend')
    ->middleware(['auth:api', 'throttle:6,1'])->name('verification.resend');

Route::post('/forgot', 'AuthController@forgot')->name('forgot');
Route::post('/reset-password', 'AuthController@resetPassword')->name('reset-password');

Route::middleware('auth:api')->group(function () {
    Route::get('/user', 'AuthController@getUser')->name('user');
    Route::post('/logout', 'AuthController@logout')->name('logout');

    Route::apiResources([
        'users.profiles' => UserProfileController::class,
        'users.settings' => UserSettingController::class,
        'users.privacy-settings' => UserPrivacySettingController::class,
        'spaces' => SpaceController::class,
        'spaces.members' => SpaceMemberController::class,
        'spaces.members.roles' => SpaceMemberRoleController::class,
        'spaces.channels' => ChannelController::class,
        'spaces.channels.members' => ChannelMemberController::class,
        'spaces.privacy-settings' => SpacePrivacySettingController::class,
    ]);

    Route::put('/users/{user}/profiles/{profile}/images', 'UserProfileController@uploadImages')
        ->name('users.profiles.upload-images');
    Route::put('/spaces/{space}/images', 'SpaceController@uploadImages')
        ->name('spaces.upload-images');
    Route::put('/spaces/{space}/members/{member}/roles/{role}/make-owner', 'SpaceMemberRoleController@transferOwnership')
        ->name('spaces.members.roles.make-owner');
});

// test routes for social authentication
Route::get('/auth/{provider}/redirect', function ($provider) {
    return Socialite::driver($provider)->stateless()->redirect();
});

Route::get('/auth/{provider}/callback', function ($provider) {
    $user = Socialite::driver('google')->stateless()->user();

    \Log::info($provider . ' access token:' . $user->token);
    return [
        'user' => $user
    ];
});
