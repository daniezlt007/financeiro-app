<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Cliente extends Model
{
    use HasFactory, Auditable;
    protected $guarded = [];

    /**
     * Relacionamento com empresa
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Relacionamento com vendas
     */
    public function vendas()
    {
        return $this->hasMany(Venda::class);
    }
}
