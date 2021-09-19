<?php
namespace App\Traits;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\MySoftDeletingScope;
use App\ACL\ACL;

trait MySoftDeletes
{
  use SoftDeletes;

  public static function bootSoftDeletes()
  {
    static::addGlobalScope(new MySoftDeletingScope);
  }

  protected function runSoftDelete()
  {
    $query = $this->newModelQuery()->where($this->getKeyName(), $this->getKey());
    $time = $this->freshTimestamp();
    $columns = [
      $this->getDeletedAtColumn() => $this->fromDateTime($time),
      'deleted_by' => ACL::getUserId()
    ];

    $this->{$this->getDeletedAtColumn()} = $time;
    $this->deleted_by = ACL::getUserId();

    if ($this->timestamps && !is_null($this->getUpdatedAtColumn())) {
      $this->{$this->getUpdatedAtColumn()} = $time;
      $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
    }
    $query->update($columns);
  }

  public function restore()
  {
    // If the restoring event does not return false, we will proceed with this
    // restore operation. Otherwise, we bail out so the developer will stop
    // the restore totally. We will clear the deleted timestamp and save.
    if ($this->fireModelEvent('restoring') === false) {
      return false;
    }

    $this->{$this->getDeletedAtColumn()} = null;
    $this->deleted_by = null;

    // Once we have saved the model, we will fire the "restored" event so this
    // developer will do anything they need to after a restore operation is
    // totally finished. Then we will return the result of the save call.
    $this->exists = true;

    $result = $this->save();

    $this->fireModelEvent('restored', false);

    return $result;
  }
}
