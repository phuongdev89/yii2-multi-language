<?php
/**
 * Created by Navatech.
 * @project enesti-com-vn
 * @author  Phuong
 * @email   phuong17889[at]gmail.com
 * @date    6/27/2016
 * @time    5:07 PM
 */
namespace navatech\language\web;

use navatech\language\models\Language;
use navatech\language\Module;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Cookie;

class LanguageController extends Controller {

	/**
	 * {@inheritDoc}
	 */
	public function beforeAction($action) {
		$languages = ArrayHelper::map(Language::getLanguages(), 'code', 'name');
		if (isset($_GET['language']) && isset($languages[$_GET['language']]) && $_GET['language'] != Yii::$app->request->getCookies()->getValue('_language')) {
			Yii::$app->session['language'] = $_GET['language'];
			$cookie                        = new Cookie([
				'name'  => '_language',
				'value' => $_GET['language'],
				'path'  => '/',
			]);
			Yii::$app->language            = $_GET['language'];
			Yii::$app->response->cookies->add($cookie);
			$this->refresh();
		}
		if (!Yii::$app->request->cookies->has('_language')) {
			if (Module::hasSetting()) {
				$cookie             = new Cookie([
					'name'  => '_language',
					'value' => Yii::$app->setting->general_language,
					'path'  => '/',
				]);
				Yii::$app->language = Yii::$app->setting->general_language;
			} else {
				$cookie = new Cookie([
					'name'  => '_language',
					'value' => Yii::$app->language,
					'path'  => '/',
				]);
			}
			Yii::$app->response->cookies->add($cookie);
		} else {
			Yii::$app->language = Yii::$app->request->getCookies()->getValue('_language');
		}
		return parent::beforeAction($action);
	}
}