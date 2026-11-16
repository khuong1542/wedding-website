<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveWishSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['is_visible' => ['required', 'boolean'], 'allow_submissions' => ['required', 'boolean'], 'require_approval' => ['required', 'boolean'], 'display_limit' => ['required', 'integer', 'min:1', 'max:100'], 'sort' => ['required', Rule::in(['newest', 'oldest'])]];
    }
}
