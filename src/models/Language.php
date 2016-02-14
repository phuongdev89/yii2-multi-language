<?php
/**
 * Created by Navatech.
 * @project Yii2 Multi Language
 * @author  Phuong
 * @email   phuong17889[at]gmail.com
 * @date    04/02/2016
 * @time    1:49 SA
 * @version 1.0.1
 */
namespace navatech\language\models;

use navatech\language\MultiLanguage;
use navatech\language\Translate;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "language".
 *
 * @property integer $id
 * @property string  $name
 * @property string  $code
 * @property string  $country
 * @property int     $status
 */
class Language extends ActiveRecord {

	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return '{{%language}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
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
	 * @return PhraseMeta[]
	 * @since 1.0.2
	 */
	public function getPhraseMeta() {
		return $this->hasMany(PhraseMeta::className(), ['language_id' => 'id'])->all();
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id'      => 'No.',
			'name'    => Translate::name(),
			'code'    => Translate::code(),
			'country' => Translate::country(),
			'status'  => Translate::status(),
		];
	}

	/**
	 *
	 * @param array $attributes
	 *
	 * @return array|\yii\db\ActiveRecord[]|Language[]
	 * @since 1.0.0
	 */
	public static function getAllLanguages($attributes = []) {
		if ($attributes == null) {
			$attributes['status'] = 1;
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
	public static function getIdByCode($code) {
		$model = self::findOne(['code' => $code]);
		if ($model) {
			return $model->id;
		} else {
			return 0;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function afterSave($insert, $changedAttributes) {
		parent::afterSave($insert, $changedAttributes);
		MultiLanguage::setLanguages();
	}

	/**
	 * @inheritdoc
	 */
	public function beforeDelete() {
		if (MultiLanguage::removeAllData($this->code)) {
			return parent::beforeDelete();
		}
		return false;
	}
}