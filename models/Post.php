<?php

    namespace app\models;

    use Yii;

    /**
     * This is the model class for table "post".
     *
     * @property int $id
     * @property string $author_name
     * @property string $email
     * @property string $message
     * @property string $ip
     * @property string|null $image
     * @property int $created_at
     * @property int|null $deleted_at
     */
    class Post extends \yii\db\ActiveRecord
    {
        public $imageFile;
        public $verifyCode;
        public static function tableName()
        {
            return 'post';
        }

        public function rules()
        {
            return [
                ['author_name', 'string', 'min' => 2, 'max' => 15],
                ['email', 'email'],
                ['message', 'string', 'min' => 5, 'max' => 1000],
                [['author_name', 'email', 'message'], 'required'],
                ['message', 'match', 'pattern' => '/\S+/'],

                ['imageFile', 'file',
                    'skipOnEmpty' => true,
                    'extensions' => ['jpg', 'png', 'webp'],
                    'maxSize' => 2 * 1024 * 1024,
                ],

                ['verifyCode', 'captcha'],
            ];
        }

        public static function canPost($identifier, $minutes = 3)
        {
            $last = self::find()
                ->where(['or', ['ip' => $identifier], ['email' => $identifier]])
                ->orderBy(['id' => SORT_DESC])
                ->one();

            if (!$last) return true;

            return (time() - $last->created_at) > ($minutes * 60);
        }

        public function uploadImage()
        {
            if ($this->imageFile) {
                list($width, $height) = getimagesize($this->imageFile->tempName);
                if ($width > 1500 || $height > 1500) {
                    $this->addError('imageFile', 'Размер изображения превышает 1500px');
                    return false;
                }
                $filename = uniqid('', true) . '.' . $this->imageFile->extension;

                $uploadPath = Yii::getAlias('@app/web/uploads/');
                if (!is_dir($uploadPath)) {
                    if (!mkdir($uploadPath, 0777, true)
                        && !is_dir(
                            $uploadPath
                        )
                    ) {
                        throw new \RuntimeException(
                            sprintf(
                                'Directory "%s" was not created',
                                $uploadPath
                            )
                        );
                    }
                }

                $this->imageFile->saveAs($uploadPath . $filename);
                $this->image = $filename;
            }
            return true;
        }
        public function softDelete()
        {
            $this->deleted_at = time();
            return $this->save(false);
        }
        public function beforeSave($insert)
        {
            if ($insert) {
                $this->token = Yii::$app->security->generateRandomString(32);
            }
            return parent::beforeSave($insert);
        }
    }

