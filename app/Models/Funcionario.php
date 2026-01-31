<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory, \App\Traits\Auditable;
    
    protected $guarded = [];

    protected $casts = [
        // Campos removidos - casts não são mais necessários
    ];

    /**
     * Relacionamento com empresa
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Scope para funcionários ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', 'ATIVO');
    }

    /**
     * Scope para funcionários por empresa
     */
    public function scopePorEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }
}
