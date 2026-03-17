<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    // ── Helpers de rol ────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAbogado(): bool
    {
        return $this->role === 'abogado';
    }

    public function isReadonly(): bool
    {
        return $this->role === 'readonly';
    }

    /**
     * Puede crear y editar casos (admin o abogado).
     */
    public function puedeEditar(): bool
    {
        return in_array($this->role, ['admin', 'abogado']);
    }

    /**
     * Puede ejecutar acciones rápidas del flujo jurídico.
     */
    public function puedeAccionarFlujo(): bool
    {
        return in_array($this->role, ['admin', 'abogado']);
    }

    /**
     * Puede eliminar casos (solo admin).
     */
    public function puedeEliminar(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Puede gestionar usuarios (solo admin).
     */
    public function puedeGestionarUsuarios(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Texto legible del rol.
     */
    public function textoRol(): string
    {
        return match($this->role) {
            'admin'    => 'Administrador',
            'abogado'  => 'Abogado',
            'readonly' => 'Solo lectura',
            default    => 'Usuario',
        };
    }

    /**
     * Color badge del rol para la UI.
     */
    public function colorRol(): string
    {
        return match($this->role) {
            'admin'    => '#2563eb',
            'abogado'  => '#198754',
            'readonly' => '#6b7280',
            default    => '#374151',
        };
    }
}