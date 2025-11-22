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
        public $imageFile; // здесь свойство для загрузки

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

                // правила для загрузки файла
                ['imageFile', 'file',
                    'skipOnEmpty' => true,
                    'extensions' => ['jpg', 'png', 'webp'],
                    'maxSize' => 2 * 1024 * 1024, // 2MB
                ],
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

        // Метод для сохранения изображения при сохранении поста
        public function uploadImage()
        {
            if ($this->imageFile) {
                $filename = uniqid('', true) . '.' . $this->imageFile->extension;
                $this->imageFile->saveAs("uploads/$filename");
                $this->image = $filename;
            }
        }
        public function softDelete()
        {
            $this->deleted_at = time();
            return $this->save(false);
        }

    }

