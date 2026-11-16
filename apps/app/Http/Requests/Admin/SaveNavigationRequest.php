<?php

namespace App\Http\Requests\Admin;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveNavigationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['menu', 'footer_links'] as $field) {
            if (is_string($this->input($field))) {
                $decoded = json_decode($this->input($field), true);
                if (is_array($decoded)) {
                    $this->merge([$field => $decoded]);
                }
            }
        }
    }

    private function linkRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value)) {
                return;
            }
            $relative = preg_match('~^(?:/(?!/)[a-zA-Z0-9/_?=&%.:#\~-]*|\#[a-zA-Z][a-zA-Z0-9_-]*)$~', $value);
            $external = filter_var($value, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($value, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true);
            if (! $relative && ! $external) {
                $fail(__('navigation.invalid_url'));
            }
        };
    }

    public function rules(): array
    {
        $rules = [
            'menu' => ['present', 'array', 'max:20'], 'footer_links' => ['present', 'array', 'max:20'],
            'footer' => ['required', 'array:brand,description,copyright,email,phone,address,facebook,instagram,youtube,tiktok'],
            'footer.brand' => ['required', 'string', 'max:120'], 'footer.description' => ['present', 'nullable', 'string', 'max:1000'], 'footer.copyright' => ['present', 'nullable', 'string', 'max:255'],
            'footer.email' => ['present', 'nullable', 'email', 'max:255'], 'footer.phone' => ['present', 'nullable', 'string', 'max:30', 'regex:/^[0-9+().\s-]*$/'], 'footer.address' => ['present', 'nullable', 'string', 'max:500'],
            'display' => ['required', 'array:layout,logo_position,show_icons,new_tab,show_footer'],
            'display.layout' => ['required', Rule::in(['horizontal', 'stacked'])], 'display.logo_position' => ['required', Rule::in(['left', 'center'])],
            'display.show_icons' => ['required', 'boolean'], 'display.new_tab' => ['required', 'boolean'], 'display.show_footer' => ['required', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], 'remove_logo' => ['sometimes', 'boolean'],
        ];
        foreach (['menu', 'footer_links'] as $list) {
            $rules[$list.'.*'] = ['required', 'array:id,label,url,icon,visible'];
            $rules[$list.'.*.id'] = ['required', 'uuid', 'distinct'];
            $rules[$list.'.*.label'] = ['required', 'string', 'max:80'];
            $rules[$list.'.*.url'] = ['required', 'string', 'max:1000', $this->linkRule()];
            $rules[$list.'.*.icon'] = ['required', Rule::in(config('website_navigation.icons'))];
            $rules[$list.'.*.visible'] = ['required', 'boolean'];
        }
        foreach (['facebook', 'instagram', 'youtube', 'tiktok'] as $social) {
            $rules['footer.'.$social] = ['present', 'nullable', 'url:http,https', 'max:1000'];
        }

        return $rules;
    }
}
