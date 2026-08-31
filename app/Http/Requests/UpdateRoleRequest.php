<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
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
        $roleId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($roleId),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:roles,id',
                Rule::notIn([$roleId]),
            ],
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
            'is_active.boolean' => 'O campo ativo deve ser verdadeiro ou falso.',
            'parent_id.integer' => 'O perfil pai informado é inválido.',
            'parent_id.exists' => 'O perfil pai selecionado não existe.',
            'parent_id.not_in' => 'Um perfil não pode ser definido como seu próprio pai.',
            'permissions.array' => 'As permissões informadas são inválidas.',
            'permissions.*.exists' => 'Uma das permissões selecionadas não existe.',
        ];
    }
}
