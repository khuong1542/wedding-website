<?php

namespace App\Http\Requests\Admin;

class PreviewPageBuilderRequest extends SavePageBuilderRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['revision'], $rules['action']);

        return $rules;
    }
}
