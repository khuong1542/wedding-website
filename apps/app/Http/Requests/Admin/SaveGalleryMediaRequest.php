<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveGalleryMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['id' => ['nullable', 'integer', Rule::exists('gallery_media', 'id')], 'title' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string', 'max:3000'], 'type' => ['required', Rule::in(['image', 'video'])], 'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], 'video_url' => [Rule::requiredIf($this->input('type') === 'video'), 'nullable', 'url:http,https', 'max:1000'], 'alt_text' => ['nullable', 'string', 'max:255'], 'album_id' => ['nullable', 'integer', Rule::exists('gallery_albums', 'id')], 'position' => ['required', 'integer', 'min:0', 'max:100000'], 'is_visible' => ['required', 'boolean']];
    }
}
