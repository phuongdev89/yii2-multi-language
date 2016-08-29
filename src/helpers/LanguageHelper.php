<?php
/**
 * Created by Navatech.
 * @project yii2-basic
 * @author  Phuong
 * @email   phuong17889[at]gmail.com
 * @date    5/13/2016
 * @time    12:36 AM
 */
namespace navatech\language\helpers;

use navatech\language\models\Language;
use navatech\language\models\Phrase;
use navatech\language\models\PhraseTranslate;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\db\ActiveRecord;
use yii\helpers\Json;

class LanguageHelper {

	/**
	 * @param $category
	 * @param $name
	 *
	 * @return string
	 * @since 1.0.2
	 */
	public static function newPhrase($category = null, $name) {
		/**@var Phrase $phraseCategory */
		$model = new Phrase();
		if ($category === null) {
			$model->parent_id = 0;
		} else {
			$phraseQuery = Phrase::find()->where(['name' => $category]);
			if ($phraseQuery->exists() && $phraseCategory = $phraseQuery->one()) {
				$model->parent_id = $phraseCategory->id;
			} else {
				$phraseCategory            = new Phrase();
				$phraseCategory->parent_id = 0;
				$phraseCategory->name      = $category;
				if ($phraseCategory->save()) {
					$model->parent_id             = $phraseCategory->getPrimaryKey();
					$PhraseTranslate              = new PhraseTranslate();
					$PhraseTranslate->phrase_id   = $phraseCategory->getPrimaryKey();
					$PhraseTranslate->language_id = Language::getIdByCode(Yii::$app->language);
					$PhraseTranslate->value       = '';
					$PhraseTranslate->save();
				}
			}
		}
		$model->name = $name;
		if ($model->save()) {
			$PhraseTranslate              = new PhraseTranslate();
			$PhraseTranslate->phrase_id   = $model->getPrimaryKey();
			$PhraseTranslate->language_id = Language::getIdByCode(Yii::$app->language);
			$PhraseTranslate->value       = '';
			$PhraseTranslate->save();
		}
		return 'error: phrase [' . $name . '] not found';
	}

	/**
	 * @param $language_code string
	 * @param $path          string
	 *
	 * @return array
	 * @throws ErrorException
	 * @since 1.0.0
	 */
	private static function _setAllData($language_code, $path) {
		/**@var $models Phrase[] */
		/**@var $categories Phrase[] */
		$file = $path . DIRECTORY_SEPARATOR . 'phrase_' . $language_code . '.json';
		if (!file_exists($file)) {
			$data = null;
		} else {
			$data = file_get_contents($file);
		}
		if ($data == null || $data == '') {
			$code       = $language_code;
			$categories = Phrase::find()->where([
				'parent_id' => 0,
			])->all();
			foreach ($categories as $category) {
				$category->setDynamicField();
				$data[$category->id] = [
					'key'   => $category->name,
					'value' => $category->$code,
				];
				$models              = Phrase::find()->where(['parent_id' => $category->id])->all();
				foreach ($models as $model) {
					$model->setDynamicField();
					$data[$category->id]['data'][$model->id] = [
						'key'   => $model->name,
						'value' => $model->$code,
					];
				}
			}
		} else {
			$data       = Json::decode($data);
			$code       = $language_code;
			$categories = Phrase::find()->where([
				'parent_id' => 0,
			])->all();
			foreach ($categories as $category) {
				if (!isset($data[$category->id])) {
					$category->setDynamicField();
					$data[$category->id] = [
						'key'   => $category->name,
						'value' => $category->$code,
					];
				}
				$models = Phrase::find()->where(['parent_id' => $category->id])->all();
				foreach ($models as $model) {
					if (!isset($data[$category->id]['data'][$model->id])) {
						$model->setDynamicField();
						$data[$category->id]['data'][$model->id] = [
							'key'   => $model->name,
							'value' => $model->$code,
						];
					}
				}
			}
		}
		file_put_contents($file, Json::encode($data));
		return $data;
	}

