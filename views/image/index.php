<?php

use app\models\Images;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Images';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="images-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Images', ['create'], ['class' => 'btn btn-success']) ?>
    </p>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'preview',
                'format' => 'raw',
                'value' => static function ($model) {
                    return Html::img($model->generatePreviewDataUri());
                },
            ],
            'filename',
            'uploaded_at',
            [
                'class' => ActionColumn::class,
                'urlCreator' => static function ($action, Images $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                },
                'template' => '{imageOriginal}<br>{download}',
                'buttons' => [
                    'imageOriginal' => static function ($url, $model, $key) {
                        return Html::a( "Original image", "/uploads/" . $model->filename, ['target' => '_blank']);
                    },
                    'download' => static function ($url, $model, $key) {
                        return Html::a( "download zip", "/image/download?filename=" . $model->filename, ['target' => '_blank']);
                    },
                ],
            ],
        ],
    ]); ?>


</div>
