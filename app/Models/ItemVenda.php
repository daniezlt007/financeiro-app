<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemVenda extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'itens_venda';

public function venda(){ return $this->belongsTo(Venda::class); }
public function servico(){ return $this->belongsTo(Servico::class); }
public function produto(){ return $this->belongsTo(Produto::class); }

}