	/**
	 * @param $path
	 *
	 * @param $data
	 *
	 * @since 1.0.0
	 */
	private static function _setClass($path, $data) {
		$php = '<?php' . PHP_EOL;
		$php .= 'namespace navatech\language;' . PHP_EOL;
		$php .= 'class Translate {' . PHP_EOL;
		foreach ($data as $category) {
			$php .= '       /**' . PHP_EOL;
			$php .= '       * @param null|array|mixed $parameters' . PHP_EOL;
			$php .= '       * @param null|string $language_code' . PHP_EOL;
			$php .= '       * @return string' . PHP_EOL;
			$php .= '       */' . PHP_EOL;
			$php .= '       public static function ' . $category['key'] . '($parameters = null, $language_code = null){}' . PHP_EOL;
			if (isset($category['data']) && $category['data'] != null) {
				foreach ($category['data'] as $item) {
					$php .= '       /**' . PHP_EOL;
					$php .= '       * @param null|array|mixed $parameters' . PHP_EOL;
					$php .= '       * @param null|string $language_code' . PHP_EOL;
					$php .= '       * @return string' . PHP_EOL;
					$php .= '       */' . PHP_EOL;
					$php .= '       public static function ' . $item['key'] . '($parameters = null, $language_code = null){}' . PHP_EOL;
				}
			}
		}
		$php .= '//defined_new_method_here' . PHP_EOL;
		$php .= '}';
		$file = $path . DIRECTORY_SEPARATOR . 'Translate.php';
		$fp   = fopen($file, 'wb');
		fwrite($fp, $php);
		fclose($fp);
	}

	/**
	 *
	 * @throws Exception|InvalidParamException
	 */
	public static function setLanguages() {
		$runtime = Yii::getAlias('@runtime');
		$path    = $runtime . DIRECTORY_SEPARATOR . 'language';
		if (!file_exists($path) && !@mkdir($path, 0777, true) && !is_dir($path)) {
			throw new Exception('Cannot create directory');
		}
		$code = [];
		foreach (Language::getLanguages() as $language) {
			$code[$language->id] = $language->getAttributes();
		}
		$file = $path . DIRECTORY_SEPARATOR . 'languages.json';
		$fp   = fopen($file, 'wb');
		fwrite($fp, Json::encode($code));
		fclose($fp);
	}

	/**
	 * @return array|mixed
	 * @throws ErrorException|Exception
	 * @since 1.0.2
	 */
	public static function getLanguages() {
		$runtime = Yii::getAlias('@runtime');
		$path    = $runtime . DIRECTORY_SEPARATOR . 'language';
		if (!file_exists($path)) {
			self::setLanguages();
			return self::getLanguages();
		}
		$file = $path . DIRECTORY_SEPARATOR . 'languages.json';
		if (!file_exists($file)) {
			self::setLanguages();
			return self::getLanguages();
		}
		try {
			$myFile = fopen($file, "r");
			$data   = fread($myFile, filesize($file));
		} catch (ErrorException $e) {
			throw new ErrorException('Unable to open "' . $file . '"');
		}
		$data = Json::decode($data);
		fclose($myFile);
		return $data;
	}

	/**
	 * @param null $language_code
	 *
	 * @throws Exception|InvalidParamException
	 */
	public static function setAllData($language_code = null) {
		$runtime = Yii::getAlias('@runtime');
		$path    = $runtime . DIRECTORY_SEPARATOR . 'language';
		if (!file_exists($path) && !@mkdir($path, 0777, true) && !is_dir($path)) {
			throw new Exception('Cannot create directory');
		}
		$data = null;
		if ($language_code !== null) {
			$data = self::_setAllData($language_code, $path);
		} else {
			foreach (Language::getLanguages() as $language) {
				$data = self::_setAllData($language->code, $path);
			}
		}
		self::_setClass($path, $data);
	}

	/**
	 * @param $language_code
	 *
	 * @return array|mixed|string
	 * @since 1.0.1
	 * @throws InvalidParamException|ErrorException
	 */
	public static function getData($language_code) {
		$runtime = Yii::getAlias('@runtime');
		$path    = $runtime . DIRECTORY_SEPARATOR . 'language';
		$file    = $path . DIRECTORY_SEPARATOR . 'phrase_' . $language_code . '.json';
		if (!file_exists($path) || !file_exists($file) || !file_get_contents($file)) {
			self::setAllData($language_code);
			return self::getData($language_code);
		}
		try {
			$myFile  = fopen($file, "r");
			$content = fread($myFile, filesize($file));
		} catch (ErrorException $e) {
			throw new ErrorException('Unable to open "' . $file . '"');
		}
		fclose($myFile);
		if ($content === '') {
			self::setAllData($language_code);
			return self::getData($language_code);
		}
		$data = Json::decode($content);
		if ($data === null) {
			self::setAllData($language_code);
			return self::getData($language_code);
		}
		return $data;
	}

