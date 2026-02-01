<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parceiro extends Model
{
    use HasFactory;

    protected $fillable = ['nome_parceiro'];

    public function setNomeParceiroAttribute($value)
    {
        $this->attributes['nome_parceiro'] = $value ? strtoupper($value) : null;
    }
}
