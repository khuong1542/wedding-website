<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CouplePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['person' => $this->route('person'), 'kind' => $this->route('kind', 'photo')]);
    }

    public function rules(): array
    {
        return ['person' => ['required', Rule::in(['groom', 'bride'])], 'kind' => ['required', Rule::in(['photo', 'bank_qr'])]];
    }
}
