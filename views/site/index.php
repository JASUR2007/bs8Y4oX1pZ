<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Post */
/* @var $posts app\models\Post[] */

$this->title = 'StoryVault';
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

<div class="site-index py-5 bg-light">
    <div class="container">
        <h1 class="text-center mb-5">StoryVault — Оставьте своё сообщение</h1>

        <div class="row gx-4 gy-4">
            <div class="col-lg-7">
                <h2 class="mb-4">Сообщения</h2>
                <?php foreach ($posts as $post): ?>
                    <div class="card mb-3 shadow-sm">
                        <?php if ($post->image): ?>
                            <img src="/uploads/<?= Html::encode($post->image) ?>" class="card-img-top" alt="Изображение от <?= Html::encode($post->author_name) ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title mb-1">
                                <?= Html::encode($post->author_name) ?>
                                <small class="text-muted">(<?= \app\components\Helper::maskIp($post->ip) ?>)</small>
                            </h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?= Yii::$app->formatter->asRelativeTime($post->created_at) ?></h6>
                            <p class="card-text"><?= strip_tags($post->message, '<b><i><s>') ?></p>
                            <p class="text-muted small mb-0">Сообщений с этого IP: <?= \app\models\Post::find()->where(['ip' => $post->ip])->count() ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Оставить сообщение</h3>
                        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data', 'id' => 'post-form']]); ?>

                        <?= $form->field($model, 'author_name')->textInput(['maxlength' => true, 'class' => 'form-control']) ?>
                        <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'class' => 'form-control']) ?>
                        <?= $form->field($model, 'message')->textarea(['rows' => 4, 'class' => 'form-control']) ?>
                        <?= $form->field($model, 'imageFile')->fileInput(['class' => 'form-control']) ?>
                        <?= $form->field($model, 'verifyCode')->widget(\yii\captcha\Captcha::class, [
                            'captchaAction' => 'site/captcha',
                            'imageOptions' => [
                                'id' => 'captcha-image',
                                'class' => 'mb-2 rounded border',
                                'title' => 'Обновить капчу'
                            ],
                            'options' => ['class' => 'form-control mb-3'],
                            'template' => '{image} {input}'
                        ]) ?>

                        <div class="text-end">
                            <?= Html::submitButton('Отправить', ['class' => 'btn btn-primary']) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('post-form').addEventListener('submit', function(e) {
        e.preventDefault();

        let form = this;
        let author = form.querySelector('#post-author_name').value.trim();
        let email = form.querySelector('#post-email').value.trim();
        let message = form.querySelector('#post-message').value.trim();
        let fileInput = form.querySelector('#post-imagefile');
        let captcha = form.querySelector('#post-verifycode').value.trim();

        let errors = [];
        if (author.length < 2 || author.length > 15) {
            errors.push("Имя автора должно быть от 2 до 15 символов.");
        }
        let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errors.push("Введите корректный email.");
        }
        if (message.length < 5 || message.length > 1000 || message.trim().length === 0) {
            errors.push("Сообщение должно быть от 5 до 1000 символов и не состоять только из пробелов.");
        }
        if (!captcha) {
            errors.push("Введите код с картинки (капча).");
        }
        function submitForm() {
            document.getElementById('captcha-image').src = '<?= \yii\helpers\Url::to(['site/captcha']) ?>' + '?r=' + Math.random();
            form.submit();
        }

        if (fileInput.files.length > 0) {
            let file = fileInput.files[0];
            let allowedTypes = ['image/jpeg','image/png','image/webp'];
            if (!allowedTypes.includes(file.type)) {
                errors.push("Допускаются только изображения jpg, png, webp.");
            }
            if (file.size > 2 * 1024 * 1024) {
                errors.push("Размер изображения не более 2 Мб.");
            }

            if (errors.length > 0) {
                alert(errors.join("\n"));
                return false;
            }

            let img = new Image();
            img.onload = function() {
                if (img.width > 1500 || img.height > 1500) {
                    alert("Размер сторон изображения не должен превышать 1500px.");
                } else {
                    submitForm();
                }
            };
            img.src = URL.createObjectURL(file);
        } else {
            if (errors.length > 0) {
                alert(errors.join("\n"));
                return false;
            }
            submitForm();
        }
    });
</script>
