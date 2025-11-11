<?php
namespace api\controllers;

use Yii;
use yii\rest\Controller;
use yii\filters\VerbFilter;
use common\models\User;
use common\components\JwtHttpBearerAuth;

class UserController extends Controller
{
    public function behaviors()
    {
        $b = parent::behaviors();
        $b['verbs'] = ['class'=>VerbFilter::class,'actions'=>[
            'register'=>['POST'], 'view'=>['GET'],
        ]];
        $b['authenticator'] = ['class'=>JwtHttpBearerAuth::class,'only'=>['view']];
        return $b;
    }

    public function actionRegister()
    {
        $body = Yii::$app->request->bodyParams;
        $user = new User();
        $user->username = $body['login'] ?? $body['username'] ?? null;
        $user->email = $body['email'] ?? null;
        $password = $body['password'] ?? null;
        if (!$password) { Yii::$app->response->statusCode=400; return ['error'=>'Password is required']; }
        $user->setPassword($password);
        $user->auth_key = Yii::$app->security->generateRandomString();
        if ($user->validate() && $user->save()) return $user;
        Yii::$app->response->statusCode = 422; return $user->getErrors();
    }

    public function actionView($id)
    {
        return [123];
        $u = User::findOne((int)$id);
        if (!$u){ Yii::$app->response->statusCode=404; return ['error'=>'User not found']; }
        return $u;
    }
}
