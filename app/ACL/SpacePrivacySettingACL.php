<?php
namespace App\ACL;
use App\Scopes\SpacePrivacySettingScope;

trait SpacePrivacySettingACL
{
    use ACL;

    public static function bootSpacePrivacySettingACL()
    {
        static::addGlobalScope(new SpacePrivacySettingScope);

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
