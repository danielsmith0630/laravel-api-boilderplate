<?php
namespace App\Scopes;
use App\ACL\ACL;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MySoftDeletingScope extends SoftDeletingScope
{
  public function extend(Builder $builder)
  {
    foreach ($this->extensions as $extension) {
      $this->{"add{$extension}"}($builder);
    }
    $builder->onDelete(function (Builder $builder) {
      $model = $builder->getModel();
      $column = $this->getDeletedAtColumn($builder);
      return $builder->update([
        $model->getTable() . '.' . $column => $model->freshTimestampString(),
        $model->getTable() . '.deleted_by' => ACL::getUserId()
      ]);
    });
  }
  protected function addRestore(Builder $builder)
  {
    $builder->macro('restore', function (Builder $builder) {
      $model = $builder->getModel();
      $builder->withTrashed();
      return $builder->update([
        $model->getTable() . '.' . $model()->getDeletedAtColumn() => null,
        $model->getTable() . '.deleted_by' => null
      ]);
    });
  }
}
