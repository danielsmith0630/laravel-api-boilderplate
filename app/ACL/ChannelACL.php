<?php
namespace App\ACL;
use App\Scopes\ChannelScope;

trait ChannelACL
{
  use ACL;

  public static function bootChannelACL()
  {
    static::addGlobalScope(new ChannelScope);

    static::creating(function($model)
    {
      // Enforce that the creator of the channel is set as the owner
      $model->owner_id = ACL::getUserId() ?? $model->created_by;
    });
  }
}
