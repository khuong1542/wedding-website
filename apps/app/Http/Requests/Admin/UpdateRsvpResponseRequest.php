<?php

namespace App\Http\Requests\Admin;

use App\Models\RsvpResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRsvpResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'group' => ['required', Rule::in(RsvpResponse::GROUPS)],
            'status' => ['required', Rule::in(RsvpResponse::STATUSES)],
            'attendees' => ['required', 'integer', $this->input('status') === 'attending' ? 'min:1' : 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
