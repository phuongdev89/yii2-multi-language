<?php
/**
 * Created by Navatech.
 * @project    Yii2 Multi Language
 * @author     Phuong
 * @email      phuong17889[at]gmail.com
 * @created    13/02/2016 16:20 CH
 * @updated    03/03/2016 00:40 SA
 * @since      2.0.0
 */
namespace navatech\language\widgets;

use navatech\language\assets\LanguageAsset;
use navatech\language\helpers\LanguageHelper;
use navatech\language\models\Language;
use Yii;
use yii\base\Exception;
use yii\base\InvalidParamException;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\web\UrlManager;

/**
 * Widget is the base class for widgets.
 *
 * @property string        $id       ID of the widget.
 * @property \yii\web\View $view     The view object that can be used to render views or view files. Note that the
 * type of this property differs in getter and setter. See [[getView()]] and [[setView()]] for details.
 * @property string        $viewPath The directory containing the view files for this widget. This property is
 * read-only.
 */
class LanguageWidget extends Widget {

	public $type    = 'classic';

	public $size    = 30;

	/**@var UrlManager */
	private $urlManager;

	private $languages;

	private $current;

	/**
	 * Initializes the object.
	 * This method is invoked at the end of the constructor after the object is initialized with the
	 * given configuration.
	 * @throws Exception|InvalidParamException
	 */
	public function init() {
		parent::init();
		LanguageAsset::register($this->view);
		$this->urlManager = Yii::$app->urlManager;
		$this->languages  = LanguageHelper::getLanguages();
		$this->current    = [
			'code'    => 'en',
			'name'    => 'United States',
			'country' => 'us',
		];
	}

	/**
	 * Returns the directory containing the view files for this widget.
	 * The default implementation returns the 'views' subdirectory under the directory containing the widget class file.
	 * @return string the directory containing the view files for this widget.
	 * @throws InvalidParamException
	 */
	public function getViewPath() {
		if ($this->viewDir === null) {
			$name = explode("\\", self::className());
			return Yii::getAlias(dirname(__DIR__ . '/../views') . DIRECTORY_SEPARATOR . end($name));
		}
		return $this->viewDir;
	}

	/**
	 * @return array
	 */
	protected function getData() {
		/**@var  $currentLanguage Language */
		list($route, $params) = Yii::$app->getUrlManager()->parseRequest(Yii::$app->getRequest());
		$route  = Yii::$app->controller->getRoute() != '' ? Yii::$app->controller->route : $route;
		$params = ArrayHelper::merge($_GET, $params);
		$data   = [0];
		foreach ($this->languages as $language) {
			if ($language['code'] === Yii::$app->language) {
				$this->current = ArrayHelper::merge([
					'url' => $this->urlManager->createUrl(ArrayHelper::merge($params, [
						array_key_exists('route', $params) ? $params['route'] : $route,
						'language' => $language['code'],
					])),
				], $language);
				$data[0]       = $this->current;
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
					'url'     => $this->urlManager->createUrl(ArrayHelper::merge($params, [
						array_key_exists('route', $params) ? $params['route'] : $route,
						'language' => $currentLanguage->code,
					])),
					'code'    => $currentLanguage->code,
					'name'    => $currentLanguage->name,
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
	public function run() {
		$renderView = 'languageClassic';
		if ($this->type === 'selector') {
			$renderView = 'languageSelector';
		}
		return $this->render($renderView, [
			'data'    => $this->getData(),
			'size'    => $this->size,
			'current' => $this->current,
		]);
	}
}
