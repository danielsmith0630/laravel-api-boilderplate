<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\ACL\ACL;

class SpacePrivacySettingScope implements Scope
{
  /**
   * Apply the scope to a given Eloquent query builder.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $builder
   * @param  \Illuminate\Database\Eloquent\Model  $model
   * @return void
   */
  public function apply(Builder $builder, Model $model)
  {
    $builder->whereIn($model->getTable() . '.space_id', ACL::getVisibleSpaceIds());
  }
}
