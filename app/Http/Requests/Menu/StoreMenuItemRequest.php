<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreMenuItemRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'type' => 'nullable|string|in:url,route,separator',
            'link' => 'nullable|string|max:500',
            'parent_id' => 'nullable|integer|exists:hycms_menu_items,mnit_iditem',
            'order' => 'nullable|integer|min:0',
            'css_class' => 'nullable|string|max:100',
            'target' => 'nullable|string|in:_self,_blank',
            'enabled' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The menu item title is required.',
            'type.in' => 'The type must be one of: url, route, separator.',
            'target.in' => 'The target must be either _self or _blank.',
            'parent_id.exists' => 'The parent menu item does not exist.',
        ];
    }
}
