<?php
namespace App\ACL;
use App\Scopes\SpaceMemberScope;

trait SpaceMemberACL
{
  use ACL;

  public static function bootSpaceMemberACL()
  {
    static::addGlobalScope(new SpaceMemberScope);

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
