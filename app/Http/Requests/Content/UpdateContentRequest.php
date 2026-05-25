<?php

namespace App\Http\Requests\Content;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateContentRequest extends FormRequest
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
        $contentId = $this->route('content')->cont_idcont;

        return [
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:hycms_contents,cont_cdslug,' . $contentId . ',cont_idcont',
            'type' => 'nullable|string|in:post,page,custom',
            'status' => 'nullable|string|in:draft,published,archived',
            'body' => 'nullable|string',
            'body_type' => 'nullable|string|in:html,markdown,plain',
            'summary' => 'nullable|string|max:500',
            'meta' => 'nullable|array',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:hycms_categories,cate_idcate',
            'media' => 'nullable|array',
            'media.*' => 'integer|exists:hycms_media,medi_idmedi',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'slug.required' => 'The slug field is required.',
            'slug.unique' => 'The slug has already been taken.',
            'type.in' => 'The type must be one of: post, page, custom.',
            'status.in' => 'The status must be one of: draft, published, archived.',
            'categories.*.exists' => 'One or more categories do not exist.',
            'media.*.exists' => 'One or more media items do not exist.',
        ];
    }
}
