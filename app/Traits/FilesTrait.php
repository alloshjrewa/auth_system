<?php
namespace App\Traits;

use App\Traits\ResponseTrait;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


trait FilesTrait {
    use ResponseTrait;
    public function uploadFile(UploadedFile $file, $path, $disk = '')
    {

        $filename =   $this->StrategyName($file);
        $file->storeAs($path, $filename, $disk);

        return $filename;
    }

    public function deleteFile($path,$disk ,$dir)
    {
        $path = $dir . $path;
        if (Storage::disk($disk)->exists($path)) {
             Storage::disk($disk)->delete($path);
        }
        else {
            $this->errorResponse(404 , "photo does not exists");
        }
    }
    public function StrategyName(UploadedFile $file)
    {
        $filename =  time() . '_' . $file->getClientOriginalName();

        return $filename;
    }

}
