<?php
namespace App\ACL;
use App\Scopes\SpaceMemberRoleScope;

trait SpaceMemberRoleACL
{
  use ACL;

  public static function bootSpaceMemberRoleACL()
  {
    static::addGlobalScope(new SpaceMemberRoleScope);

    static::creating(function($model)
    {
      //
    });

    static::updating(function($model)
    {
      //
    });

    static::deleting(function($model)
    {
      //
    });
  }
}
