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


        /**
         * {@inheritdoc}
         */
        public static function tableName()
        {
            return 'post';
        }

        /**
         * {@inheritdoc}
         */
        public function rules()
        {
            return [
                ['author_name', 'string', 'min' => 2, 'max' => 15],
                ['email', 'email'],
                ['message', 'string', 'min' => 5, 'max' => 1000],
                [['author_name', 'email', 'message'], 'required'],
                ['message', 'match', 'pattern' => '/\S+/'],
            ];
        }

        /**
         * {@inheritdoc}
         */
        public static function canPost($identifier, $minutes = 3)
        {
            $last = self::find()
                ->where(['or', ['ip' => $identifier], ['email' => $identifier]])
                ->orderBy(['id' => SORT_DESC])
                ->one();

            if (!$last) return true;

            return (time() - $last->created_at) > ($minutes * 60);
        }

    }
