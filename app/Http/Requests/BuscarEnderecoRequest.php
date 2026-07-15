<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BuscarEnderecoRequest extends FormRequest
{
    /**
     * Não há autenticação neste projeto — qualquer requisição pode consultar um CEP.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Remove hífen, espaços e pontos do CEP antes de validar,
     * para que a regra "digits:8" veja só os dígitos.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'cep' => preg_replace('/[^0-9]/', '', (string) $this->input('cep')),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cep' => ['required', 'digits:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'cep.required' => 'O CEP é obrigatório.',
            'cep.digits' => 'O CEP deve conter exatamente 8 dígitos (após remover hífen, pontos e espaços).',
        ];
    }
}
