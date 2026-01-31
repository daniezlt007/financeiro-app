<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    use HasFactory, \App\Traits\Auditable;
    protected $guarded = [];

public function venda(){ return $this->belongsTo(Venda::class); }
public function user(){ return $this->belongsTo(User::class); }

}
