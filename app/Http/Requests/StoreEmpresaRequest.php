<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmpresaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome_fantasia' => ['required','string','max:255'],
            'razao_social'  => ['required','string','max:255'],
            'cnpj'          => ['nullable','string','max:25'],
            'email'         => ['nullable','email','max:255'],
            'telefone'      => ['nullable','string','max:40'],
            'endereco'      => ['nullable','string','max:255'],
            'status'        => ['nullable','in:ATIVA,INATIVA'],
        ];
    }
}


