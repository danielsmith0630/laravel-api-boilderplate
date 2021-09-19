<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Resources\ItemResource;
use App\Http\Resources\CollectionResource;

use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function jsonCollection($data, int $status = null, array $headers = [])
    {
        $response = (new CollectionResource($data))->response();

        if ($status) {
          $response->setStatusCode($status);
        }

        $headers['Content-Type'] = 'application/vnd.api+json';
        $response->withHeaders($headers);

        return $response;
    }

    protected function jsonItem($data, int $status = null, array $headers = [])
    {
        $response = (new ItemResource($data))->response();

        if ($status) {
          $response->setStatusCode($status);
        }

        $headers['Content-Type'] = 'application/vnd.api+json';
        $response->withHeaders($headers);

        return $response;
    }
}
