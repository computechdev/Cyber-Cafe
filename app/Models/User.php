<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    const NIVEL_ADMINISTRADOR = 1;
    const NIVEL_SUBADMIN = 2;
    const NIVEL_CLIENTE = 3;
    const NIVEL_SOCIO = 4;
    const NIVEL_FUNCIONARIO = 5;
    const NIVEL_OPERADOR = 6;

    protected $table = 'users';

    protected $fillable = [
        'legacy_user_id',
        'name',
        'email',
        'password',
        'username',
        'legacy_passwd',
        'nivel',
        'cadastro_legado',
        'id_apoio',
        'porcentagem',
        'id_pais',
        'data_corte',
        'validade',
        'revalidar',
        'data_validacao',
        'status',
        'afiliado',
        'fechar_faturas_ponto',
        // 'cpf',
        // 'chave_pix',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'legacy_passwd',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'cadastro_legado' => 'datetime',
        'data_corte' => 'date',
        'validade' => 'date',
        'data_validacao' => 'datetime',
    ];

    public function isAdministrador()
    {
        return (int) $this->nivel === self::NIVEL_ADMINISTRADOR;
    }

    public function isSubadmin()
    {
        return (int) $this->nivel === self::NIVEL_SUBADMIN;
    }

    public function isCliente()
    {
        return (int) $this->nivel === self::NIVEL_CLIENTE;
    }

    public function isSocio()
    {
        return (int) $this->nivel === self::NIVEL_SOCIO;
    }

    public function isFuncionario()
    {
        return (int) $this->nivel === self::NIVEL_FUNCIONARIO;
    }

    public function isOperador()
    {
        return (int) $this->nivel === self::NIVEL_OPERADOR;
    }

    public function temNivel($niveisPermitidos)
    {
        if (!is_array($niveisPermitidos)) {
            $niveisPermitidos = [$niveisPermitidos];
        }

        return in_array((int) $this->nivel, array_map('intval', $niveisPermitidos));
    }
}