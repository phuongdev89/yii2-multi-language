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

	/**@var array */
	protected $data;

	/**@var string */
	protected $value = '';

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
			$this->data = LanguageHelper::getData(Yii::$app->language);
		} else {
			$this->data = LanguageHelper::getData($language_code);
		}
	}

	/**
	 * @param $name
	 * @param $arguments
	 *
	 * @return string
	 * @since 3.0.0
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
		$translate = new self($language_code);
		if ($translate->data !== null) {
			foreach ($translate->data as $category) {
				if ($category['key'] == $name && $translate->value = $category['value']) {
					return $translate->value($parameters);
				} else {
					if (isset($category['data']) && $category['data'] != null) {
						foreach ($category['data'] as $item) {
							if ($item['key'] == $name && $translate->value = $item['value']) {
								return $translate->value($parameters);
							}
						}
					}
				}
			}
		}
		return LanguageHelper::newPhrase(null, $name);
	}

	/**
	 * @param null $parameters
	 *
	 * @return string
	 * @since 3.0.0
	 */
	public function value($parameters = null) {
		if ($parameters !== null) {
			foreach ($parameters as $key => $param) {
				$this->value = str_replace('{' . ($key + 1) . '}', $param, $this->value);
			}
		}
		return trim($this->value);
	}

	/**
	 * @param $name
	 *
	 * @return string
	 * @since 3.0.0
	 */
	public function __get($name) {
		foreach ($this->data as $category) {
			if ($category['key'] == $name) {
				return trim($category['value']);
			} else {
				if (isset($category['data']) && $category['data'] != null) {
					foreach ($category['data'] as $item) {
						if ($item['key'] == $name) {
							return $item['value'];
						}
					}
				}
			}
		}
		return LanguageHelper::newPhrase(null, $name);
	}

	/**
	 * @param      $category
	 * @param      $name
	 * @param null $parameters
	 *
	 * @return string
	 * @since 3.0.0
	 */
	public static function t($category, $name, $parameters = null) {
		$language_code = Yii::$app->language;
		$translate     = new self($language_code);
		foreach ($translate->data as $data) {
			if ($data['key'] == $category) {
				if ($data['key'] == $name) {
					$translate->value = $data['value'];
				} else {
					if (isset($category['data']) && $data['data'] != null) {
						foreach ($data['data'] as $item) {
							if ($item['key'] == $name) {
								$translate->value = $item['value'];
							}
						}
					}
				}
			}
		}
		if ($translate->value !== '') {
			return $translate->value($parameters);
		} else {
			return LanguageHelper::newPhrase($category, $name);
		}
	}
}