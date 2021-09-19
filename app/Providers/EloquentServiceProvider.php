<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class EloquentServiceProvider extends ServiceProvider
{
  public function boot()
  {
    Relation::morphMap([
      'user' => 'App\Models\User',
      'user_profile' => 'App\Models\UserProfile',
      'user_setting' => 'App\Models\UserSetting',
      'user_privacy_setting' => 'App\Models\UserPrivacySetting',
      'space' => 'App\Models\Space',
      'space_privacy_setting' => 'App\Models\SpacePrivacySetting',
      'space_member' => 'App\Models\SpaceMember',
      'space_member_role' => 'App\Models\SpaceMemberRole',
      'channel' => 'App\Models\Channel',
      'channel_member' => 'App\Models\ChannelMember',
      'avatar' => 'App\Models\Avatar',
      'banner' => 'App\Models\Banner',
      'file' => 'App\Models\File',
    ]);
  }
}
