<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveGalleryAlbumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['id' => ['nullable', 'integer', Rule::exists('gallery_albums', 'id')], 'title' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string', 'max:1000'], 'position' => ['required', 'integer', 'min:0', 'max:100000'], 'is_visible' => ['required', 'boolean']];
    }
}
