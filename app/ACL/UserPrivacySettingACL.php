<?php
namespace App\ACL;
use App\Scopes\UserPrivacySettingScope;

trait UserPrivacySettingACL
{
  use ACL;

  public static function bootUserPrivacySettingACL()
  {
    static::addGlobalScope(new UserPrivacySettingScope);

    static::creating(function($model)
    {
      // Enforce that the creator of the privacy setting is set as the owner
      $model->user_id = ACL::getUserId() ?? $model->user_id;
    });

    static::updating(function($model)
    {
      // Users can only update their own privacy settings
      if ($model->user_id != ACL::getUserId()) {
        abort(403, 'Forbidden');
      }
    });

    static::deleting(function($model)
    {
      // Users can only delete their own privacy settings
      if ($model->user_id != ACL::getUserId()) {
        abort(403, 'Forbidden');
      }
    });
  }
}
