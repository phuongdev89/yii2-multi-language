<?php
/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/30/16
 * Time: 3:19 PM
 */

namespace phuongdev89\language\db;
/**
 * @method string getTranslateAttribute(string $attribute, string|null $language_code = null)
 * @method boolean hasTranslateAttribute(string $attribute_translation)
 * @method array getTranslateAttributes(string $attribute = null)
 */
class ActiveRecord extends \yii\db\ActiveRecord
{

    /**
     * {@inheritDoc}
     */
    public static function find()
    {
        return new LanguageQuery(get_called_class());
    }

    /**
     * @param $condition
     *
     * @return array|null|ActiveRecord
     */
    public static function findOneTranslated($condition)
    {
        return is_array($condition) ? self::find()->where($condition)->translate()->one() : self::find()
            ->where(['id' => $condition])
            ->translate()
            ->one();
    }
}
