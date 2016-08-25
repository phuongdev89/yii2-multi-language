<?php
/**
 * Created by Navatech.
 * @project Yii2 Multi Language
 * @author  Phuong
 * @email   phuong17889[at]gmail.com
 * @date    04/02/2016
 * @time    11:03 SA
 * @since   1.0.1
 */
namespace navatech\language;

use navatech\language\helpers\LanguageHelper;
use Yii;
use yii\base\InvalidParamException;

class Translate {

	private $categories;

	/**
	 * Language constructor.
	 * @since 1.0.0
	 *
	 * @param null $language_code
	 *
	 * @throws InvalidParamException
	 */
	public function __construct($language_code = null) {
		if ($language_code === null) {
			$this->categories = LanguageHelper::getData(Yii::$app->language);
		} else {
			$this->categories = LanguageHelper::getData($language_code);
		}
	}

	/**
	 * @param $name
	 * @param $arguments
	 *
	 * @return string
	 * @since 1.0.2
	 * @throws InvalidParamException
	 */
	public static function __callStatic($name, $arguments) {
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
		$language = new self($language_code);
		if ($language->categories !== null) {
			foreach ($language->categories as $category) {
				if (array_key_exists($name, $category) && $value = $category[$name]) {
					if ($parameters !== null) {
						foreach ($parameters as $key => $param) {
							$value = str_replace('{' . ($key + 1) . '}', $param, $value);
						}
					}
					return trim($value);
				}
			}
		}
		return LanguageHelper::newPhrase($name);
	}

	/**
	 * @param $name
	 *
	 * @return string
	 * @since 2.0.0
	 */
	public function __get($name) {
		foreach ($this->categories as $category) {
			if (array_key_exists($name, $category)) {
				return $category[$name];
			}
		}
		return LanguageHelper::newPhrase($name);
	}

	public static function t($category, $name, $parameters = null) {
	}
}