<?php
namespace App\ACL;
use App\Scopes\ChannelMemberScope;

trait ChannelMemberACL
{
  use ACL;

  public static function bootChannelMemberACL()
  {
    static::addGlobalScope(new ChannelMemberScope);
  }
}
