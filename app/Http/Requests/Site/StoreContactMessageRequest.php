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
            'asunto.in' => 'El asunto seleccionado no es válido.',
            'mensaje.min' => 'El mensaje debe tener al menos :min caracteres.',
        ];
    }
}
