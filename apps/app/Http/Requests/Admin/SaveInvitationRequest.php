<?php

namespace App\Http\Requests\Admin;

use App\Models\Invitation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', Rule::exists('invitations', 'id')],
            'title' => ['required', 'string', 'max:120'], 'status' => ['required', Rule::in(Invitation::STATUSES)],
            'template' => ['required', Rule::in(array_keys(config('invitation_templates')))],
            'description' => ['nullable', 'string', 'max:3000'], 'groom_name' => ['nullable', 'string', 'max:120'],
            'bride_name' => ['nullable', 'string', 'max:120'], 'date' => ['nullable', 'date_format:Y-m-d'],
            'time' => ['nullable', 'date_format:H:i'], 'venue' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:3000'], 'accent' => ['required', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'seo_title' => ['nullable', 'string', 'max:160'], 'seo_description' => ['nullable', 'string', 'max:320'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], 'remove_photo' => ['sometimes', 'boolean'],
        ];
    }
}
