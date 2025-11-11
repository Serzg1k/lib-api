<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%book}}`.
 */
class m251111_102547_create_book_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%book}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'author' => $this->string()->notNull(),
            'description' => $this->text(),
            'published_year' => $this->integer(),
            'created_by' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey('fk_book_user','{{%book}}','created_by','{{%user}}','id','CASCADE','CASCADE');
    }
    public function safeDown()
    {
        $this->dropForeignKey('fk_book_user','{{%book}}');
        $this->dropTable('{{%book}}');
    }
}
