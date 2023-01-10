<?php

use yii\db\Migration;

class m160507_171815_relations extends Migration
{

    public function safeUp()
    {
        $this->addForeignKey('phrase_translate_fk_phrase', '{{%phrase_translate}}', 'phrase_id', '{{%phrase}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('phrase_translate_fk_language', '{{%phrase_translate}}', 'language_id', '{{%language}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        echo "m160507_171815_relations cannot be reverted.\n";
        return false;
    }
}
