<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCoupleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [];
        foreach (['groom', 'bride'] as $person) {
            foreach (['full_name', 'nickname', 'address', 'father', 'mother', 'family_address', 'phone'] as $field) {
                $rules[$person.'_'.$field] = [$field === 'full_name' ? 'required' : 'nullable', 'string', 'max:'.(in_array($field, ['address', 'family_address', 'phone'], true) ? 255 : 160)];
            }
            $rules[$person.'_birthday'] = ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'];
            $rules[$person.'_bio'] = ['nullable', 'string', 'max:2000'];
            $rules[$person.'_family_intro'] = ['nullable', 'string', 'max:1500'];
            $rules[$person.'_email'] = ['nullable', 'email', 'max:255'];
            foreach (['facebook', 'instagram'] as $field) {
                $rules[$person.'_'.$field] = ['nullable', 'url:http,https', 'max:255'];
            }
            $rules[$person.'_bank_qr'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];
            $rules['remove_'.$person.'_bank_qr'] = ['sometimes', 'boolean'];
            $rules[$person.'_photo'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];
            $rules['remove_'.$person.'_photo'] = ['sometimes', 'boolean'];
        }
        foreach (['couple_title', 'hashtag'] as $field) {
            $rules[$field] = ['nullable', 'string', 'max:160'];
        }
        foreach (['met_on', 'engaged_on', 'wedding_on'] as $field) {
            $rules[$field] = ['nullable', 'date_format:Y-m-d'];
        }
        foreach (['quote', 'welcome'] as $field) {
            $rules[$field] = ['nullable', 'string', 'max:2000'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [];
        foreach (array_keys($this->rules()) as $field) {
            $person = str_starts_with($field, 'groom_') ? 'groom' : (str_starts_with($field, 'bride_') ? 'bride' : null);
            $key = $person ? substr($field, strlen($person) + 1) : $field;
            $attributes[$field] = ($person ? __('couple.'.$person).' — ' : '').__('couple.'.$key);
        }

        return $attributes;
    }

    protected function prepareForValidation(): void
    {
        $locale = $this->query('lang', $this->cookie('admin_locale', 'en'));
        app()->setLocale(in_array($locale, ['en', 'vn'], true) ? $locale : 'en');
    }
}