	/**
	 * @param $language_code
	 *
	 * @return bool
	 * @since 1.0.0
	 * @throws InvalidParamException
	 */
	public static function removeAllData($language_code) {
		$runtime = Yii::getAlias('@runtime');
		$path    = $runtime . DIRECTORY_SEPARATOR . 'language';
		if (!file_exists($path)) {
			return true;
		}
		$file = $path . DIRECTORY_SEPARATOR . 'phrase_' . $language_code . '.json';
		if (!file_exists($file)) {
			return true;
		}
		return unlink($file);
	}

	/**
	 * @param PhraseTranslate $model
	 *
	 * @throws ErrorException|Exception
	 * @since 1.0.2
	 */
	public static function setData(PhraseTranslate $model) {
		$name          = $model->phrase->name;
		$language_code = $model->language->code;
		$runtime       = Yii::getAlias('@runtime');
		$path          = $runtime . DIRECTORY_SEPARATOR . 'language';
		if (!file_exists($path)) {
			self::setAllData();
		}
		if ($model->isNewRecord) {
			foreach (self::getLanguages() as $language) {
				$file = $path . DIRECTORY_SEPARATOR . 'phrase_' . $language['code'] . '.json';
				if (!file_exists($file)) {
					self::setAllData($language['code']);
				}
				try {
					$myFile = fopen($file, "r");
					$data   = fread($myFile, filesize($file));
				} catch (ErrorException $e) {
					throw new ErrorException('Unable to open "' . $file . '"');
				}
				fclose($myFile);
				$data = Json::decode($data);
				if ($data === null) {
					self::setAllData($language['code']);
				} else {
					if (!array_key_exists($name, $data)) {
						$data[$name] = $model->value;
						file_put_contents($file, Json::encode($data));
					}
				}
			}
			$class = $path . DIRECTORY_SEPARATOR . 'Translate.php';
			if (!file_exists($class)) {
				self::setAllData();
			}
			try {
				$myFile  = fopen($class, "r");
				$content = fread($myFile, filesize($class));
			} catch (ErrorException $e) {
				throw new ErrorException('Unable to open "' . $class . '"');
			}
			fclose($myFile);
			if (!strpos($content, 'function ' . $name . '(')) {
				$php = '       /**' . PHP_EOL;
				$php .= '       * @param null|array|mixed $parameters' . PHP_EOL;
				$php .= '       * @param null|string $language_code' . PHP_EOL;
				$php .= '       * @return string' . PHP_EOL;
				$php .= '       */' . PHP_EOL;
				$php .= '       public static function ' . $name . '($parameters = null, $language_code = null){}' . PHP_EOL;
				$php .= '//defined_new_method_here' . PHP_EOL;
				$content = str_replace('//defined_new_method_here', $php, $content);
				$fp      = fopen($class, 'wb');
				fwrite($fp, $content);
				fclose($fp);
			}
		} else {
			$file = $path . DIRECTORY_SEPARATOR . 'phrase_' . $language_code . '.json';
			if (!file_exists($file)) {
				self::setAllData($language_code);
			}
			try {
				$myFile = fopen($file, "r");
				$data   = fread($myFile, filesize($file));
			} catch (ErrorException $e) {
				throw new ErrorException('Unable to open "' . $file . '"');
			}
			$data = Json::decode($data);
			fclose($myFile);
			if ($data === null) {
				self::setAllData($language_code);
			} else {
				$data[$name] = $model->value;
				file_put_contents($file, Json::encode($data));
			}
		}
	}

	/**
	 * Return all translated attributes include value
	 *
	 * @param ActiveRecord $model
	 * @param null         $language_code
	 *
	 * @return array
	 * @throws InvalidConfigException
	 */
	public static function attributes($model, $language_code = null) {
		$behavior = $model->behaviors();
		if (!isset($behavior['ml'])) {
			throw new InvalidConfigException("MultiLanguage was not defined in " . get_class($model) . ".");
		}
		$response = [];
		foreach (Language::getLanguages() as $language) {
			foreach ($behavior['ml']['attributes'] as $mlAttribute) {
				$attribute = $mlAttribute . '_' . $language->code;
				if ($language_code != null) {
					if ($language_code == $language->code) {
						$response[$attribute] = isset($model->behaviors['ml']) ? $model->$attribute : '';
					}
				} else {
					$response[$attribute] = isset($model->behaviors['ml']) ? $model->$attribute : '';
				}
			}
		}
		return $response;
	}

	/**
	 * Return all translated attributes name
	 *
	 * @param ActiveRecord $model
	 * @param null         $language_code
	 *
	 * @return array
	 * @throws InvalidConfigException
	 */
	public static function attributeNames($model, $language_code = null) {
		$attributes = self::attributes($model, $language_code);
		return array_keys($attributes);
	}
}