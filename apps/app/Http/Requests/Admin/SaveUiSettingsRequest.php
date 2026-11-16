<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class SaveUiSettingsRequest extends SaveAppearanceRequest
{
    public function rules(): array
    {
        return parent::rules() + [
            'mode' => ['required', Rule::in(['light', 'dark', 'system'])], 'layout' => ['required', Rule::in(['full', 'boxed', 'wide'])],
            'shadow' => ['required', Rule::in(['none', 'soft', 'medium', 'strong'])], 'spacing' => ['required', 'integer', 'min:0', 'max:40'],
            'button_style' => ['required', Rule::in(['filled', 'outline'])], 'button_size' => ['required', Rule::in(['small', 'medium', 'large'])],
            'hover_effect' => ['required', 'boolean'], 'smooth_scroll' => ['required', 'boolean'],
        ];
    }
}
