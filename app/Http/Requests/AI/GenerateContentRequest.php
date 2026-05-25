<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class GenerateContentRequest extends FormRequest
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
            'prompt' => 'required|string|min:10|max:2000',
            'type' => 'nullable|string|in:blog_post,article,description,summary',
            'tone' => 'nullable|string|in:professional,casual,formal,friendly',
            'length' => 'nullable|string|in:short,medium,long',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'prompt.required' => 'The prompt is required.',
            'prompt.min' => 'The prompt must be at least 10 characters.',
            'prompt.max' => 'The prompt must not exceed 2000 characters.',
            'type.in' => 'The type must be one of: blog_post, article, description, summary.',
            'tone.in' => 'The tone must be one of: professional, casual, formal, friendly.',
            'length.in' => 'The length must be one of: short, medium, long.',
        ];
    }
}
