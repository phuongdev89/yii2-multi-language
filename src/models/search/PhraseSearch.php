<?php
/**
 * Created by Navatech.
 * @project nic
 * @author  Phuong
 * @email   phuong17889[at]gmail.com
 * @date    04/02/2016
 * @time    2:43 CH
 * @since   1.0.1
 */
namespace navatech\language\models\search;

use navatech\language\models\Language;
use navatech\language\models\Phrase;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "phrase".
 *
 * @property integer    $id
 * @property string     $name
 * @property Language[] $languages
 */
class PhraseSearch extends Phrase {

	/**
	 * @return array validation rules
	 * @see scenarios()
	 */
	public function rules() {
		$code = [];
		foreach ($this->languages as $language) {
			$code[] = $language->code;
		}
		return [
			[
				[
					'id',
				],
				'integer',
			],
			[
				array_merge([
					'id',
					'name',
				], $code),
				'safe',
			],
		];
	}

	/**
	 * Creates data provider instance with search query applied
	 *
	 * @param array $params
	 *
	 * @return ActiveDataProvider
	 * @since 1.0.0
	 * @throws \yii\base\InvalidParamException
	 */
	public function search($params) {
		$query        = Phrase::find();
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'sort'  => ['defaultOrder' => ['id' => SORT_DESC]],
		]);
		$this->load($params);
		if (!$this->validate()) {
			return $dataProvider;
		}
		foreach ($this->_dynamicData as $key => $value) {
			if ($this->$key !== '') {
				$language_id = Language::getIdByCode($key);
				if ($language_id !== 0) {
					$query->join('LEFT JOIN', 'phrase_translate as lang_' . $key, 'lang_' . $key . '.phrase_id = {{%phrase}}.id AND lang_' . $key . '.language_id = ' . $language_id);
					$query->andFilterWhere([
						'like',
						'lang_' . $key . '.value',
						$this->$key,
					]);
				}
			}
		}
		$query->andFilterWhere([
			'id' => $this->id,
		]);
		$query->andFilterWhere([
			'like',
			'name',
			$this->name,
		]);
		return $dataProvider;
	}
}