<?php

namespace App\Http\Requests\Admin;

use App\Models\WeddingEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveWeddingEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $locale = $this->query('lang', $this->cookie('admin_locale', 'en'));
        if ($this->route('event_scope') === 'shared') {
            $this->merge(['side' => 'shared']);
        }
        app()->setLocale(in_array($locale, ['en', 'vn'], true) ? $locale : 'en');
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', Rule::exists('wedding_events', 'id')->where(fn ($query) => $this->route('event_scope') === 'shared' ? $query->where('side', 'shared') : $query->whereIn('side', ['groom', 'bride']))],
            'side' => ['required', Rule::in($this->route('event_scope') === 'shared' ? ['shared'] : ['groom', 'bride'])],
            'title' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(WeddingEvent::TYPES)],
            'date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'venue' => ['required', 'string', 'max:255'],
            'map_url' => ['nullable', 'url:http,https', 'max:1000'],
            'description' => ['nullable', 'string', 'max:3000'],
            'is_visible' => ['required', 'boolean'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['sometimes', 'boolean'],
        ];
    }
}
