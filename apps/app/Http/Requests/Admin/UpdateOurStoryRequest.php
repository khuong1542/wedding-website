<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOurStoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $locale = $this->query('lang', $this->cookie('admin_locale', 'en'));
        app()->setLocale(in_array($locale, ['en', 'vn'], true) ? $locale : 'en');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'introduction' => ['nullable', 'string', 'max:6000'],
            'milestones' => ['sometimes', 'array', 'max:30'],
            'milestones.*' => ['array:id,title,date,description,photo,remove_photo,is_visible'],
            'milestones.*.id' => ['nullable', 'integer', 'distinct', Rule::exists('story_milestones', 'id')->where('our_story_id', 1)],
            'milestones.*.is_visible' => ['sometimes', 'boolean'],
            'milestones.*.title' => ['required', 'string', 'max:120'],
            'milestones.*.date' => ['nullable', 'date_format:Y-m-d'],
            'milestones.*.description' => ['nullable', 'string', 'max:3000'],
            'milestones.*.photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'milestones.*.remove_photo' => ['sometimes', 'boolean'],
        ];
    }
}
