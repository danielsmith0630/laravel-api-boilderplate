<?php
namespace App\ACL;
use App\Scopes\UserSettingScope;

trait UserSettingACL
{
  use ACL;

  public static function bootUserSettingACL()
  {
    static::addGlobalScope(new UserSettingScope);

    static::creating(function($model)
    {
      // Enforce that the owner of the setting is the current auth user
      $model->user_id = ACL::getUserId() ?? $model->user_id;
    });

    static::updating(function($model)
    {
      // Only the owner can update the application setting
      if ($model->user_id != ACL::getUserId()) {
        abort(403, 'Forbidden');
      }
    });

    static::deleting(function($model)
    {
      // Only the owner can delete the application setting
      if ($model->user_id != ACL::getUserId()) {
        abort(403, 'Forbidden');
      }
    });
  }
}
