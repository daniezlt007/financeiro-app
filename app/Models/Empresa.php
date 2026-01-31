<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory, \App\Traits\Auditable;
    protected $fillable = [
        'nome_fantasia',
        'razao_social',
        'cnpj',
        'email',
        'telefone',
        'endereco',
        'status',       // 'ATIVA' | 'INATIVA'
    ];

    // Convenções/constantes
    public const ATIVA   = 1;
    public const INATIVA = 0;

    /**
     * Aceita 'ATIVA'/'INATIVA', true/false, '1'/'0' e converte para int.
     */
    public function setStatusAttribute($value): void
    {
        if (is_string($value)) {
            $upper = mb_strtoupper(trim($value));
            if (in_array($upper, ['ATIVA','ATIVO'], true)) {
                $this->attributes['status'] = self::ATIVA;
                return;
            }
            if (in_array($upper, ['INATIVA','INATIVO'], true)) {
                $this->attributes['status'] = self::INATIVA;
                return;
            }
            // Strings numéricas '1'/'0'
            if (ctype_digit($upper)) {
                $this->attributes['status'] = (int) $upper;
                return;
            }
        }

        // booleanos, ints, etc.
        $this->attributes['status'] = (int) (bool) $value;
    }

    /**
     * Texto amigável para leitura (não mexe no valor do banco).
     */
    public function getStatusTextAttribute(): string
    {
        return ((int) ($this->attributes['status'] ?? 0)) === self::ATIVA ? 'ATIVA' : 'INATIVA';
    }

    // Scopes úteis
    public function scopeAtivas($q)   { return $q->where('status', self::ATIVA); }
    public function scopeInativas($q) { return $q->where('status', self::INATIVA); }

    /**
     * Relacionamento many-to-many com serviços (serviços compartilhados)
     */
    public function servicos()
    {
        return $this->belongsToMany(Servico::class, 'empresa_servico')
            ->withTimestamps();
    }
}
