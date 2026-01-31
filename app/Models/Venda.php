<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    use HasFactory, \App\Traits\Auditable;
    protected $guarded = [];
    
    protected $casts = [
        'funcionario_id' => 'integer',
        'valor_total' => 'decimal:2',
        'percentual_desconto' => 'decimal:2',
        'comissao_venda' => 'decimal:2',
    ];

    // Mutators para converter campos para UPPERCASE
    public function setClienteNomeCompletoAttribute($value)
    {
        $this->attributes['cliente_nome_completo'] = $value ? strtoupper($value) : null;
    }

    public function setClientePlacaAttribute($value)
    {
        $this->attributes['cliente_placa'] = $value ? strtoupper($value) : null;
    }

    public function setClienteChassiAttribute($value)
    {
        $this->attributes['cliente_chassi'] = $value ? strtoupper($value) : null;
    }

    public function funcionario(){ return $this->belongsTo(Funcionario::class); }
    public function itens(){ return $this->hasMany(ItemVenda::class); }
    public function pagamentos(){ return $this->hasMany(Pagamento::class); }
    public function user(){ return $this->belongsTo(User::class); }
    
    // Valores permitidos para o campo parceiro
    public static function getParceirosDisponiveis()
    {
        return [
            'THAI VEICULOS',
            'DUNORTE ANANINDEUA',
            'DU NORTE BELEM',
            'DU NORTE PAC',
            'FÊNIX',
            'PSA ANANINDEUA',
            'PSA BELÉM',
            'FIAT',
            'GWM',
            'TROPICAL NISSAN ANANINDEUA',
            'TROPICAL NISSAN BELÉM',
            'UNIQUE',
            'GAC BELÉM'
        ];
    }

}
