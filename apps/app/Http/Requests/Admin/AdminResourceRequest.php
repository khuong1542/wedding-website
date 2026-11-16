<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['resource_id' => $this->route('event') ?? $this->route('milestone') ?? $this->route('contentPage') ?? $this->route('response') ?? $this->route('invitation') ?? $this->route('wish')]);
    }

    public function rules(): array
    {
        return ['resource_id' => ['required', 'integer', 'min:1']];
    }
}
