<?php
namespace App\ACL;

use Illuminate\Support\Facades\Auth;
use App\Models\{
  Space,
  SpaceMember,
  UserPrivacySetting
};
use App\Scopes\{
  SpaceScope,
  SpaceMemberScope,
  UserPrivacySettingScope
};

trait ACL
{
  private static $user;
  private static $spaces;
  private static $channels;
  private static $visibleUserIds;
  private static $visibleSpaceIds;

  public static function reset()
  {
    self::$user = null;
    self::$spaces = null;
    self::$channels = null;
    self::$visibleUserIds = null;
    self::$visibleSpaceIds = null;
  }

  public static function getUser()
  {
    if(!self::$user) {
      self::$user = Auth::user();
    }
    return self::$user;
  }

  public static function getUserId()
  {
    return self::getUser() ? self::getUser()->id : null;
  }

  public static function getSpaces()
  {
    if(!self::$spaces) {
      self::$spaces = Auth::user()->loadMissing('spaces')->spaces;
    }
    return self::$spaces;
  }

  public static function getChannels()
  {
    if(!self::$channels) {
      self::$channels = Auth::user()->loadMissing('channels')->channels;
    }
    return self::$channels;
  }

  public static function getVisibleUserIds()
  {
    if (!self::$visibleUserIds) {
      $spaceIds = self::getSpaces()->pluck('id')->toArray();
      $spaceUserIds = SpaceMember::withoutGlobalScope(SpaceMemberScope::class)
        ->where('space_id', $spaceIds)->get()->pluck('user_id')->toArray();

      $userIds = UserPrivacySetting::withoutGlobalScope(UserPrivacySettingScope::class)
        ->where('is_public', 1)->get()->pluck('user_id')->toArray();

      self::$visibleUserIds = array_unique(
        array_merge($spaceUserIds, $userIds, [ self::getUserId() ])
      );
    }
    return self::$visibleUserIds;
  }

  public static function getVisibleSpaceIds()
  {
    if (!self::$visibleSpaceIds) {
      $belongedSpaceIds = self::getSpaces()->pluck('id')->toArray();
      $nonPrivateSpaceIds = Space::withoutGlobalScope(SpaceScope::class)
        ->whereIn('privacy', [ 'public', 'protected' ])->get()->pluck('id')->toArray();

      self::$visibleSpaceIds = array_unique(
        array_merge($belongedSpaceIds, $nonPrivateSpaceIds)
      );
    }
    return self::$visibleSpaceIds;
  }
}
