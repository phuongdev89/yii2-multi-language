<?php

use phuongdev89\language\helpers\LanguageHelper;
use yii\db\Migration;

class m160213_041916_phuongdev89_multi_language_insert extends Migration
{

    public function up()
    {
        $this->insert('{{%language}}', [
            'id' => 1,
            'name' => 'Việt Nam',
            'code' => 'vi',
            'country' => 'vn',
            'status' => 1,
        ]);
        $this->insert('{{%language}}', [
            'id' => 2,
            'name' => 'United States',
            'code' => 'en',
            'country' => 'us',
            'status' => 1,
        ]);
        $this->insert('{{%phrase}}', [
            'id' => 1,
            'name' => 'language',
        ]);
        $this->insert('{{%phrase}}', [
            'id' => 2,
            'name' => 'phrase',
        ]);
        $this->insert('{{%phrase}}', [
            'id' => 3,
            'name' => 'name',
        ]);
        $this->insert('{{%phrase}}', [
            'id' => 4,
            'name' => 'code',
        ]);
        $this->insert('{{%phrase}}', [
            'id' => 5,
            'name' => 'country',
        ]);
        $this->insert('{{%phrase}}', [
            'id' => 6,
            'name' => 'status',
        ]);
        $this->insert('{{%phrase}}', [
            'id' => 7,
            'name' => 'translate',
        ]);
        $this->insert('{{%phrase_translate}}', [
            'phrase_id' => 1,
            'language_id' => 1,
            'value' => 'Ngôn ngữ',
        ]);
        $this->insert('{{%phrase_translate}}', [
            'phrase_id' => 1,
            'language_id' => 2,
            'value' => 'Language',
        ]);
        $this->insert('{{%phrase_translate}}', [
            'phrase_id' => 2,
            'language_id' => 1,
            'value' => 'Từ ngữ',
        ]);
        $this->insert('{{%phrase_translate}}', [
            'phrase_id' => 2,
            'language_id' => 2,
            'value' => 'Phrase',
        ]);
        $this->insert('{{%phrase_translate}}', [
            'phrase_id' => 3,
            'language_id' => 1,
            'value' => 'Tên',
        ]);
        $this->insert('{{%phrase_translate}}', [
            'phrase_id' => 3,
            'language_id' => 2,
            'value' => 'Name',
        ]);
        $this->insert('{{%phrase_translate}}', [
            'phrase_id' => 4,
            'language_id' => 1,
            'value' => 'Mã',
        ]);
        $this->insert('{{%phrase_translate}}', [
            'phrase_id' => 4,
            'language_id' => 2,
            'value' => 'Code',
        ]);
        $this->insert('{{%phrase_translate}}', [
            'phrase_id' => 5,
            'language_id' => 1,
            'value' => 'Quốc gia',
        ]);
        $this->insert('{{%phrase_translate}}', [
            'phrase_id' => 5,
            'language_id' => 2,
            'value' => 'Country',
        ]);
        $this->insert('{{%phrase_translate}}', [
            'phrase_id' => 6,
            'language_id' => 1,
            'value' => 'Trạng thái',
        ]);
        $this->insert('{{%phrase_translate}}', [
            'phrase_id' => 6,
            'language_id' => 2,
            'value' => 'Status',
        ]);
        $this->insert('{{%phrase_translate}}', [
            'phrase_id' => 7,
            'language_id' => 1,
            'value' => 'Dịch',
        ]);
        $this->insert('{{%phrase_translate}}', [
            'phrase_id' => 7,
            'language_id' => 2,
            'value' => 'Translate',
        ]);
        LanguageHelper::setLanguages();
        LanguageHelper::setAllData();
    }

    public function down()
    {
        echo "m160213_041916_phuongdev89_multi_language_insert cannot be reverted.\n";
        return false;
    }
}
