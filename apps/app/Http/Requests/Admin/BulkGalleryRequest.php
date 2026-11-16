<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['action' => ['required', Rule::in(['delete', 'album', 'show', 'hide'])], 'ids' => ['required', 'array', 'min:1', 'max:48'], 'ids.*' => ['required', 'integer', 'distinct', Rule::exists('gallery_media', 'id')], 'album_id' => [Rule::requiredIf($this->input('action') === 'album'), 'nullable', 'integer', Rule::exists('gallery_albums', 'id')]];
    }
}
