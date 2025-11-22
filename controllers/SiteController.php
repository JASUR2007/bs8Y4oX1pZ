<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    protected function maskIp($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            $parts[2] = '**';
            $parts[3] = '**';
            return implode('.', $parts);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            for ($i = 4; $i < 8; $i++) $parts[$i] = '**';
            return implode(':', $parts);
        }

        return $ip;
    }
    public function actionIndex()
    {
        $model = new Post();

        if ($model->load(Yii::$app->request->post())) {

            $model->imageFile = \yii\web\UploadedFile::getInstance($model, 'imageFile');

            $ip = Yii::$app->request->userIP;

            if (!Post::canPost($ip)) {
                $model->addError('message', 'Вы можете отправлять сообщение только 1 раз в 3 минуты.');
            } else {
                $model->created_at = time();
                $model->ip = $ip;

                $model->uploadImage();

                if ($model->save(false)) {
                    Yii::$app->mailer->compose()
                        ->setTo($model->email)
                        ->setSubject('Управление сообщением на StoryVault')
                        ->setHtmlBody("
                            <p>Ваше сообщение успешно опубликовано!</p>
                            <p><a href='" . Yii::$app->urlManager->createAbsoluteUrl(['site/edit', 'id' => $model->id, 'token' => $model->token]) . "'>Редактировать</a></p>
                            <p><a href='" . Yii::$app->urlManager->createAbsoluteUrl(['site/delete', 'id' => $model->id, 'token' => $model->token]) . "'>Удалить</a></p>")
                        ->send();

                    return $this->refresh();
                }
            }
        }
        return $this->render('index', [
            'model' => $model,
            'posts' => Post::find()->where(['deleted_at' => null])->orderBy(['id' => SORT_DESC])->all(),
        ]);
    }

}
