<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user')->user_iduser;

        return [
            'name' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|string|email|max:150|unique:hycms_users,user_dsemai,' . $userId . ',user_iduser',
            'password' => 'nullable|string|min:8',
            'status' => 'nullable|string|in:active,inactive',
            'roles' => 'nullable|array',
            'roles.*' => 'string|exists:hycms_roles,role_cdslug',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The user name is required.',
            'email.required' => 'The email is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'password.min' => 'The password must be at least 8 characters.',
            'status.in' => 'The status must be either active or inactive.',
            'roles.*.exists' => 'One or more roles do not exist.',
        ];
    }
}
