<?php

namespace App\Traits;

use Plank\Mediable\Media;
use Illuminate\Support\Facades\Storage;
use Plank\Mediable\MediaUploaderFacade;

trait MediaTrait
{
    public function mediaUpload($file, $model, $disk, $directory, $tag, $mediaFileName = null, $description = null)
    {
        if ($file) {
            $this->upload($file, $model, $disk, $directory, $tag, $mediaFileName, $description);
        }
    }

    public function mediaUpdate($file, $model, $disk, $directory, $tag)
    {
        if ($model->hasMedia($tag)) {
            $media = $model->getMedia($tag);
            $model->detachMedia($media, $tag);
            Media::find($media->first()->id)->delete();
        }
        $this->upload($file, $model, $disk, $directory, $tag);
    }

    public function mediaDelete($model, $tag, $disk, $directory)
    {
        if ($model->hasMedia($tag) && !empty($directory)) {
            $media = $model->getMedia($tag);
            $mediaIds = $media->pluck('id')->all();
            foreach ($mediaIds as $mediaId) {
                Media::find($mediaId)->delete();
                Storage::disk($disk)->delete($media);
            }
        } else {
            return response()->json('Directory is empty.');
        }
    }

    public function mediaSingleDelete($model, $tag, $disk, $directory, $mediaId)
    {
        if ($model->hasMedia($tag) && !empty($directory)) {
            $media = Media::where('id', $mediaId)->first();
            $media->delete();
            Storage::disk($disk)->delete($media);
        } else {
            return response()->json('Directory is empty.');
        }
    }

    private function upload($file, $model, $disk, $directory, $tag, $mediaFileName = null, $description = null)
    {
        $media = MediaUploaderFacade::fromSource($file)
            ->toDestination($disk, $directory)
            ->useHashForFilename()
            ->upload();
        $media->update(['file_original_name' => ($mediaFileName) ? $mediaFileName : $file->getClientOriginalName(), 'description' => $description]);
        $model->attachMedia($media, $tag);

        return $model;
    }
}
