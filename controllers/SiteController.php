<?php

    namespace app\controllers;

    use Yii;
    use yii\filters\AccessControl;
    use yii\web\Controller;
    use yii\web\Response;
    use yii\filters\VerbFilter;
    use app\models\Post;
    use app\components\Helper;

    class SiteController extends Controller
    {
        public function actionIndex()
        {
            $model = new Post();

            if ($model->load(Yii::$app->request->post())) {
                $model->imageFile = \yii\web\UploadedFile::getInstance($model, 'imageFile');
                $ip = Yii::$app->request->userIP;
                $model->ip = $ip;
                $model->created_at = time();

                if (!Post::canPost($ip)) {
                    $model->addError('message', 'Можете отправлять сообщение только 1 раз в 3 минуты.');
                } elseif ($model->uploadImage() && $model->save(false)) {
                    Yii::$app->mailer->compose()
                        ->setFrom(['no-reply@storyvault.test' => 'StoryVault'])
                        ->setTo($model->email)
                        ->setSubject('Управление сообщением на StoryVault')
                        ->setHtmlBody("
                            <p>Ваше сообщение успешно опубликовано!</p>
                            <p><a href='" . Yii::$app->urlManager->createAbsoluteUrl(['site/edit', 'id' => $model->id, 'token' => $model->token]) . "'>Редактировать</a></p>
                            <p><a href='" . Yii::$app->urlManager->createAbsoluteUrl(['site/delete', 'id' => $model->id, 'token' => $model->token]) . "'>Удалить</a></p>
                        ")
                        ->send();
                    $this->createAction('captcha')->getVerifyCode(true);
                    return $this->refresh();
                }else {
                    $this->createAction('captcha')->getVerifyCode(true);
                }
            }
            return $this->render('index', [
                'model' => $model,
                'posts' => Post::find()->where(['deleted_at' => null])->orderBy(['id' => SORT_DESC])->all(),
            ]);
        }
        public function actions()
        {
            return [
                'captcha' => [
                    'class' => 'yii\captcha\CaptchaAction',
                    'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                    'height' => 50,
                    'width' => 120,
                    'padding' => 5,
                    'minLength' => 4,
                    'maxLength' => 5,
                ],
            ];
        }
        public function actionDelete($id, $token)
        {
            $model = Post::findOne($id);

            if (!$model || $model->token !== $token) {
                throw new \yii\web\NotFoundHttpException('Запись не найдена.');
            }

            if ($model->softDelete()) {
                Yii::$app->session->setFlash('success', 'Сообщение успешно удалено.');
            } else {
                Yii::$app->session->setFlash('error', 'Не удалось удалить сообщение.');
            }

            return $this->redirect(['index']);
        }
    }
