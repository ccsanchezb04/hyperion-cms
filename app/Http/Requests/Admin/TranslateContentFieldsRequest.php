<?php

namespace App\Http\Requests\Admin;

use App\Services\AITranslator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida el payload del POST /admin/contents/translate (botón "Traducir
 * con AI" en el editor de Content). Title o body son opcionales por
 * separado pero al menos uno debe venir con contenido.
 */
class TranslateContentFieldsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $langs = array_keys(AITranslator::LANGUAGE_NAMES);

        return [
            'source_title'    => ['nullable', 'string', 'max:500'],
            'source_body'     => ['nullable', 'string', 'max:20000'],
            'source_language' => ['nullable', 'string', Rule::in(array_merge(['auto'], $langs))],
            'target_language' => ['required', 'string', Rule::in($langs)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if (empty($this->input('source_title')) && empty($this->input('source_body'))) {
                $v->errors()->add('source_title', 'Provide source_title or source_body to translate.');
            }
        });
    }
}
