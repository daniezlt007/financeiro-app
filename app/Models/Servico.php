<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    use HasFactory, \App\Traits\Auditable;
    protected $guarded = [];

    /**
     * Relacionamento com empresa (proprietária/criadora)
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Relacionamento many-to-many com empresas (serviços compartilhados)
     */
    public function empresas()
    {
        return $this->belongsToMany(Empresa::class, 'empresa_servico')
            ->withTimestamps();
    }
}
