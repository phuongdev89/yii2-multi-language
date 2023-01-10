<?php
/**
 * Created by phuongdev89.
 * @project Yii2 Multi Language
 * @author  Phuong
 * @email   phuongdev89@gmail.com
 * @date    04/02/2016
 * @time    11:03 SA
 * @since   1.0.1
 */

namespace phuongdev89\language;

use phuongdev89\language\helpers\LanguageHelper;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\InvalidParamException;

class Translate
{

    private $values;

    /**
     * Language constructor.
     * @param null $language_code
     *
     * @throws ErrorException
     * @throws Exception
     * @since 1.0.0
     */
    public function __construct($language_code = null)
    {
        if ($language_code === null) {
            $this->values = LanguageHelper::getData(Yii::$app->language);
        } else {
            $this->values = LanguageHelper::getData($language_code);
        }
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return string
     * @throws ErrorException
     * @throws Exception
     * @since 1.0.2
     */
    public static function __callStatic($name, $arguments)
    {
        $parameters = null;
        if (array_key_exists(0, $arguments)) {
            if (!is_array($arguments[0])) {
                $parameters = [$arguments[0]];
            } else {
                $parameters = $arguments[0];
            }
        }
        $language_code = Yii::$app->language;
        if (array_key_exists(1, $arguments) && is_string($arguments[1]) && strlen($arguments[1]) === 2) {
            $language_code = $arguments[1];
        }
        $language = new Translate($language_code);
        if ($language->values !== null && array_key_exists($name, $language->values) && $value = $language->values[$name]) {
            if ($parameters !== null) {
                foreach ($parameters as $key => $param) {
                    $value = str_replace('{' . ($key + 1) . '}', $param, $value);
                }
            }
            return trim($value);
        } else {
            return LanguageHelper::newPhrase($name);
        }
    }

    /**
     * @param $name
     *
     * @return string
     * @since 2.0.0
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->values)) {
            return $this->values[$name];
        } else {
            return LanguageHelper::newPhrase($name);
        }
    }
}
