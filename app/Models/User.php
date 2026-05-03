<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = '_tb_usuarios'; // tabela correta
    protected $primaryKey = 'id_user'; // chave primária
    public $timestamps = true; // já que você tem created_at e updated_at

    protected $fillable = [
        'nome',
        'email',
        'password',
        'unidade_id',
        'tipo',
        'status',
        'nivel',
        'azure_id'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];
    
    public function contagens()
{
    return $this->hasMany(ContagemItem::class, 'usuario_id', 'id_user');
}
}
