<?php
namespace common\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Book extends ActiveRecord
{
    public static function tableName(){ return '{{%book}}'; }

    public function behaviors(){ return [TimestampBehavior::class]; }

    public function rules()
    {
        return [
            [['title','author'], 'required'],
            [['title','author'], 'string', 'max'=>255],
            [['description'], 'string'],
            [['published_year','created_by'], 'integer'],
            ['published_year', 'integer', 'min'=>0, 'max'=>2100],
        ];
    }

    public function fields()
    {
        return ['id','title','author','description','published_year','created_by','created_at','updated_at'];
    }
}
