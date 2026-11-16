<?php

namespace App\Http\Requests\Admin;

use App\Models\RsvpResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListRsvpResponsesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'group' => ['nullable', Rule::in(RsvpResponse::GROUPS)],
            'status' => ['nullable', Rule::in(RsvpResponse::STATUSES)],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'selected' => ['nullable', 'integer', 'min:1'],
            'edit' => ['nullable', 'boolean'],
            'per_page' => ['nullable', Rule::in([10, 25, 50])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
