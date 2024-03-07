<?php

namespace app\controllers;

use app\models\Images;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

class ApiController extends Controller
{
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => \yii\filters\ContentNegotiator::class,
                'formats' => [
                    'application/json' => \yii\web\Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return Images::find()->all();
    }

    public function actionView($id)
    {
        $image = Images::findOne($id);
        if ($image === null) {
            throw new NotFoundHttpException('The requested file does not exist.');
        }
        return $image;
    }
}