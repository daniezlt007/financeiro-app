<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'model_type',
        'model_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Relacionamento com o usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retorna o nome amigável da ação
     */
    public function getActionNameAttribute()
    {
        return match($this->action) {
            'created' => 'Criação',
            'updated' => 'Atualização',
            'deleted' => 'Exclusão',
            'viewed' => 'Visualização',
            default => $this->action,
        };
    }

    /**
     * Retorna o nome amigável do model
     */
    public function getModelNameAttribute()
    {
        $modelNames = [
            'Cliente' => 'Cliente',
            'Venda' => 'Venda',
            'Produto' => 'Produto',
            'Servico' => 'Serviço',
            'Funcionario' => 'Funcionário',
            'Empresa' => 'Empresa',
            'Pagamento' => 'Pagamento',
            'Transacao' => 'Transação',
            'Meta' => 'Meta',
            'Recorrencia' => 'Recorrência',
            'User' => 'Usuário',
        ];

        $shortName = class_basename($this->model_type);
        return $modelNames[$shortName] ?? $shortName;
    }
}
