<?php

namespace App\Http\Requests\Admin;

use App\Models\Wish;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListWishesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['q' => ['nullable', 'string', 'max:120'], 'status' => ['nullable', Rule::in(Wish::STATUSES)], 'sort' => ['nullable', Rule::in(['newest', 'oldest'])], 'selected' => ['nullable', 'integer', 'min:0'], 'tab' => ['nullable', Rule::in(['list', 'settings'])], 'per_page' => ['nullable', Rule::in([10, 25, 50])], 'page' => ['nullable', 'integer', 'min:1']];
    }
}
