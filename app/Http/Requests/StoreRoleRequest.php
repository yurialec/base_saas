<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer', 'exists:roles,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'O nome do perfil é obrigatório.',
            'name.max' => 'O nome do perfil deve ter no máximo 255 caracteres.',
            'name.unique' => 'Já existe um perfil cadastrado com este nome.',
            'description.string' => 'A descrição deve ser um texto.',
            'parent_id.integer' => 'O perfil pai informado é inválido.',
            'parent_id.exists' => 'O perfil pai selecionado não existe.',
            'permissions.array' => 'As permissões informadas são inválidas.',
            'permissions.*.exists' => 'Uma das permissões selecionadas não existe.',
        ];
    }
}
