<?php
namespace app\controllers;
use app\models\User;
use Yii;
class UserController extends RestController
{
    public $modelClass = 'app\models\User';
    public function actions()
    {$actions = parent::actions();
        unset($actions['delete'], $actions['create']);

        return $actions;
    }
    public function actionOne()
    {
        $user = User::getByToken();
        if ($user && $user->isAuthorized()) {
            return $this->Response(200, ['data' => User::findOne($user->id)]);
        }
        return $this->Response(401, ['error' => ['message' => 'Вы не авторизованы']]);
    }
    public function actionCreate()
    {

        $data = Yii::$app->request->post();
        $user = new User();
        $user->scenario = User::SCENARIO_REGISTER;
        $user->load($data, '');
        if ($this->ValidationError($user)) return $this->ValidationError($user);
        $user->password = Yii::$app->getSecurity()->generatePasswordHash($user->password);
        $user->save();
        return $this->Response(201, [
            'user_id' => $user->id,
            'message' => 'Пользователь зарегистрирован'
        ]);
    }

    public function actionLogin()
    {
        $data = Yii::$app->request->post();
        $user = new User();
        $user->scenario = User::SCENARIO_LOGIN;
        $user->load($data, '');

        if ($this->ValidationError($user)) return $this->ValidationError($user);
        $user = null;
        $user = User::findOne(['email' => $data['email']]);
        
        if ($user) {
            if ($user->validatePassword($data['password'])) {

                $user->token = Yii::$app->getSecurity()->generateRandomString();
                $user->save();
                return $this->Response(200, [
                    'token' => $user->token
                ]);
            }
        }
        return $this->Response(401, [
            'message' => 'Неправильный email или пароль'
        ]);
    }



    public function actionUpgrade() {
        $user = User::getByToken();
        if (!$user || !$user->isAuthorized()) {
        return $this->Response(403, ['error' => ['message' => 'Доступ запрещен']]);
        }
        $data = Yii::$app->request->post();
        $user->load($data, '');
        if ($this->ValidationError($user)) {
        return $this->ValidationError($user);
        }
        if (isset($data['password'])) $user->password = Yii::$app->getSecurity()->generatePasswordHash($user->password);
        if ($user->save()) {
        return $this->Response(204, [
        'id' => $user->id,
        'message' => 'Данные пользователя успешно обновлены'
        ]);
        } else {
        return $this->Response(500, ['error' => ['message' => 'Не удалось сохранить данные пользователя']]);
        }
        }
        
 }
