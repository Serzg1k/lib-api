<?php
namespace api\controllers;

use Yii;
use yii\rest\Controller;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use common\models\Book;
use common\components\JwtHttpBearerAuth;
use yii\rest\Serializer;

class BookController extends Controller
{

    public $serializer = [
        'class' => Serializer::class,
        'collectionEnvelope' => 'items',
    ];
    public function behaviors()
    {
        $b = parent::behaviors();
        $b['verbs'] = ['class'=>VerbFilter::class,'actions'=>[
            'index'=>['GET'],'view'=>['GET'],'create'=>['POST'],'update'=>['PUT'],'delete'=>['DELETE'],
        ]];
        $b['authenticator'] = ['class'=>JwtHttpBearerAuth::class,'only'=>['create','update','delete']];
        return $b;
    }

    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => Book::find()->orderBy(['id'=>SORT_DESC]),
            'pagination' => ['pageSizeLimit' => [1,100]],
        ]);
    }

    public function actionView($id)
    {
        $m = Book::findOne((int)$id);
        if (!$m){
            Yii::$app->response->statusCode=404;
            return ['error'=>'Book not found'];
        }
        return $m;
    }

    public function actionCreate()
    {
        $m = new Book();
        $m->load(Yii::$app->request->bodyParams, '');
        $m->created_by = Yii::$app->user->id;
        if ($m->validate() && $m->save()){
            return $m;
        }
        Yii::$app->response->statusCode=422; return $m->getErrors();
    }

    public function actionUpdate($id)
    {
        $m = Book::findOne((int)$id);
        if (!$m){
            Yii::$app->response->statusCode=404; return ['error'=>'Book not found'];
        }
        if ($m->created_by !== Yii::$app->user->id){
            Yii::$app->response->statusCode=403;
            return ['error'=>'Forbidden'];
        }
        $m->load(Yii::$app->request->bodyParams, '');
        if ($m->validate() && $m->save()) {
            return $m;
        }
        Yii::$app->response->statusCode=422; return $m->getErrors();
    }

    public function actionDelete($id)
    {
        $m = Book::findOne((int)$id);
        if (!$m){
            Yii::$app->response->statusCode=404; return ['error'=>'Book not found'];
        }
        if ($m->created_by !== Yii::$app->user->id){
            Yii::$app->response->statusCode=403;
            return ['error'=>'Forbidden'];
        }
        $m->delete();
        return ['status'=>'ok'];
    }
}
