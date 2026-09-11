<?php

namespace App\Http\Requests\Auth;

use App\Rules\CpfCnpj;
use Illuminate\Foundation\Http\FormRequest;

class RegisterCompanyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $normalized = [];

        if (is_string($this->input('phone'))) {
            $normalized['phone'] = preg_replace('/^\+|[\s().-]/', '', $this->input('phone'));
        }

        if (is_string($this->input('cpf_cnpj'))) {
            $normalized['cpf_cnpj'] = strtoupper(preg_replace('/[\s.\/-]/', '', $this->input('cpf_cnpj')));
        }

        $this->merge($normalized);
    }

    public function rules()
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'phone' => ['bail', 'required', 'string', 'regex:/^[1-9][0-9]{9,14}$/'],
            'cpf_cnpj' => ['bail', 'required', 'string', new CpfCnpj()],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages()
    {
        return [
            'phone.required' => 'Informe o telefone da empresa.',
            'phone.string' => 'Informe um telefone válido.',
            'phone.regex' => 'Informe um telefone com DDD, contendo de 10 a 15 dígitos.',
            'cpf_cnpj.required' => 'Informe o CPF ou CNPJ da empresa.',
            'cpf_cnpj.string' => 'Informe um CPF ou CNPJ válido.',
        ];
    }
}
