
#Behavior attributes
Attributes marked as bold are required

Attribute | Description
----------|------------
languageField | The name of the language field of the translation table. Default is 'language'
translateClassName | The name of translation model class. Default value is model name + Translate
translateForeignKey | Name of the foreign key field of the translation table related to base model table.
translateTableName | The name of the translation table
**attributes** | Multilanguage attributes. Required only

#Usage

Here an example of base 'post' table :

```sql
CREATE TABLE IF NOT EXISTS `post` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    `enabled` tinyint(1) NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

And its associated translation table (configured as default), assuming translated fields are 'title' and 'content':

```sql
CREATE TABLE IF NOT EXISTS `post_translate` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `post_id` int(11) NOT NULL,
    `language` varchar(6) NOT NULL,
    `title` varchar(255) NOT NULL,
    `content` TEXT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `post_id` (`post_id`),
    KEY `language` (`language`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `post_translate` ADD CONSTRAINT `postlang_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
```

Attaching this behavior to the model (Post in the example). Commented fields have default values.

```php
/**
 * You should keep below comments for IDE hint
 * @method string getTranslateAttribute(string $attribute, string|null $language_code = null)
 * @method boolean hasTranslateAttribute(string $attribute_translation)
 * @method array getTranslateAttributes(string $attribute)
 */
class Model extends \phuongdev89\language\db\ActiveRecord {

    /**
     * {@inheritDoc}
     */
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['ml'] = [
            'class'      => \phuongdev89\language\components\LanguageBehavior::className(),
            'attributes' => [
                'title',
                'content',
            ],
        ];
        return $behaviors;
    }

    /**
     * {@inheritDoc}
     */
    public function rules() {
        return [
            .........
                [
                    //if want all translated attributes required
                    ArrayHelper::merge(\phuongdev89\language\helpers\LanguageHelper::attributeNames($this), [
                        'email',
                        .......
                    ]),
                    'required',
                ],
            .........
                [
                    //make all translated attributes safe
                    [
                        'title',
                        'content',
                        'email',
                        .......
                    ]),
                    'safe',
                ],
            .........
        ];
    }

    	/**
    	 * @inheritdoc
    	 */
    	public function attributeLabels() {
    		$attributeLabels = [
    			'id'           => 'ID',
    			'Email'        => Translate::email(),
    			......
    		];
    		foreach(\phuongdev89\language\helpers\LanguageHelper::attributeNames($this) as $mlAttribute){
    		    $attributeLabels[$mlAttribute] = Translate::$mlAttribute();
    		}
    		return $attributeLabels;
    	}
```

#Examples

Example #1:
--------
current language translations are inserted to the model as normal attributes by default.

```php
//Assuming current language is english
$model = Post::findOne(1);
echo $model->title; //echo "English title"

//Now let's imagine current language is french
$model = Post::findOne(1);
echo $model->title; //echo "Titre en Français"

$model = Post::find()->where(['id' => 1])->localized('en')->one();
echo $model->title; //echo "English title"

//Current language is still french here
```
Form example to get all translate attributes:
```php
	$form = ActiveForm::begin();
	foreach ($model->getTranslateAttributes() as $translateAttribute) {
		echo $form->field($model, $translateAttribute)->textInput();
	}
	echo $form->field($model, 'created_at')->textInput();
	ActiveForm::end();
```

Example #2:
--------
if you use `translate()` in a `find()` query, every model translation is loaded as virtual attributes (title_en, title_fr, title_de, ...).

```php
$model = Post::find()->translate()->one();
echo $model->title_en; //echo "English title"
echo $model->title_fr; //echo "Titre en Français"
```

or get by attribute;
```php
	$form = ActiveForm::begin();
	foreach ($model->getTranslateAttributes('title') as $translateAttribute) {
		echo $form->field($model, $translateAttribute)->textInput();
	}
	foreach ($model->getTranslateAttributes('content') as $translateAttribute) {
		echo $form->field($model, $translateAttribute)->textInput();
	}
	echo $form->field($model, 'created_at')->textInput();
	ActiveForm::end();
```
Example #3:
--------
If you use `findOneTranslated()` query, every model translation is loaded as virtual attributes (title_en, title_fr, title_de, ...)
and in `getTranslateAttribute()` method

```php
$model = Post::findOneTranslated(1);
//OR
$model = Post::findOneTranslated(['id' => 1]);
echo $model->getTranslateAttribute('title'); //echo "English title"
echo $model->getTranslateAttribute('title', 'fr'); //echo "Titre en Français"
```

or get by attribute;
```php
$form = ActiveForm::begin();
    foreach (Language::getLanguages() as $key => $language):
        echo '<div class="active in tab-pane fade" id="tab_'.$language->code.'">';
        echo $form->field($model, 'title_' . $language->code)->textInput([
            'value' => $model->getIsNewRecord() ? '' : $model->getTranslateAttribute('title', $language->code),
        ]);
        echo $form->field($model, 'content_' . $language->code)->textarea([
            'value' => $model->getIsNewRecord() ? '' : $model->getTranslateAttribute('content', $language->code),
        ]);
        echo '</div>';
    endforeach;
ActiveForm::end();
```
