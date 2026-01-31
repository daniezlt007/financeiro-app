<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot do trait
     */
    public static function bootAuditable()
    {
        // Quando um registro é criado
        static::created(function ($model) {
            $model->auditLog('created', null, $model->getAuditableAttributes());
        });

        // Quando um registro é atualizado
        static::updated(function ($model) {
            $model->auditLog('updated', $model->getOriginal(), $model->getAuditableAttributes());
        });

        // Quando um registro é deletado
        static::deleted(function ($model) {
            $model->auditLog('deleted', $model->getAuditableAttributes(), null);
        });
    }

    /**
     * Registra o log de auditoria
     */
    protected function auditLog($action, $oldValues, $newValues)
    {
        // Ignora se não há usuário autenticado (comandos artisan, seeds, etc)
        if (!Auth::check()) {
            return;
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'action' => $action,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Retorna os atributos que devem ser auditados
     * Sobrescreva este método no model para customizar
     */
    protected function getAuditableAttributes()
    {
        // Remove campos sensíveis e timestamps desnecessários
        $hidden = ['password', 'remember_token'];
        
        return collect($this->getAttributes())
            ->except(array_merge($hidden, ['created_at', 'updated_at']))
            ->toArray();
    }
}


