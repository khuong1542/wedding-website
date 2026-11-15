<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRegistryRequest extends FormRequest
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
        $rules = [
            'enabled' => ['required', 'boolean'],
            'message' => ['nullable', 'string', 'max:3000'],
            'instructions' => ['nullable', 'string', 'max:6000'],
            'gifts' => ['sometimes', 'array', 'max:30'],
            'gifts.*' => ['array:title,url,note'],
            'gifts.*.title' => ['required', 'string', 'max:120'],
            'gifts.*.url' => ['nullable', 'url:http,https', 'max:1000'],
            'gifts.*.note' => ['nullable', 'string', 'max:1000'],
        ];
        foreach (['bride', 'groom'] as $person) {
            $rules[$person.'_enabled'] = ['required', 'boolean'];
            foreach (['bank', 'account', 'holder'] as $field) {
                $rules[$person.'_'.$field] = ['required_if:'.$person.'_enabled,1', 'nullable', 'string', 'max:160'];
            }
            $rules[$person.'_memo'] = ['nullable', 'string', 'max:255'];
            $rules[$person.'_bank_qr'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];
            $rules['remove_'.$person.'_bank_qr'] = ['sometimes', 'boolean'];
        }

        return $rules;
    }
}
