<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveContentPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', Rule::exists('content_pages', 'id')],
            'groom_name' => ['nullable', 'string', 'max:120'],
            'bride_name' => ['nullable', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['sometimes', 'required', 'string', 'max:160', 'regex:~^/(?:[a-z0-9]+(?:-[a-z0-9]+)*(?:/[a-z0-9]+(?:-[a-z0-9]+)*)*)?$~', Rule::unique('content_pages', 'slug')->ignore((int) $this->input('id', 0))],
            'is_visible' => ['sometimes', 'required', 'boolean'],
            'summary' => ['nullable', 'string', 'max:3000'],
            'seo_title' => ['nullable', 'string', 'max:160'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'canonical_url' => ['nullable', 'url:http,https', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['sometimes', 'boolean'],
            'blade_template' => ['required', Rule::in(array_keys(config('page_templates')))],
            'sections' => ['prohibited'],
        ];
    }
}
