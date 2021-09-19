<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
  private $relationships = null;
  private $request;

  public function toArray($request)
  {
    $this->request = $request;

    return $this->formatResource($this->resource, $this->getMorphClass(), $this->request->fullUrl());
  }

  public function formatResource($model, $name, $linkToSelf = null)
  {
    // $data = array_filter($model->toArray(), function($relationName) {
    //   return $relationName[0] !== '_';
    // }, ARRAY_FILTER_USE_KEY);

    $data = [
      'type'          => $name,
      'id'            => (string) $model->getKey(),
      'attributes'    => $model->attributesToArray(),
      'relationships' => null,
      'includes'      => null
    ];

    if ($linkToSelf) {
      $data['links'] = [
        'self' => $linkToSelf
      ];
    }

    $resource = array_merge($data, $this->processRelations($model));

    return $resource;
  }

  public function processRelations($model)
  {
    $relationships = $model->getRelations();

    $data = [
      'relationships' => (object) [],
      'includes'      => []
    ];

    foreach($relationships as $key => $relation)
    {
      if ($key === 'pivot') {
        continue;
      }
      if ($relation instanceof \Illuminate\Database\Eloquent\Collection) {
        $this->processCollection($data, $key, $relation);
      } else {
        $this->processModel($data, $key, $relation, $model);
      }
    }

    return $data;
  }

  public function processCollection(&$data, $key, $collection)
  {
    if (!isset($data['relationships']->$key)) {
      $data['relationships']->$key = (object) [
        'data' => []
      ];
    }

    foreach($collection as $model) {
      if (!$model) {
        $model = $collection->$key()->getRelated();
      }

      $name = $model->getMorphClass();

      if($name !== 'pivot') {
        $data['relationships']->{$key}->data[] = $this->formatRelation($model, $name);
        $data['includes'][] = $this->formatResource($model, $name);
      }
    }
  }

  public function processModel(&$data, $key, $relation, $model)
  {
    if (!isset($data['relationships']->$key)) {
      $data['relationships']->$key = (object) [
        'data' => null
      ];
    }

    if (!$relation) {
      $relation = $model->$key()->getRelated();
    }

    $name = $relation->getMorphClass();

    if ($name !== 'pivot') {
      $data['relationships']->{$key}->data = $this->formatRelation($relation, $name);
      $data['includes'][] = $this->formatResource($relation, $name);
    }
  }

  public function formatRelation($model, $name)
  {
    return [
      'type'  => $name,
      'id'    => $model->getKey()
    ];
  }

  // public function processIncludes($model)
  // {
  //   return $this->formatResource($model);
  // }
}
