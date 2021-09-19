<?php
namespace App\Services;

use App\Models\File;
use App\Rules\Base64Image;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Storage;

class FileService {
    public static $TYPE_AVATAR = "avatar";
    public static $TYPE_BANNER = "banner";

    protected $request;
    protected $file;

    protected $fileData;
    protected $fileName;
    protected $mimeType;
    protected $extension;

    protected $models;
    protected $path;
    protected $errors = [];
    protected $saved = [];
    protected $type;
    protected $spaceId;

    public function __construct(Request $request, $models, $path, $type, $spaceId)
    {
        if ($request->hasFile($type) || $request->input($type)) {
            $this->request = $request;
            if ($request->hasFile($type)) {
                $this->file = $request->file($type);
            } else {
                $this->fileData = base64_decode($request->input($type));
            }
            $this->models = $models;
            $this->path = $path;
            $this->setType($type);
            $this->spaceId = $spaceId;
        } else {
            $this->errors[] = 'No file present.';
        }
    }

    public function errors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return !!count($this->errors);
    }

    public function successful()
    {
        return !count($this->errors);
    }

    public function saved()
    {
        return collect($this->saved);
    }

    public function upload()
    {
        if ($this->hasErrors()) {
            return $this->errors;
        }

        $savedPath = "";
        if (!$this->request->hasFile($this->type)) {
            $f = finfo_open();

            $this->mimeType = finfo_buffer($f, $this->fileData, FILEINFO_MIME_TYPE);
            $valid_types = [
                'image/png' => 'png',
                'image/jpeg' => 'jpg',
                'image/svg+xml' => 'svg'
            ];
            $this->extension = $valid_types[$this->mimeType];
            $this->fileName = Str::uuid();

            $savedPath = $this->getPath() . '/' . $this->fileName . '.' . $this->extension;

            Storage::disk('s3')
                ->put($savedPath, $this->fileData, 'public');
        } else {
            $name = Str::uuid() . '.' . $this->file->getClientOriginalExtension();
    
            $savedPath = $this->file->storeAs(
                $this->getPath(), $name, 's3'
            );
        }

        if (!$savedPath) {
            $this->logError($name);
            $this->errors[] = 'Could not save file';
            return false;
        } else {
            $this->saveModels($savedPath);
            return true;
        }
    }

    protected function setType($type) {
        $this->type = $type;
    }

    protected function getPath()
    {
        return app()->environment() . $this->path;
    }

    protected function logError($name)
    {
        \Log::error("File Storage Error:", [
            "user" => ACL::getUser(),
            "path" => $this->path,
            "name" => $name,
            "request" => $this->request->all()
        ]);
    }

    protected function record($model, $path)
    {
        $file = new File();
        $file->user_id = $this->request->user()->id;
        $file->space_id = $this->spaceId;
        $file->path = $path;
        $file->url = env('AWS_URL') . $path;
        if (!$this->request->hasFile($this->type)) {
            $file->name = $this->fileName;
            $file->extension = $this->extension;
            $file->mime = $this->mimeType;
            $file->size = (int)(strlen(rtrim($this->fileData, '=')) * 3 / 4);
        } else {
            $file->name = $this->file->getClientOriginalName();
            $file->extension = $this->file->getClientOriginalExtension();
            $file->mime = $this->file->getMimeType();
            $file->size = $this->file->getSize();
        }
        array_push($this->saved, $model->file()->save($file));
    }

    protected function saveModels($path)
    {
        if ($this->models instanceof Collection) {
            $this->models->each(function ($model, $key) use ($path) {
                $this->record($model, $path);
            });
        } else {
            $this->record($this->models, $path);
        }
    }
}
