<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\AllowedFilter;
use App\Traits\MySoftDeletes;
use App\Traits\ModelMetaData;

class BaseModel extends Model
{

	use HasFactory, MySoftDeletes, ModelMetaData;
	protected $dates = ['deleted_at'];

	protected $fillable = [];

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
