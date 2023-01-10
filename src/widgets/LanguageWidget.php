<?php
/**
 * Created by phuongdev89.
 * @project    Yii2 Multi Language
 * @author     Phuong
 * @email      phuongdev89@gmail.com
 * @created    13/02/2016 16:20 CH
 * @updated    03/03/2016 00:40 SA
 * @since      2.0.0
 */

namespace phuongdev89\language\widgets;

use phuongdev89\language\assets\LanguageAsset;
use phuongdev89\language\helpers\LanguageHelper;
use phuongdev89\language\models\Language;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\InvalidParamException;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\web\UrlManager;
use yii\web\View;

/**
 * Widget is the base class for widgets.
 *
 * @property string $id ID of the widget.
 * @property View $view The view object that can be used to render views or view files. Note that the
 * type of this property differs in getter and setter. See [[getView()]] and [[setView()]] for details.
 * @property string $viewPath The directory containing the view files for this widget. This property is
 * read-only.
 * @property-read array|int[] $data
 */
class LanguageWidget extends Widget
{

    public $type = 'classic';

    public $size = 30;

    /**@var UrlManager */
    private $urlManager;

    private $languages;

    private $current;

    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     * @throws Exception
     * @throws ErrorException
     */
    public function init()
    {
        parent::init();
        LanguageAsset::register($this->view);
        $this->urlManager = Yii::$app->urlManager;
        $this->languages = LanguageHelper::getLanguages();
        $this->current = [
            'code' => 'en',
            'name' => 'United States',
            'country' => 'us',
        ];
    }

    /**
     * @return array
     */
    protected function getData()
    {
        /**@var  $currentLanguage Language */
        list($route, $params) = Yii::$app->getUrlManager()->parseRequest(Yii::$app->getRequest());
        $route = Yii::$app->controller->getRoute() != '' ? Yii::$app->controller->route : $route;
        $params = ArrayHelper::merge($_GET, $params);
        $data = [0];
        foreach ($this->languages as $language) {
            if ($language['code'] === Yii::$app->language) {
                $this->current = ArrayHelper::merge([
                    'url' => $this->urlManager->createUrl(ArrayHelper::merge($params, [
                        array_key_exists('route', $params) ? $params['route'] : $route,
                        'language' => $language['code'],
                    ])),
                ], $language);
                $data[0] = $this->current;
            } else {
                $data[] = ArrayHelper::merge([
                    'url' => $this->urlManager->createUrl(ArrayHelper::merge($params, [
                        array_key_exists('route', $params) ? $params['route'] : $route,
                        'language' => $language['code'],
                    ])),
                ], $language);
            }
        }
        if (!array_key_exists(0, $data) || $data[0] === 0) {
            $currentLanguage = Language::findOne(['code' => Yii::$app->language]);
            if ($currentLanguage) {
                $data[0] = [
                    'url' => $this->urlManager->createUrl(ArrayHelper::merge($params, [
                        array_key_exists('route', $params) ? $params['route'] : $route,
                        'language' => $currentLanguage->code,
                    ])),
                    'code' => $currentLanguage->code,
                    'name' => $currentLanguage->name,
                    'country' => $currentLanguage->country,
                ];
            }
        }
        if (count($data) > count(Language::getLanguages())) {
            return array_unique($data);
        } else {
            return $data;
        }
    }

    /**
     * Executes the widget.
     * @return string the result of widget execution to be outputted.
     * @throws InvalidParamException
     */
    public function run()
    {
        $renderView = 'languageClassic';
        if ($this->type === 'selector') {
            $renderView = 'languageSelector';
        }
        return $this->render($renderView, [
            'data' => $this->getData(),
            'size' => $this->size,
            'current' => $this->current,
        ]);
    }
}
