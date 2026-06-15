<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';
    protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
    'validade_cnh' => 'date', 
];

    protected $fillable = [
        'cpf',
        'name',
        'data_nascimento',
        'telefone',
        'login',
        'password',
        'perfil',
        'cnh',
        'validade_cnh',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // relacionamentos
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'cliente_id');
    }

    public function logAtividades()
    {
        return $this->hasMany(LogAtividade::class);
    }

    // helpers de permissão
    public function isGerente(): bool
    {
        return $this->role->name === 'gerente';
    }

    public function isOperador(): bool
    {
        return $this->role->name === 'operador';
    }

    public function isCliente(): bool
    {
        return $this->role->name === 'cliente';
    }
}