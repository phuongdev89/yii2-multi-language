<?php
/**
 * Created by phuongdev89.
 * @project yii2-basic
 * @author  Phuong
 * @email   phuongdev89@gmail.com
 * @date    5/13/2016
 * @time    12:36 AM
 */

namespace phuongdev89\language\helpers;

use phuongdev89\language\db\ActiveRecord;
use phuongdev89\language\models\Language;
use phuongdev89\language\models\Phrase;
use phuongdev89\language\models\PhraseTranslate;
use phuongdev89\language\Module;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\helpers\Json;

class LanguageHelper
{

    /**
     * @param $name
     *
     * @return string
     * @since 1.0.2
     */
    public static function newPhrase($name)
    {
        $model = new Phrase();
        $model->name = $name;
        if ($model->save()) {
            $PhraseTranslate = new PhraseTranslate();
            $PhraseTranslate->phrase_id = $model->getPrimaryKey();
            $PhraseTranslate->language_id = Language::getIdByCode(Yii::$app->language);
            $PhraseTranslate->value = ucfirst(str_replace('_', ' ', $name));
            $PhraseTranslate->save();
        }
        return 'error: phrase [' . $name . '] not found';
    }

    /**
     * @param $language_code
     * @param $path
     *
     * @return array
     * @throws ErrorException
     * @since 1.0.0
     */
    private static function _setAllData($language_code, $path)
    {
        $file = $path . DIRECTORY_SEPARATOR . 'phrase_' . $language_code . '.json';
        if (!file_exists($file)) {
            $data = null;
        } else {
            $data = file_get_contents($file);
        }
        if ($data == null || $data == '') {
            /**@var $models Phrase[] */
            $models = Phrase::find()->all();
            $code = $language_code;
            foreach ($models as $model) {
                $model->setDynamicField();
                $data[$model->name] = $model->$code;
            }
        } else {
            $data = [];
            /**@var $models Phrase[] */
            $models = Phrase::find()->all();
            $code = $language_code;
            foreach ($models as $model) {
                $model->setDynamicField();
                if (!array_key_exists($model->name, $data)) {
                    $data[$model->name] = $model->$code;
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
    private static function _setClass($path, $data)
    {
        $php = '<?php' . PHP_EOL;
        $php .= 'namespace phuongdev89\language;' . PHP_EOL;
        $php .= 'class Translate {' . PHP_EOL;
        foreach ($data as $key => $item) {
            $php .= '       /**' . PHP_EOL;
            $php .= '       * @param null|array|mixed $parameters' . PHP_EOL;
            $php .= '       * @param null|string $language_code' . PHP_EOL;
            $php .= '       * @return string' . PHP_EOL;
            $php .= '       */' . PHP_EOL;
            $php .= '       public static function ' . $key . '($parameters = null, $language_code = null){}' . PHP_EOL;
        }
        $php .= '//defined_new_method_here' . PHP_EOL;
        $php .= '}';
        $file = $path . DIRECTORY_SEPARATOR . 'Translate.php';
        $fp = fopen($file, 'wb');
        fwrite($fp, $php);
        fclose($fp);
    }

    /**
     *
     * @throws Exception|InvalidParamException
     */
    public static function setLanguages()
    {
        $runtime = Module::isBasic() ? Yii::getAlias('@runtime') : Yii::getAlias('@backend/runtime');
        $path = $runtime . DIRECTORY_SEPARATOR . 'language';
        if (!file_exists($path) && !@mkdir($path, 0777, true) && !is_dir($path)) {
            throw new Exception('Cannot create directory');
        }
        $code = [];
        foreach (Language::getLanguages() as $language) {
            $code[$language->id] = $language->getAttributes();
        }
        $file = $path . DIRECTORY_SEPARATOR . 'languages.json';
        $fp = fopen($file, 'wb');
        fwrite($fp, Json::encode($code));
        fclose($fp);
    }

    /**
     * @return array|mixed
     * @throws ErrorException|Exception
     * @since 1.0.2
     */
    public static function getLanguages()
    {
        $runtime = Module::isBasic() ? Yii::getAlias('@runtime') : Yii::getAlias('@backend/runtime');
        $path = $runtime . DIRECTORY_SEPARATOR . 'language';
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
            $data = fread($myFile, filesize($file));
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
     * @throws ErrorException
     * @throws Exception
     */
    public static function setAllData($language_code = null)
    {
        $runtime = Module::isBasic() ? Yii::getAlias('@runtime') : Yii::getAlias('@backend/runtime');
        $path = $runtime . DIRECTORY_SEPARATOR . 'language';
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
     * @throws ErrorException
     * @throws Exception
     * @since 1.0.1
     */
    public static function getData($language_code)
    {
        $runtime = Module::isBasic() ? Yii::getAlias('@runtime') : Yii::getAlias('@backend/runtime');
        $path = $runtime . DIRECTORY_SEPARATOR . 'language';
        $file = $path . DIRECTORY_SEPARATOR . 'phrase_' . $language_code . '.json';
        if (!file_exists($path) || !file_exists($file) || !file_get_contents($file)) {
            self::setAllData($language_code);
            return self::getData($language_code);
        }
        try {
            $myFile = fopen($file, "r");
            $content = fread($myFile, filesize($file));
        } catch (ErrorException $e) {
            throw new ErrorException('Unable to open "' . $file . '"');
        }
        fclose($myFile);
        if ($content === '') {
            self::setAllData($language_code);
            return self::getData($language_code);
        }
        try {
            $data = Json::decode($content);
            if ($data === null) {
                self::setAllData($language_code);
                return self::getData($language_code);
            }
        } catch (InvalidParamException $e) {
            self::setAllData($language_code);
            return [];
        }
        return $data;
    }

    /**
     * @param $language_code
     *
     * @return bool
     * @throws InvalidParamException
     * @since 1.0.0
     */
    public static function removeAllData($language_code)
    {
        $runtime = Module::isBasic() ? Yii::getAlias('@runtime') : Yii::getAlias('@backend/runtime');
        $path = $runtime . DIRECTORY_SEPARATOR . 'language';
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
    public static function setData(PhraseTranslate $model)
    {
        $name = $model->phrase->name;
        $language_code = $model->language->code;
        $runtime = Module::isBasic() ? Yii::getAlias('@runtime') : Yii::getAlias('@backend/runtime');
        $path = $runtime . DIRECTORY_SEPARATOR . 'language';
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
                    $data = fread($myFile, filesize($file));
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
                $myFile = fopen($class, "r");
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
                $fp = fopen($class, 'wb');
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
                $data = fread($myFile, filesize($file));
                $data = Json::decode($data);
                fclose($myFile);
                if ($data === null) {
                    self::setAllData($language_code);
                } else {
                    $data[$name] = $model->value;
                    file_put_contents($file, Json::encode($data));
                }
            } catch (ErrorException $e) {
                throw new ErrorException('Unable to open "' . $file . '"');
            } catch (InvalidParamException $e) {
                self::setAllData($language_code);
            }
        }
    }

    /**
     * Return all translated attributes include value
     *
     * @param ActiveRecord $model
     * @param null $language_code
     *
     * @return array
     * @throws InvalidConfigException
     */
    public static function attributes($model, $language_code = null)
    {
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
     * @param null $language_code
     *
     * @return array
     * @throws InvalidConfigException
     */
    public static function attributeNames($model, $language_code = null)
    {
        $attributes = self::attributes($model, $language_code);
        return array_keys($attributes);
    }
}
