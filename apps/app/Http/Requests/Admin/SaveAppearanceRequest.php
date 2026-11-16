<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveAppearanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = ['theme' => ['required', Rule::in(array_keys(config('appearance.themes')))], 'heading_font' => ['required', Rule::in(array_keys(config('appearance.fonts')))], 'body_font' => ['required', Rule::in(array_keys(config('appearance.fonts')))], 'font_size' => ['required', 'integer', 'min:12', 'max:20'], 'content_width' => ['required', 'integer', 'min:960', 'max:1600'], 'radius' => ['required', 'integer', 'min:0', 'max:32'], 'sticky_header' => ['required', 'boolean'], 'animations' => ['required', 'boolean']];
        foreach (['primary', 'secondary', 'accent', 'background', 'text', 'border'] as $color) {
            $rules[$color] = ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'];
        }

        return $rules;
    }
}
