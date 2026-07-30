<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ActivateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'dominio' => [
                'required',
                'string',
                'max:63',
                'regex:/^(?!-)[a-z0-9]+(?:-[a-z0-9]+)*(?<!-)$/',
                'unique:empresas,dominio',
                'not_in:www,admin,api,app,mail,localhost',
            ],
            'email_admin' => ['required', 'email:rfc', 'max:255', 'unique:empresas,email_admin'],
            'password_admin' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'dominio' => strtolower(trim((string) $this->input('dominio'))),
            'email_admin' => strtolower(trim((string) $this->input('email_admin'))),
        ]);
    }
}
