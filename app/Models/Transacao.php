<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transacao extends Model
{
    use HasFactory, \App\Traits\Auditable;
    
    protected $table = 'transacoes';
    
    protected $guarded = [];
    
    protected $appends = ['comprovante_url', 'categoria_label', 'forma_pagamento_label'];

    protected $fillable = [
        'empresa_id',
        'tipo',
        'categoria',
        'data',
        'valor',
        'descricao',
        'observacoes',
        'forma_pagamento',
        'status',
        'venda_id',
        'user_id',
        'comprovante_pagamento',
    ];

    // Relacionamentos
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeEntradas($query)
    {
        return $query->where('tipo', 'ENTRADA');
    }

    public function scopeSaidas($query)
    {
        return $query->where('tipo', 'SAIDA');
    }

    public function scopePorEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopePorPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data', [$dataInicio, $dataFim]);
    }

    // Métodos auxiliares
    public function isEntrada()
    {
        return $this->tipo === 'ENTRADA';
    }

    public function isSaida()
    {
        return $this->tipo === 'SAIDA';
    }

    public function isPago()
    {
        return $this->status === 'PAGO';
    }

    public function isPendente()
    {
        return $this->status === 'PENDENTE';
    }

    // Constantes para categorias
    public const CATEGORIAS_ENTRADA = [
        'VENDA' => 'Venda',
        'ENTRADA_MANUAL' => 'Entrada Manual',
        'OUTROS_RECEBIMENTOS' => 'Outros Recebimentos',
    ];

    public const CATEGORIAS_SAIDA = [
        'RETIRADA_SOCIO' => 'Retirada de Sócio',
        'DESPESA_FIXA' => 'Despesa Fixa',
        'DESPESA_VARIAVEL' => 'Despesa Variável',
        'FORNECEDOR' => 'Pagamento a Fornecedor',
        'SALARIO' => 'Salário',
        'IMPOSTO' => 'Imposto',
        'OUTRAS_SAIDAS' => 'Outras Saídas',
    ];

    public const FORMAS_PAGAMENTO = [
        'DINHEIRO' => 'Dinheiro',
        'PIX' => 'PIX',
        'CREDITO' => 'Crédito',
        'DEBITO' => 'Débito',
        'BOLETO' => 'Boleto',
        'TRANSFERENCIA' => 'Transferência',
        'OUTRO' => 'Outro',
    ];

    public function getCategoriaLabelAttribute()
    {
        if ($this->tipo === 'ENTRADA') {
            return self::CATEGORIAS_ENTRADA[$this->categoria] ?? $this->categoria;
        }
        return self::CATEGORIAS_SAIDA[$this->categoria] ?? $this->categoria;
    }

    public function getFormaPagamentoLabelAttribute()
    {
        return self::FORMAS_PAGAMENTO[$this->forma_pagamento] ?? $this->forma_pagamento;
    }

    public function getComprovanteUrlAttribute()
    {
        if ($this->comprovante_pagamento) {
            return asset('storage/comprovantes/' . $this->comprovante_pagamento);
        }
        return null;
    }

    public function hasComprovante()
    {
        return !empty($this->comprovante_pagamento) && file_exists(storage_path('app/public/comprovantes/' . $this->comprovante_pagamento));
    }

    public function deleteComprovante()
    {
        if ($this->hasComprovante()) {
            \Storage::disk('public')->delete('comprovantes/' . $this->comprovante_pagamento);
            $this->update(['comprovante_pagamento' => null]);
        }
    }
}
