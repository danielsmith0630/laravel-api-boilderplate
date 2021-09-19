<?php
namespace App\ACL;
use App\Scopes\UserProfileScope;

trait UserProfileACL
{
  use ACL;

  public static function bootUserProfileACL()
  {
    static::addGlobalScope(new UserProfileScope);

    static::creating(function($model)
    {
      // Enforce that the owner of the profile is the current auth user
      $model->user_id = ACL::getUserId() ?? $model->user_id;
    });

    static::updating(function($model)
    {
      // Only the owner can update the profile
      if ($model->user_id != ACL::getUserId()) {
          abort(403, 'Forbidden');
      }
    });

    static::deleting(function($model)
    {
      // Only the owner can delete the profile
      if ($model->user_id != ACL::getUserId()) {
          abort(403, 'Forbidden');
      }
    });
  }
}
