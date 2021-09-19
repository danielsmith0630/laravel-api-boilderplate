<?php
namespace App\Traits;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait ModelMetaData
{
  public static function bootModelMetaData()
	{
    static::creating(function($model)
    {
      $model->created_by = Auth::id() ?? $model->created_by ?? 0;
      $model->updated_by = Auth::id() ?? $model->created_by ?? 0;
    });

    static::updating(function($model)
    {
      $model->updated_by = Auth::id() ?? $model->updated_by ?? 0;
      if (!Auth::id()) {
        Log::debug('ModelUpdating', [
          'model' => $model,
          'Auth' => Auth::user(),
          'request' => request()
        ]);
      }
    });
	}

  public function createdBy()
  {
    return $this->belongsTo('App\Models\User', 'created_by', 'id');
  }

  public function updatedBy()
  {
    return $this->belongsTo('App\Models\User', 'updated_by', 'id');
  }

  public function deletedBy()
  {
    return $this->belongsTo('App\Models\User', 'deleted_by', 'id');
  }

  public static function staticModel()
  {
    return with(new static);
  }

  public static function rawColumns()
  {
    return Self::staticModel()->getConnection()->getSchemaBuilder()->getColumnListing(Self::staticModel()->getTable());
  }

  public static function getFields()
  {
    $cols = Self::rawColumns();

    $joinedColumns = is_array(Self::staticModel()->joinedColumns) ? Self::staticModel()->joinedColumns : [];

    $cols = array_merge($cols, $joinedColumns);

    $cols = array_diff($cols, Self::staticModel()->hidden);

    foreach ($cols as $key => $col) {
      if ($col == 'id' || in_array(substr($col, -3), ['_id', '_by'])) {
        $cols[$key] = AllowedFilter::exact($col, in_array($col, $joinedColumns) ? null : Self::staticModel()->getTable() . '.' . $col);
      }
    }

    return $cols;
  }
}
