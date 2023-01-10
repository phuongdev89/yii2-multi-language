<?php
/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/30/16
 * Time: 10:58 AM
 */

namespace phuongdev89\language;

use phuongdev89\language\models\Language;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Cookie;

class Component extends \yii\base\Component
{

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();
        $languages = ArrayHelper::map(Language::getLanguages(), 'code', 'name');
        if (isset($_GET['language']) && isset($languages[$_GET['language']]) && $_GET['language'] != Yii::$app->request->getCookies()
                ->getValue('_language')
        ) {
            Yii::$app->session->set('language', $_GET['language']);
            $cookie = new Cookie([
                'name' => '_language',
                'value' => $_GET['language'],
                'path' => '/',
            ]);
            Yii::$app->language = $_GET['language'];
            Yii::$app->response->cookies->add($cookie);
            Yii::$app->response->refresh();
        }
        if (!Yii::$app->request->cookies->has('_language')) {
            if (Module::hasSetting()) {
                $cookie = new Cookie([
                    'name' => '_language',
                    'value' => Yii::$app->setting->general_language,
                    'path' => '/',
                ]);
                Yii::$app->language = Yii::$app->setting->general_language;
            } else {
                $cookie = new Cookie([
                    'name' => '_language',
                    'value' => Yii::$app->language,
                    'path' => '/',
                ]);
            }
            Yii::$app->response->cookies->add($cookie);
        } else {
            Yii::$app->language = Yii::$app->request->getCookies()->getValue('_language');
        }
    }
}
