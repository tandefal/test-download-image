<?php

namespace app\controllers;

use app\models\Images;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;
use ZipArchive;

/**
 * ImageController implements the CRUD actions for Images model.
 */
class ImageController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Images models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Images::find(),
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Creates a new Images model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Images();

        if ($this->request->isPost) {
            $model->files = UploadedFile::getInstances($model, 'files');
            if ($model->files && $model->upload()) {
                Yii::$app->session->setFlash('success', 'Images uploaded successfully.');
                return $this->redirect(['index']);
            }

            if ($model->files) {
                Yii::$app->session->setFlash('error', 'Failed to upload files.');
            } else {
                Yii::$app->session->setFlash('warning', 'Please upload at least one file.');
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionDownload($filename)
    {
        $filePath = "uploads/{$filename}";

        if (file_exists($filePath)) {
            $zipFile = "{$filename}.zip";

            $zip = new ZipArchive();
            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $zip->addFile($filePath, basename($filePath));
                $zip->close();
            }
            Yii::$app->response->sendFile($zipFile);
            unlink($zipFile);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Finds the Images model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Images the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Images::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
