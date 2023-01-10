<?php
/**
 * Created by phuongdev89.
 * @project Yii2 Multi Language
 * @author  Phuong
 * @email   phuongdev89@gmail.com
 * @date    04/02/2016
 * @time    1:30 CH
 * @since   1.0.1
 */

namespace phuongdev89\language\models\search;

use phuongdev89\language\models\Language;
use yii\base\InvalidParamException;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "language".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property string $country
 * @property int $status
 */
class LanguageSearch extends Language
{

    /**
     * @return array validation rules
     * @see scenarios()
     */
    public function rules()
    {
        return [
            [
                [
                    'id',
                    'status',
                ],
                'integer',
            ],
            [
                [
                    'name',
                    'code',
                    'country',
                ],
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
     * @throws InvalidParamException
     * @since 1.0.0
     */
    public function search($params)
    {
        $query = Language::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
        ]);
        $query->andFilterWhere([
            'like',
            'name',
            $this->name,
        ]);
        $query->andFilterWhere([
            'like',
            'code',
            $this->code,
        ]);
        $query->andFilterWhere([
            'like',
            'country',
            $this->country,
        ]);
        return $dataProvider;
    }
}
