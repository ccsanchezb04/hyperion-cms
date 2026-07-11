<?php

namespace App\Http\Requests\Site;

use App\Models\ContactMessage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email:rfc', 'max:190'],
            'asunto' => ['required', 'string', Rule::in([
                ContactMessage::SUBJECT_QUOTE,
                ContactMessage::SUBJECT_SUPPORT,
                ContactMessage::SUBJECT_OTHER,
            ])],
            'mensaje' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'email' => 'correo electrónico',
            'asunto' => 'asunto',
            'mensaje' => 'mensaje',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required'  => 'El nombre es obligatorio.',
            'nombre.string'    => 'El nombre debe ser texto.',
            'nombre.max'       => 'El nombre no puede tener más de :max caracteres.',

            'email.required'   => 'El correo electrónico es obligatorio.',
            'email.email'      => 'Ingresa una dirección de correo electrónico válida.',
            'email.max'        => 'El correo electrónico no puede tener más de :max caracteres.',

            'asunto.required'  => 'Debes seleccionar un asunto.',
            'asunto.in'        => 'El asunto seleccionado no es válido.',

            'mensaje.required' => 'El mensaje es obligatorio.',
            'mensaje.string'   => 'El mensaje debe ser texto.',
            'mensaje.min'      => 'El mensaje debe tener al menos :min caracteres.',
            'mensaje.max'      => 'El mensaje no puede superar los :max caracteres.',
        ];
    }
}
