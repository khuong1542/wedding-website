<?php

namespace App\Http\Requests\Admin;

use App\Models\Wish;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWishStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['status' => ['required', Rule::in(Wish::STATUSES)], 'name' => ['prohibited'], 'email' => ['prohibited'], 'message' => ['prohibited']];
    }
}
