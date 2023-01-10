<?php
/**
 * Created by phuongdev89.
 * @project    Yii2 Multi Language
 * @author     Phuong
 * @email      phuongdev89@gmail.com
 * @created    04/02/2016 1:49 SA
 * @updated    03/03/2016 00:40 SA
 * @since      2.0.0
 */

namespace phuongdev89\language\models;

use phuongdev89\language\db\ActiveRecord;
use phuongdev89\language\helpers\LanguageHelper;
use phuongdev89\language\Translate;
use yii\base\Exception;
use yii\base\InvalidParamException;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "language".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property string $country
 * @property int $status
 * @property PhraseTranslate[] $phraseTranslates
 */
class Language extends ActiveRecord
{

    /**
     * @return string the table name
     */
    public static function tableName()
    {
        return '{{%language}}';
    }

    /**
     * @return array validation rules
     * @see scenarios()
     */
    public function rules()
    {
        return [
            [
                [
                    'name',
                    'code',
                    'country',
                ],
                'required',
            ],
            [
                ['status'],
                'integer',
            ],
            [
                [
                    'name',
                    'code',
                    'country',
                ],
                'string',
                'max' => 255,
            ],
            [
                [
                    'id',
                    'name',
                    'code',
                    'status',
                    'country',
                ],
                'safe',
            ],
        ];
    }

    /**
     * @return ActiveQuery
     * @since 1.0.2
     */
    public function getPhraseTranslates()
    {
        return $this->hasMany(PhraseTranslate::class, ['language_id' => 'id']);
    }

    /**
     * @return array attribute labels (name => label)
     * @see generateAttributeLabel()
     */
    public function attributeLabels()
    {
        return [
            'id' => 'No.',
            'name' => Translate::name(),
            'code' => Translate::code(),
            'country' => Translate::country(),
            'status' => Translate::status(),
        ];
    }

    /**
     *
     * @param array|null $attributes
     *
     * @return array|ActiveRecord[]|Language[]
     * @since      1.0.0
     * @deprecated This function should be named "Language::getLanguages(array $attributes = [])" or call helper
     *             function "MultiLanguageHelper::getLanguages()"
     */
    public static function getAllLanguages($attributes = null)
    {
        return self::getLanguages($attributes);
    }

    /**
     * @param array $attributes
     *
     * @return array|ActiveRecord[]|Language[]
     * @since      ^2.0
     */
    public static function getLanguages($attributes = null)
    {
        if ($attributes === null) {
            $attributes = ['status' => 1];
        }
        return self::find()->where($attributes)->all();
    }

    /**
     *
     * @param $code
     *
     * @return int
     * @since 1.0.0
     */
    public static function getIdByCode($code)
    {
        $model = self::findOne(['code' => $code]);
        if ($model) {
            return $model->id;
        } else {
            return 0;
        }
    }

    /**
     * @param boolean $insert whether this method called while inserting a record.
     *                                   If false, it means the method is called while updating a record.
     * @param array $changedAttributes The old values of attributes that had changed and were saved.
     *                                   You can use this parameter to take action based on the changes made for
     *                                   example send an email when the password had changed or implement audit trail
     *                                   that tracks all the changes.
     *                                   `$changedAttributes` gives you the old attribute values while the active
     *                                   record (`$this`) has already the new, updated values.
     *
     * @throws Exception|InvalidParamException
     */
    public function afterSave($insert, $changedAttributes)
    {
        LanguageHelper::setLanguages();
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return boolean whether the record should be deleted. Defaults to true.
     * @throws Exception|InvalidParamException
     */
    public function beforeDelete()
    {
        if (LanguageHelper::removeAllData($this->code)) {
            return parent::beforeDelete();
        }
        return false;
    }
}
