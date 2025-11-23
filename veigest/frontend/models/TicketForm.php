<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * TicketForm is the model behind the ticket form.
 */
class TicketForm extends Model
{
    public $name;
    public $email;
    public $priority;
    public $category;
    public $subject;
    public $body;
    public $verifyCode;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [['name', 'email', 'subject', 'body'], 'required'],
            // email has to be a valid email address
            ['email', 'email'],
            // priority and category are required
            [['priority', 'category'], 'required'],
            // priority must be one of the valid values
            ['priority', 'in', 'range' => ['low', 'medium', 'high', 'urgent']],
            // category must be one of the valid values
            ['category', 'in', 'range' => ['technical', 'billing', 'account', 'feature', 'other']],
            // verifyCode needs to be entered correctly
            ['verifyCode', 'captcha'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Nome',
            'email' => 'Email',
            'priority' => 'Prioridade',
            'category' => 'Categoria',
            'subject' => 'Assunto',
            'body' => 'Descrição',
            'verifyCode' => 'Código de Verificação',
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     *
     * @param string $email the target email address
     * @return bool whether the email was sent
     */
    public function sendEmail($email)
    {
        return Yii::$app->mailer->compose()
            ->setTo($email)
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setReplyTo([$this->email => $this->name])
            ->setSubject('Novo Ticket de Suporte: ' . $this->subject)
            ->setTextBody($this->body)
            ->setHtmlBody('<p><strong>Nome:</strong> ' . $this->name . '</p>' .
                         '<p><strong>Email:</strong> ' . $this->email . '</p>' .
                         '<p><strong>Prioridade:</strong> ' . $this->getPriorityLabel() . '</p>' .
                         '<p><strong>Categoria:</strong> ' . $this->getCategoryLabel() . '</p>' .
                         '<p><strong>Assunto:</strong> ' . $this->subject . '</p>' .
                         '<p><strong>Descrição:</strong></p><p>' . nl2br($this->body) . '</p>')
            ->send();
    }

    /**
     * Get priority label
     * @return string
     */
    public function getPriorityLabel()
    {
        $labels = [
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta',
            'urgent' => 'Urgente',
        ];
        return isset($labels[$this->priority]) ? $labels[$this->priority] : $this->priority;
    }

    /**
     * Get category label
     * @return string
     */
    public function getCategoryLabel()
    {
        $labels = [
            'technical' => 'Problema Técnico',
            'billing' => 'Faturamento',
            'account' => 'Conta/Usuário',
            'feature' => 'Solicitação de Funcionalidade',
            'other' => 'Outro',
        ];
        return isset($labels[$this->category]) ? $labels[$this->category] : $this->category;
    }
}