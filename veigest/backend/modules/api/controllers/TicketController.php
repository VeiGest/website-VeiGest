<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use backend\modules\api\models\Ticket;
use backend\modules\api\models\Route;

/**
 * Ticket API Controller
 * 
 * Fornece operações CRUD para bilhetes com multi-tenancy
 * Implementa filtragem automática por company_id
 * 
 * @author VeiGest Team
 */
class TicketController extends BaseApiController
{
    public $modelClass = 'backend\modules\api\models\Ticket';

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['view']);
        unset($actions['delete']);
        return $actions;
    }

    /**
     * List tickets with filters
     * GET /api/tickets
     */
    public function actionIndex()
    {
        $companyId = $this->getCompanyId();
        
        $query = Ticket::find()->where(['company_id' => $companyId]);

        // Filter by route
        $routeId = Yii::$app->request->get('route_id');
        if ($routeId) {
            $query->andWhere(['route_id' => $routeId]);
        }

        // Filter by status
        $status = Yii::$app->request->get('status');
        if ($status) {
            $query->andWhere(['status' => $status]);
        }

        // Search by passenger name or phone
        $search = Yii::$app->request->get('search');
        if ($search) {
            $query->andWhere(['OR',
                ['LIKE', 'passenger_name', $search],
                ['LIKE', 'passenger_phone', $search],
            ]);
        }

        // Order by
        $sort = Yii::$app->request->get('sort', '-created_at');
        if (strpos($sort, '-') === 0) {
            $query->orderBy([substr($sort, 1) => SORT_DESC]);
        } else {
            $query->orderBy([$sort => SORT_ASC]);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 20),
            ],
        ]);
    }

    /**
     * View a specific ticket
     * GET /api/tickets/{id}
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $model;
    }

    /**
     * Create a new ticket
     * POST /api/tickets
     */
    public function actionCreate()
    {
        $model = new Ticket();
        $model->company_id = $this->getCompanyId();
        $model->load(Yii::$app->request->bodyParams, '');

        // Validate route belongs to company
        if ($model->route_id) {
            $route = Route::findOne(['id' => $model->route_id, 'company_id' => $model->company_id]);
            if (!$route) {
                return $this->errorResponse('Rota não encontrada ou não pertence à sua empresa', 400);
            }
        }

        if ($model->save()) {
            Yii::$app->response->setStatusCode(201);
            return $this->successResponse($model, 'Bilhete criado com sucesso');
        }

        return $this->errorResponse('Erro ao criar bilhete', 400, $model->errors);
    }

    /**
     * Update a ticket
     * PUT /api/tickets/{id}
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->load(Yii::$app->request->bodyParams, '');

        if ($model->save()) {
            return $this->successResponse($model, 'Bilhete atualizado com sucesso');
        }

        return $this->errorResponse('Erro ao atualizar bilhete', 400, $model->errors);
    }

    /**
     * Delete a ticket
     * DELETE /api/tickets/{id}
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->delete()) {
            return $this->successResponse(null, 'Bilhete excluído com sucesso');
        }

        return $this->errorResponse('Erro ao excluir bilhete', 500);
    }

    /**
     * Cancel a ticket
     * POST /api/tickets/{id}/cancel
     */
    public function actionCancel($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== Ticket::STATUS_ACTIVE) {
            return $this->errorResponse('Este bilhete já foi processado', 400);
        }

        if ($model->cancel()) {
            return $this->successResponse($model, 'Bilhete cancelado com sucesso');
        }

        return $this->errorResponse('Erro ao cancelar bilhete', 500);
    }

    /**
     * Complete a ticket
     * POST /api/tickets/{id}/complete
     */
    public function actionComplete($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== Ticket::STATUS_ACTIVE) {
            return $this->errorResponse('Este bilhete já foi processado', 400);
        }

        if ($model->complete()) {
            return $this->successResponse($model, 'Bilhete concluído com sucesso');
        }

        return $this->errorResponse('Erro ao concluir bilhete', 500);
    }

    /**
     * Get tickets by route
     * GET /api/tickets/by-route/{route_id}
     */
    public function actionByRoute($route_id)
    {
        $companyId = $this->getCompanyId();

        // Verify route belongs to company
        $route = Route::findOne(['id' => $route_id, 'company_id' => $companyId]);
        if (!$route) {
            throw new NotFoundHttpException('Rota não encontrada.');
        }

        $query = Ticket::find()
            ->where(['company_id' => $companyId, 'route_id' => $route_id])
            ->orderBy(['created_at' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 50),
            ],
        ]);
    }

    /**
     * Get tickets by status
     * GET /api/tickets/by-status/{status}
     */
    public function actionByStatus($status)
    {
        $companyId = $this->getCompanyId();

        $query = Ticket::find()
            ->where(['company_id' => $companyId, 'status' => $status])
            ->orderBy(['created_at' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 20),
            ],
        ]);
    }

    /**
     * Get ticket statistics
     * GET /api/tickets/stats
     */
    public function actionStats()
    {
        $companyId = $this->getCompanyId();

        $totalTickets = Ticket::find()->where(['company_id' => $companyId])->count();
        $activeTickets = Ticket::find()->where(['company_id' => $companyId, 'status' => Ticket::STATUS_ACTIVE])->count();
        $completedTickets = Ticket::find()->where(['company_id' => $companyId, 'status' => Ticket::STATUS_COMPLETED])->count();
        $cancelledTickets = Ticket::find()->where(['company_id' => $companyId, 'status' => Ticket::STATUS_CANCELLED])->count();

        // By status
        $byStatus = Yii::$app->db->createCommand("
            SELECT status, COUNT(*) as count
            FROM tickets
            WHERE company_id = :company_id
            GROUP BY status
        ")->bindValue(':company_id', $companyId)->queryAll();

        // Tickets per day (last 30 days)
        $perDay = Yii::$app->db->createCommand("
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM tickets
            WHERE company_id = :company_id
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ")->bindValue(':company_id', $companyId)->queryAll();

        // Top routes by tickets
        $topRoutes = Yii::$app->db->createCommand("
            SELECT r.start_location, r.end_location, COUNT(t.id) as ticket_count
            FROM tickets t
            INNER JOIN routes r ON t.route_id = r.id
            WHERE t.company_id = :company_id
            GROUP BY t.route_id
            ORDER BY ticket_count DESC
            LIMIT 10
        ")->bindValue(':company_id', $companyId)->queryAll();

        // Cancellation rate
        $cancellationRate = $totalTickets > 0 ? round(($cancelledTickets / $totalTickets) * 100, 2) : 0;

        // Today's tickets
        $todayTickets = Ticket::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['>=', 'created_at', date('Y-m-d 00:00:00')])
            ->count();

        // This week's tickets
        $weekTickets = Ticket::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['>=', 'created_at', date('Y-m-d', strtotime('monday this week'))])
            ->count();

        return $this->successResponse([
            'total_tickets' => (int) $totalTickets,
            'active_tickets' => (int) $activeTickets,
            'completed_tickets' => (int) $completedTickets,
            'cancelled_tickets' => (int) $cancelledTickets,
            'cancellation_rate_percent' => $cancellationRate,
            'today_tickets' => (int) $todayTickets,
            'this_week_tickets' => (int) $weekTickets,
            'by_status' => $byStatus,
            'per_day_last_30' => $perDay,
            'top_routes' => $topRoutes,
        ]);
    }

    /**
     * Get status options
     * GET /api/tickets/statuses
     */
    public function actionStatuses()
    {
        return $this->successResponse(Ticket::getStatusOptions());
    }

    /**
     * Bulk cancel tickets
     * POST /api/tickets/bulk-cancel
     */
    public function actionBulkCancel()
    {
        $ids = Yii::$app->request->getBodyParam('ids', []);
        
        if (empty($ids)) {
            return $this->errorResponse('Nenhum ID fornecido', 400);
        }

        $companyId = $this->getCompanyId();
        $cancelled = 0;

        foreach ($ids as $id) {
            $model = Ticket::findOne(['id' => $id, 'company_id' => $companyId, 'status' => Ticket::STATUS_ACTIVE]);
            if ($model && $model->cancel()) {
                $cancelled++;
            }
        }

        return $this->successResponse([
            'cancelled_count' => $cancelled,
            'total_requested' => count($ids),
        ], "Cancelados $cancelled de " . count($ids) . " bilhetes");
    }

    /**
     * Bulk complete tickets
     * POST /api/tickets/bulk-complete
     */
    public function actionBulkComplete()
    {
        $ids = Yii::$app->request->getBodyParam('ids', []);
        
        if (empty($ids)) {
            return $this->errorResponse('Nenhum ID fornecido', 400);
        }

        $companyId = $this->getCompanyId();
        $completed = 0;

        foreach ($ids as $id) {
            $model = Ticket::findOne(['id' => $id, 'company_id' => $companyId, 'status' => Ticket::STATUS_ACTIVE]);
            if ($model && $model->complete()) {
                $completed++;
            }
        }

        return $this->successResponse([
            'completed_count' => $completed,
            'total_requested' => count($ids),
        ], "Concluídos $completed de " . count($ids) . " bilhetes");
    }

    /**
     * Find model with company check
     */
    protected function findModel($id)
    {
        $companyId = $this->getCompanyId();
        
        $model = Ticket::find()
            ->where(['id' => $id, 'company_id' => $companyId])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Bilhete não encontrado.');
        }

        return $model;
    }
}
