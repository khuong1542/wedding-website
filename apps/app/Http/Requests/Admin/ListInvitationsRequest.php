<?php

namespace App\Http\Requests\Admin;

use App\Models\Invitation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListInvitationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'], 'status' => ['nullable', Rule::in(Invitation::STATUSES)],
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'name'])], 'tab' => ['nullable', Rule::in(['list', 'templates'])],
            'selected' => ['nullable', 'integer', 'min:1'], 'new' => ['nullable', 'boolean'],
            'template' => ['nullable', Rule::in(array_keys(config('invitation_templates')))],
            'per_page' => ['nullable', Rule::in([10, 20, 50])], 'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
