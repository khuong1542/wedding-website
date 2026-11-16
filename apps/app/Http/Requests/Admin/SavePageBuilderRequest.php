<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePageBuilderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'revision' => ['required', 'integer', 'min:0'], 'action' => ['required', Rule::in(['draft', 'publish'])],
            'blocks' => ['present', 'array', 'max:50'], 'blocks.*' => ['required', 'array:id,type,name,title,text,url,button_label,align,background,padding,height,desktop,tablet,mobile,animation,duration'],
            'blocks.*.id' => ['required', 'uuid', 'distinct'], 'blocks.*.type' => ['required', Rule::in(array_keys(config('page_builder.blocks')))],
            'blocks.*.name' => ['required', 'string', 'max:120'], 'blocks.*.title' => ['present', 'nullable', 'string', 'max:120'], 'blocks.*.text' => ['present', 'nullable', 'string', 'max:3000'],
            'blocks.*.url' => ['present', 'nullable', 'url:http,https', 'max:2048'], 'blocks.*.button_label' => ['present', 'nullable', 'string', 'max:80'],
            'blocks.*.align' => ['required', Rule::in(['left', 'center', 'right'])], 'blocks.*.background' => ['required', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'blocks.*.padding' => ['required', 'integer', 'min:0', 'max:120'], 'blocks.*.height' => ['required', 'integer', 'min:0', 'max:900'],
            'blocks.*.desktop' => ['required', 'boolean'], 'blocks.*.tablet' => ['required', 'boolean'], 'blocks.*.mobile' => ['required', 'boolean'],
            'blocks.*.animation' => ['required', Rule::in(['none', 'fade'])], 'blocks.*.duration' => ['required', 'integer', 'min:100', 'max:2000'],
        ];
    }
}
