<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['tab' => ['nullable', Rule::in(['all', 'image', 'video', 'albums'])], 'q' => ['nullable', 'string', 'max:120'], 'album' => ['nullable', 'integer', Rule::exists('gallery_albums', 'id')], 'type' => ['nullable', Rule::in(['image', 'video'])], 'sort' => ['nullable', Rule::in(['newest', 'oldest', 'position'])], 'view' => ['nullable', Rule::in(['grid', 'list'])], 'selected' => ['nullable', 'integer', 'min:0'], 'edit_album' => ['nullable', 'integer', 'min:1'], 'new' => ['nullable', 'boolean'], 'per_page' => ['nullable', Rule::in([12, 24, 48])], 'page' => ['nullable', 'integer', 'min:1']];
    }
}
