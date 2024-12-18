<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $name
 * @property string $phone
 * @property string $email
 * @property string $registration_date
 * @property string $token
 *
 * @property Cart[] $carts
 * @property Order[] $orders
 * * @property Role $role0
 * @property Subscription[] $subscriptions
 */
class User extends \yii\db\ActiveRecord
{
    const SCENARIO_LOGIN = 'login';
    const SCENARIO_REGISTER = 'register';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_LOGIN] = ['email', 'password'];
        $scenarios[self::SCENARIO_REGISTER] = ['name', 'email', 'password', 'phone'];
        return $scenarios;
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'phone', 'email',  'password'], 'required'],
            [['registration_date'], 'safe'],
            [['role'], 'integer'],
            [['name', 'phone', 'email', 'token', 'password'], 'string', 'max' => 255],
            [['role'], 'exist', 'skipOnError' => true, 'targetClass' => Role::class, 'targetAttribute' => ['role' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'phone' => 'Phone',
            'email' => 'Email',
            'registration_date' => 'Registration Date',
            'token' => 'Token',
            'password' => 'Password',
            'role' => 'Role',
        ];
    }

    /**
     * Gets query for [[Carts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCarts()
    {
        return $this->hasMany(Cart::class, ['user_id' => 'id']);
    }
    public function getRole0()
    {
        return $this->hasOne(Role::class, ['id' => 'role']);
    }
    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['user_id' => 'id']);
    }
    public function fields()
    {
        $fields = parent::fields();

        // удаляем вывод полей для безопасности
        unset($fields['password'], $fields['token']);

        $fields['ordersCount'] = function ($model) {
            return $model->getOrders()->count();
        };

        return $fields;
    }
    /**
     * Gets query for [[Subscriptions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function validatePassword($password) {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    public static function getByToken() {
        return self::findOne(['token' => str_replace('Bearer ', '', Yii::$app->request->headers->get('Authorization'))]);
    }

    public function isAdmin() {
        $roles = new Role;
        $admin_role = $roles::findOne(['name' => 'admin']);
        return $this->role === $admin_role['id'];
    }

    public function isAuthorized() {
        $token = str_replace('Bearer ', '', Yii::$app->request->headers->get('Authorization'));
        if (!$token || $token != $this->token) {
            return false;
        }
        return true;
    }
}
