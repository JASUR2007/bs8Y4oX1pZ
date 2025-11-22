<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Post */
/* @var $posts app\models\Post[] */

$this->title = 'StoryVault';
?>

<div class="site-index">
    <div class="container mt-5">

        <h1 class="mb-4">Оставьте своё сообщение</h1>

        <?php $form = ActiveForm::begin([
            'options' => ['enctype' => 'multipart/form-data']
        ]); ?>

        <?= $form->field($model, 'author_name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'message')->textarea(['rows' => 4]) ?>
        <?= $form->field($model, 'imageFile')->fileInput() ?>
        <?= $form->field($model, 'verifyCode')->widget(\yii\captcha\Captcha::class) ?>

        <div class="form-group">
            <?= Html::submitButton('Отправить', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

        <hr class="my-5">

        <h2 class="mb-4">Сообщения</h2>

        <?php foreach ($posts as $post): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">
                        <?= Html::encode($post->author_name) ?> (<?= \app\components\Helper::maskIp($post->ip) ?>)
                    </h5>
                    <h6 class="card-subtitle mb-2 text-muted">
                        <?= Yii::$app->formatter->asRelativeTime($post->created_at) ?>
                    </h6>
                    <p class="card-text"><?= strip_tags($post->message, '<b><i><u>') ?></p>

                    <?php if ($post->image): ?>
                        <img src="/uploads/<?= Html::encode($post->image) ?>" class="img-fluid mt-2" style="max-width: 300px;">
                    <?php endif; ?>

                    <p class="text-muted mt-2">
                        Сообщений с этого IP: <?= \app\models\Post::find()->where(['ip' => $post->ip])->count() ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>
