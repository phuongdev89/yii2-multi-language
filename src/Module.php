<?php
/**
 * Created by Navatech.
 * @project Yii2 Multi Language
 * @author  Phuong
 * @email   phuong17889[at]gmail.com
 * @date    04/02/2016
 * @time    1:46 SA
 * @since   1.0.2
 */
namespace navatech\language;

use navatech\language\helpers\LanguageHelper;
use Yii;

class Module extends \navatech\base\Module {

	const VERSION = '2.0.1';

	/**
	 * @var string
	 * @since 2.0.1
	 */
	public $suffix              = 'translate';

	public $controllerNamespace = 'navatech\language\controllers';

	public $defaultRoute        = 'site/index';

	/**
	 * {@inheritDoc}
	 */
	public function init() {
		parent::init();
		LanguageHelper::getData(Yii::$app->language);
	}
}