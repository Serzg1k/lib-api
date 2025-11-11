<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $email
 * @property string $auth_key
 * @property integer $created_at
 * @property integer $updated_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName(){ return '{{%user}}'; }

    public function rules()
    {
        return [
            [['username','email','password_hash'], 'required'],
            [['username'], 'string', 'min'=>3,'max'=>50],
            [['email'], 'email'],
            [['username','email'],'unique'],
        ];
    }

    public function fields()
    {
        return ['id','username','email','created_at','updated_at'];
    }

    // identity
    public static function findIdentity($id){ return static::findOne($id); }
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $payload = Yii::$app->jwt->validate($token);
        return $payload ? static::findOne((int)($payload->uid ?? 0)) : null;
    }
    public function getId(){ return $this->id; }
    public function getAuthKey(){ return $this->auth_key; }
    public function validateAuthKey($authKey){ return $this->auth_key === $authKey; }

    public function setPassword(string $password): void
    { $this->password_hash = Yii::$app->security->generatePasswordHash($password); }

    public function validatePassword(string $password): bool
    { return Yii::$app->security->validatePassword($password, $this->password_hash); }

    public function beforeSave($insert)
    {
        $now = time();
        if ($insert) $this->created_at = $now;
        $this->updated_at = $now;
        return parent::beforeSave($insert);
    }
}
