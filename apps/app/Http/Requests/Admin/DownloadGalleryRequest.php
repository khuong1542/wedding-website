<?php

namespace App\Http\Requests\Admin;

class DownloadGalleryRequest extends BulkGalleryRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['action'], $rules['album_id']);

        return $rules;
    }
}
