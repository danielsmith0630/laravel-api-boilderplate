<?php
namespace App\ACL;
use App\Scopes\SpaceScope;

trait SpaceACL
{
  use ACL;

  public static function bootSpaceACL()
  {
    static::addGlobalScope(new SpaceScope);

    static::creating(function($model)
    {
      // Enforce that the creator of the space is set as the owner
      $model->owner_id = ACL::getUserId() ?? $model->created_by;
    });

    static::updating(function($model)
    {
      // Check for ROLES
      // if (!space owner or admin do not allow to update space) {
      //     abort(403, 'Forbidden');
      // }
    });

    static::deleting(function($model)
    {
      // Check for ROLES
      // if (!space owner or admin do not allow to delete space) {
      //     abort(403, 'Forbidden');
      // }
    });
  }
}
