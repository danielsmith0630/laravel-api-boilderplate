<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Models\User' => 'App\Policies\UserPolicy',
        'App\Models\PasswordReset' => 'App\Policies\PasswordResetPolicy',
        'App\Models\UserProfile' => 'App\Policies\UserProfilePolicy',
        'App\Models\UserSetting' => 'App\Policies\UserSettingPolicy',
        'App\Models\Space' => 'App\Policies\SpacePolicy',
        'App\Models\File' => 'App\Policies\FilePolicy',
        'App\Models\Avatar' => 'App\Policies\AvatarPolicy',
        'App\Models\Banner' => 'App\Policies\BannerPolicy',
        'App\Models\SpaceMember' => 'App\Policies\SpaceMemberPolicy',
        'App\Models\Channel' => 'App\Policies\ChannelPolicy',
        'App\Models\UserPrivacySetting' => 'App\Policies\UserPrivacySettingPolicy',
        'App\Models\ChannelMember' => 'App\Policies\ChannelMemberPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        Passport::tokensExpireIn(Carbon::now()->addMinutes(config('passport.expires.minutes')));

        Passport::refreshTokensExpireIn(Carbon::now()->addDays(config('passport.expires.days')));
    }
}
