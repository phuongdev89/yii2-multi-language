<?php
use yii\db\Migration;

class m160825_093487_add_category_column extends Migration {

	public function up() {
		$this->addColumn('{{%phrase}}', 'parent_id', \yii\db\mysql\Schema::TYPE_INTEGER . '(11) NOT NULL DEFAULT "0"');
	}

	public function down() {
		echo "m160213_041916_navatech_multi_language_insert cannot be reverted.\n";
		return false;
	}
}
