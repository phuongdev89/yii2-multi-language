If not use cutom url rule, language widget suggest [localeurls](https://github.com/phuongdev89/yii2-localeurls). Automatic locale/language management through URLs for Yii 2.
* Type: Selector
~~~
[php]
    echo LanguageWidget::widget([
        //TODO type of widget ("selector" or "classic")
        'type'     => 'selector',
        //TODO uncommented to change size, default: 30, means width 30px & height 30px for every flag, from 10 to 300
        //'size'     => 30,
        //TODO uncommented to customize widget view
        //'viewDir' => '@vendor/phuongdev89/yii2-multi-language/src/views/LanguageWidget',
    ]);
~~~
![#](http://i.imgur.com/WfDK5Dq.png "Selector widget")

* Type: Classic
~~~
[php]
    echo LanguageWidget::widget([
        //TODO type of widget ("selector" or "classic")
        'type'     => 'classic',
        //TODO uncommented to change size, default: 30, means width 30px & height 30px for every flag, from 10 to 300
        //'size'     => 30,
        //TODO uncommented to customize widget view
        //'viewDir' => '@vendor/phuongdev89/yii2-multi-language/src/views/LanguageWidget',
    ]);
~~~
![#](http://i.imgur.com/cu1xGe9.png "Classic widget")
