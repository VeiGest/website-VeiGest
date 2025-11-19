<?php

namespace backend\modules\api\v1\models;

use common\models\User as BaseUser;

/**
 * User API model
 * Extends the base User model with API-specific functionality
 */
class User extends BaseUser
{
    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        return [
            'id',
            'username',
            'nome',
            'email',
            'company_id',
            'cargo',
            'telefone',
            'data_nascimento',
            'numero_carta_conducao',
            'validade_carta',
            'categoria_carta',
            'estado',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        return [
            'company',
        ];
    }

    /**
     * Get company relationship
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'username' => 'Nome de Utilizador',
            'nome' => 'Nome',
            'cargo' => 'Cargo',
            'telefone' => 'Telefone',
            'data_nascimento' => 'Data de Nascimento',
            'numero_carta_conducao' => 'Número da Carta de Condução',
            'validade_carta' => 'Validade da Carta',
            'categoria_carta' => 'Categoria da Carta',
        ]);
    }
}