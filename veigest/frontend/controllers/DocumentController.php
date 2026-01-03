<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Document;
use frontend\models\Vehicle;
use frontend\models\Driver;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class DocumentController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function init()
    {
        parent::init();
        $this->layout = 'dashboard';
    }

    public function actionIndex()
    {
        $companyId = Yii::$app->user->identity->company_id;
        $query = Document::find()->where(['company_id' => $companyId]);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'counters' => $this->buildCounters($companyId),
        ]);
    }

    public function actionCreate()
    {
        $model = new Document();
        $companyId = Yii::$app->user->identity->company_id;

        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');

            if ($model->validate()) {
                $basePath = Yii::getAlias('@frontend/web/uploads/documents/' . $companyId);
                if (!is_dir($basePath)) {
                    mkdir($basePath, 0775, true);
                }

                $fileName = uniqid('', true) . '.' . $model->file->extension;
                $fullPath = $basePath . '/' . $fileName;

                if ($model->file->saveAs($fullPath)) {
                    $model->file_path = 'uploads/documents/' . $companyId . '/' . $fileName;
                    $model->company_id = $companyId;
                    $model->save(false);
                    Yii::$app->session->setFlash('success', 'Documento carregado com sucesso.');
                    return $this->redirect(['index']);
                }

                Yii::$app->session->setFlash('error', 'Falha ao gravar o ficheiro.');
            }
        }

        return $this->render('create', [
            'model' => $model,
            'vehicles' => Vehicle::find()
                ->where(['company_id' => $companyId])
                ->select(['id', 'license_plate AS matricula'])
                ->indexBy('id')
                ->column(),
            'drivers' => Driver::find()->where(['company_id' => $companyId])->select(['name', 'id'])->indexBy('id')->column(),
        ]);
    }

    public function actionDownload($id)
    {
        $doc = $this->findOwn($id);
        $path = Yii::getAlias('@frontend/web/' . $doc->file_path);

        if (!file_exists($path)) {
            throw new NotFoundHttpException('Ficheiro não encontrado.');
        }

        return Yii::$app->response->sendFile($path, basename($path));
    }

    public function actionDelete($id)
    {
        $doc = $this->findOwn($id);
        $path = Yii::getAlias('@frontend/web/' . $doc->file_path);

        if (file_exists($path)) {
            @unlink($path);
        }

        $doc->delete();
        Yii::$app->session->setFlash('success', 'Documento eliminado.');
        return $this->redirect(['index']);
    }

    private function findOwn($id): Document
    {
        $companyId = Yii::$app->user->identity->company_id;
        $doc = Document::findOne(['id' => $id, 'company_id' => $companyId]);
        if (!$doc) {
            throw new NotFoundHttpException('Documento não encontrado.');
        }
        return $doc;
    }

    private function buildCounters(int $companyId): array
    {
        [$today, $limit] = $this->statusDates();

        return [
            'total' => Document::find()->where(['company_id' => $companyId])->count(),
            Document::STATUS_VALID => Document::find()->where(['company_id' => $companyId])->andWhere(['>', 'expires_at', $limit])->count(),
            Document::STATUS_DUE_SOON => Document::find()->where(['company_id' => $companyId])->andWhere(['between', 'expires_at', $today, $limit])->count(),
            Document::STATUS_EXPIRED => Document::find()->where(['company_id' => $companyId])->andWhere(['<', 'expires_at', $today])->count(),
        ];
    }

    private function statusDates(): array
    {
        $today = date('Y-m-d');
        $limit = date('Y-m-d', strtotime('+30 days'));
        return [$today, $limit];
    }
}
