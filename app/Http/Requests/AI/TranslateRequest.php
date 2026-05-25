<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class TranslateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'content' => 'required|string|min:10',
            'target_language' => 'required|string|in:es,fr,de,it,pt,ja,zh,ru,ar',
            'source_language' => 'nullable|string|in:auto,es,fr,de,it,pt,ja,zh,ru,ar,en',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'content.required' => 'The content is required.',
            'content.min' => 'The content must be at least 10 characters.',
            'target_language.required' => 'The target language is required.',
            'target_language.in' => 'The target language must be one of: es, fr, de, it, pt, ja, zh, ru, ar.',
            'source_language.in' => 'The source language must be one of: auto, es, fr, de, it, pt, ja, zh, ru, ar, en.',
        ];
    }
}
