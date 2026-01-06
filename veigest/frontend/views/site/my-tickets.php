<?php

use yii\helpers\Html;
use yii\helpers\Url;
use common\models\SupportTicket;

/** @var yii\web\View $this */
/** @var common\models\SupportTicket[] $tickets */

$this->title = 'Meus Tickets';
$this->params['breadcrumbs'][] = $this->title;

$priorityColors = [
    'low' => 'bg-gray-100 text-gray-800',
    'medium' => 'bg-blue-100 text-blue-800',
    'high' => 'bg-orange-100 text-orange-800',
    'urgent' => 'bg-red-100 text-red-800',
];

$statusColors = [
    'open' => 'bg-green-100 text-green-800',
    'in_progress' => 'bg-blue-100 text-blue-800',
    'waiting_response' => 'bg-yellow-100 text-yellow-800',
    'resolved' => 'bg-purple-100 text-purple-800',
    'closed' => 'bg-gray-100 text-gray-800',
];
?>

<div class="container-fluid px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            <i class="fas fa-ticket-alt mr-2 text-primary"></i>Meus Tickets
        </h1>
        <?= Html::a('<i class="fas fa-plus mr-2"></i>Novo Ticket', ['ticket'], [
            'class' => 'bg-primary text-white px-4 py-2 rounded-lg hover:bg-opacity-90 transition'
        ]) ?>
    </div>

    <?php if (empty($tickets)): ?>
    <div class="bg-white rounded-lg shadow-md p-8 text-center">
        <i class="fas fa-ticket-alt text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">Nenhum ticket encontrado</h3>
        <p class="text-gray-500 mb-4">Ainda não criou nenhum ticket de suporte.</p>
        <?= Html::a('<i class="fas fa-plus mr-2"></i>Criar Primeiro Ticket', ['ticket'], [
            'class' => 'inline-block bg-primary text-white px-6 py-3 rounded-lg hover:bg-opacity-90 transition'
        ]) ?>
    </div>
    <?php else: ?>
    
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <?php
        $stats = [
            'open' => ['label' => 'Abertos', 'icon' => 'envelope-open', 'color' => 'green'],
            'in_progress' => ['label' => 'Em Progresso', 'icon' => 'spinner', 'color' => 'blue'],
            'waiting_response' => ['label' => 'Aguardando', 'icon' => 'hourglass-half', 'color' => 'yellow'],
            'resolved' => ['label' => 'Resolvidos', 'icon' => 'check-circle', 'color' => 'purple'],
            'closed' => ['label' => 'Fechados', 'icon' => 'times-circle', 'color' => 'gray'],
        ];
        $counts = [];
        foreach ($tickets as $t) {
            $counts[$t->status] = ($counts[$t->status] ?? 0) + 1;
        }
        foreach ($stats as $status => $info):
        ?>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <i class="fas fa-<?= $info['icon'] ?> text-2xl text-<?= $info['color'] ?>-500 mb-2"></i>
            <div class="text-2xl font-bold text-gray-800"><?= $counts[$status] ?? 0 ?></div>
            <div class="text-sm text-gray-500"><?= $info['label'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Tickets List -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assunto</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioridade</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($tickets as $ticket): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="font-semibold text-gray-900">#<?= $ticket->id ?></span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-sm font-medium text-gray-900"><?= Html::encode($ticket->subject) ?></div>
                            <div class="text-sm text-gray-500 truncate max-w-xs"><?= Html::encode(mb_substr($ticket->body, 0, 50)) ?>...</div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= $ticket->getCategoryLabel() ?>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $priorityColors[$ticket->priority] ?? 'bg-gray-100 text-gray-800' ?>">
                                <?= $ticket->getPriorityLabel() ?>
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-800' ?>">
                                <?= $ticket->getStatusLabel() ?>
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= Yii::$app->formatter->asDate($ticket->created_at, 'php:d/m/Y') ?>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <?= Html::a('<i class="fas fa-eye"></i>', ['ticket-view', 'id' => $ticket->id], [
                                'class' => 'text-primary hover:text-blue-700',
                                'title' => 'Ver Detalhes'
                            ]) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.bg-purple-100 { background-color: #f3e8ff; }
.text-purple-800 { color: #5b21b6; }
.text-purple-500 { color: #8b5cf6; }
</style>
