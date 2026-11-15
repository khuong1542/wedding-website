<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListWeddingEventsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $side = $this->query('side', 'groom');
        $this->merge(['side' => $this->route('module') === 'shared-events' ? 'shared' : (in_array($side, ['groom', 'bride'], true) ? $side : 'groom'), 'new' => $this->boolean('new')]);
    }

    public function rules(): array
    {
        return ['side' => ['required', Rule::in(['groom', 'bride', 'shared'])], 'new' => ['boolean'], 'event' => ['nullable', 'integer', 'min:1']];
    }
}
