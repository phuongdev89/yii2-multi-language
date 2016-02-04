<?php
/**
 * Created by Navatech.
 * @project nic
 * @author  Phuong
 * @email   phuong17889[at]gmail.com
 * @date    04/02/2016
 * @time    2:43 CH
 * @version 1.0.0
 */
namespace navatech\language\models;

use yii\data\ActiveDataProvider;

class PhraseSearch extends Phrase {

	/**
	 * @inheritdoc
	 */
	public function rules() {
		$code = [];
		foreach($this->languages as $language) {
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
	 */
	public function search($params) {
		$query        = Phrase::find();
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'sort'  => ['defaultOrder' => ['id' => SORT_DESC]],
		]);
		$this->load($params);
		if(!$this->validate()) {
			return $dataProvider;
		}
		foreach($this->_dynamicData as $key => $value) {
			if($this->$key != '') {
				$language_id = Language::getIdByCode($key);
				if($language_id != 0) {
					$query->join('left', 'phrase_meta as lang_' . $key, 'lang_' . $key . '.phrase_id = t.phrase_id AND lang_' . $key . '.language_id = ' . $language_id);
					$query->andFilterWhere([
						'like',
						'lang_' . $key . '.value',
						$this->$key,
					]);
				}
			}
		}
		$query->andFilterWhere([
			'id'   => $this->id,
			'name' => $this->name,
		]);
		return $dataProvider;
	}
}