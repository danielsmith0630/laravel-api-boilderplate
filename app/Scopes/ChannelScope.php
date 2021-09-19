<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\ACL\ACL;

class ChannelScope implements Scope
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
    $builder
      ->whereIn($model->getTable() . '.space_id', ACL::getSpaces()->pluck('id')->toArray())
      ->where(function($query) use ($model) {
        $query->whereIn($model->getTable() . '.id', ACL::getChannels()->pluck('id')->toArray())
          ->orWhereIn($model->getTable() . '.privacy', [
            'protected',
            'public'
          ]);
      });
  }
}
