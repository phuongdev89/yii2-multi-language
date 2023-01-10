# yii2-multi-language

[![Packagist Version](https://img.shields.io/packagist/v/phuongdev89/yii2-multi-language.svg?style=flat)](https://packagist.org/packages/phuongdev89/yii2-multi-language)
[![Total Downloads](https://img.shields.io/packagist/dt/phuongdev89/yii2-multi-language.svg?style=flat)](https://packagist.org/packages/phuongdev89/yii2-multi-language)

This module allow you to create multi language use database.
By default, Yii use Yii::t() for multi language.
But you must stored the sentences on file, and it never suggested keywords for you.
Now you can store it on mySql.

## Requirements
* [Yii 2](https://packagist.org/packages/yiisoft/yii2)
* [Yii 2 Bootstrap](https://packagist.org/packages/yiisoft/yii2-bootstrap)
* [Kartik-v Yii 2 Grid](https://packagist.org/packages/kartik-v/yii2-grid)
* [Kartik-v Yii 2 editable](https://packagist.org/packages/kartik-v/yii2-editable)
* [phuongdev89 Yii 2 Locale Urls](https://packagist.org/packages/phuongdev89/yii2-localeurls)

## Install & config:

### Install

Preferred way to install this extension through [composer](http://getcomposer.org)  
Either run
~~~
composer require phuongdev89/yii2-multi-language "^2.0"
~~~
Or add to `require` section of `composer.json` then run `composer update`
~~~
"phuongdev89/yii2-multi-language" : "^2.0" 
~~~

### Config:
~~~
[php]
    'language'   => 'en', //TODO Change this to 2 characters
    .....................
    'bootstrap'           => [
        'log',
        'multiLanguage',
    ],
    'components' => [
        'multiLanguage' => [
           'class' => '\phuongdev89\language\Component',
        ],
    ],
    'modules'    => [
        'gridview' => [
            'class' => '\kartik\grid\Module',
        ],
        'language' => [
        'class'    => '\phuongdev89\language\Module',
        /*TODO uncommented if you want to custom view*/
        //'viewPath' => '@app/vendor/phuongdev89/yii2-multi-language/src/views',
        /*TODO uncommented if you want to change suffix of translated table / model.
        should be one word, lowercase only.*/
        //'suffix' => 'translate',
        ],
    ],
~~~
Run Yii Migration, you will have two default language (English & Vietnamese):
```
php yii migrate/up --migrationPath=@vendor/phuongdev89/yii2-multi-language/src/migrations
```


## Usage
[LanguageWidget](https://github.com/phuongdev89/yii2-multi-language/blob/master/docs/widget.md)

[Multi language on model](https://github.com/phuongdev89/yii2-multi-language/blob/master/docs/multi.md)

[Translate](https://github.com/phuongdev89/yii2-multi-language/blob/master/docs/translate.md)

[Custom Url Route](https://github.com/phuongdev89/yii2-multi-language/blob/master/docs/route.md)
