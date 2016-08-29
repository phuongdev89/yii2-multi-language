<?php
/**
 * Created by Navatech.
 * @project    yii-basic
 * @author     Phuong
 * @email      phuong17889[at]gmail.com
 * @date       10/03/2016
 * @time       5:39 CH
 * @since      2.0.0
 */
namespace navatech\language\base;

use navatech\language\helpers\LanguageHelper;
use navatech\language\Module;
use Yii;
use yii\base\Behavior;
use yii\base\Component;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\base\UnknownPropertyException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use yii\validators\Validator;
use yii\web\NotFoundHttpException;

/**
 * @property ActiveQuery $translation
 * @property ActiveQuery $translations
 */
class LanguageBehavior extends Behavior {

	/**
	 * @var ActiveRecord|$this|self
	 */
	public $owner;

	/**
	 * Multilanguage attributes
	 * @var array
	 */
	public $attributes;

	/**
	 * @var string the name of the translation table
	 */
	public $translateTableName;

	/**
	 * @var string the name of translation model class.
	 */
	public $translateClassName;

	/**
	 * @var string the name of the foreign key field of the translation table related to base model table.
	 */
	public $translateForeignKey;

	/**
	 * @var string the name of the lang field of the translation table. Default to 'language'.
	 */
	public $languageField = 'language';

	/**
	 * @var boolean whether to force deletion of the associated translations when a base model is deleted.
	 * Not needed if using foreign key with 'on delete cascade'.
	 * Default to true.
	 */
	public $forceDelete = true;

	/**
	 * @var array list available languages
	 */
	private $availableLanguages = [];

	/**
	 * @var string current default language
	 */
	private $currentLanguage;

	/**
	 * @var int current primary key
	 */
	private $ownerPrimaryKey;

	/**
	 * @var string current class name
	 */
	private $ownerClassName;

	/**
	 * @var array temp of values
	 */
	private $translateAttributes = [];

	/**@var Module */
	private $module;

	/**
	 * Initializes the object.
	 * This method is invoked at the end of the constructor after the object is initialized with the
	 * given configuration.
	 */
	public function init() {
		parent::init();
		$this->module          = Yii::$app->getModule('language');
		$this->currentLanguage = Yii::$app->language;
		foreach (LanguageHelper::getLanguages() as $language) {
			$this->availableLanguages[$language['code']] = $language['name'];
		}
	}

	/**
	 * Declares event handlers for the [[owner]]'s events.
	 *
	 * Child classes may override this method to declare what PHP callbacks should
	 * be attached to the events of the [[owner]] component.
	 *
	 * The callbacks will be attached to the [[owner]]'s events when the behavior is
	 * attached to the owner; and they will be detached from the events when
	 * the behavior is detached from the component.
	 *
	 * @return array events (array keys) and the corresponding event handler methods (array values).
	 */
	public function events() {
		return [
			ActiveRecord::EVENT_AFTER_FIND      => 'afterFind',
			ActiveRecord::EVENT_AFTER_UPDATE    => 'afterUpdate',
			ActiveRecord::EVENT_AFTER_INSERT    => 'afterInsert',
			ActiveRecord::EVENT_AFTER_DELETE    => 'afterDelete',
			ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
		];
	}

	/**
	 * Attaches the behavior object to the component.
	 * The default implementation will set the [[owner]] property
	 * and attach event handlers as declared in [[events]].
	 * Make sure you call the parent implementation if you override this method.
	 *
	 * @param Component $owner the component that this behavior is to be attached to.
	 *
	 * @throws InvalidConfigException
	 */
	public function attach($owner) {
		/** @var ActiveRecord $ownerClassName */
		/** @var ActiveRecord $owner */
		parent::attach($owner);
		$this->module          = Yii::$app->getModule('language');
		$this->currentLanguage = Yii::$app->language;
		foreach (LanguageHelper::getLanguages() as $language) {
			$this->availableLanguages[$language['code']] = $language['name'];
		}
		if (empty($this->availableLanguages) || !is_array($this->availableLanguages)) {
			throw new InvalidConfigException('Please specify at least one of available languages on ' . Url::to(['language/index']), 101);
		}
		if (array_values($this->availableLanguages) !== $this->availableLanguages) {
			$this->availableLanguages = array_keys($this->availableLanguages);
		}
		if (empty($this->attributes) || !is_array($this->attributes)) {
			throw new InvalidConfigException('Please specify multilingual attributes for the ' . get_class($this) . ' in the ' . get_class($this->owner), 103);
		}
		if (!$this->translateClassName) {
			$this->translateClassName = get_class($this->owner) . ucfirst($this->module->suffix);
		}
		$this->ownerClassName  = get_class($this->owner);
		$ownerClassName        = $this->ownerClassName;
		$this->ownerPrimaryKey = $ownerClassName::primaryKey()[0];
		if (!$this->translateForeignKey) {
			$this->translateForeignKey = $this->owner->tableName() . '_id';
		}
		if (!$this->translateTableName) {
			$this->translateTableName = $this->owner->tableName() . '_' . $this->module->suffix;
		}
		$rules      = $owner->rules();
		$validators = $owner->getValidators();
		foreach ($rules as $rule) {
			if ($rule[1] == 'unique') {
				continue;
			}
			$rule_attributes = is_array($rule[0]) ? $rule[0] : [$rule[0]];
			$attributes      = array_intersect($this->attributes, $rule_attributes);
			if (empty($attributes)) {
				continue;
			}
			$rule_attributes = [];
			foreach ($attributes as $key => $attribute) {
				foreach ($this->availableLanguages as $language) {
					$rule_attributes[] = $attribute . "_" . $language;
				}
			}
			$params = array_slice($rule, 2);
			if ($rule[1] !== 'required') {
				$validators[] = Validator::createValidator($rule[1], $owner, $rule_attributes, $params);
			} else {
				$validators[] = Validator::createValidator('safe', $owner, $rule_attributes, $params);
			}
		}
		if (class_exists($this->translateClassName)) {
			$translation = new $this->translateClassName;
			foreach ($this->availableLanguages as $language) {
				foreach ($this->attributes as $attribute) {
					$this->setTranslateAttribute($attribute . "_" . $language, $translation->$attribute);
					if ($language == Yii::$app->language) {
						$this->setTranslateAttribute($attribute, $translation->$attribute);
					}
				}
			}
		}
	}

	/**
	 * Relation to model translations
	 * @return ActiveQuery
	 * @since 2.0.0
	 */
	public function getTranslations() {
		return $this->owner->hasMany($this->translateClassName, [$this->translateForeignKey => $this->ownerPrimaryKey]);
	}

	/**
	 * Relation to model translation
	 *
	 * @param $language
	 *
	 * @return ActiveQuery
	 * @since 2.0.0
	 */
	public function getTranslation($language = null) {
		if ($language == null) {
			$language = $this->currentLanguage;
		}
		return $this->owner->hasOne($this->translateClassName, [$this->translateForeignKey => $this->ownerPrimaryKey])->where([$this->languageField => $language]);
	}

	/**
	 * Handle 'beforeValidate' event of the owner.
	 */
	public function beforeValidate() {
		foreach ($this->attributes as $attribute) {
			$this->setTranslateAttribute($attribute, $this->getTranslateAttribute($attribute . '_' . $this->currentLanguage));
		}
	}

	/**
	 * Handle 'afterFind' event of the owner.
	 */
	public function afterFind() {
		if ($this->owner->isRelationPopulated('translations') && $related = $this->owner->getRelatedRecords()['translations']) {
			$translations = $this->indexByLanguage($related);
			foreach ($this->availableLanguages as $lang) {
				foreach ($this->attributes as $attribute) {
					foreach ($translations as $translation) {
						if ($translation->{$this->languageField} == $lang) {
							$this->setTranslateAttribute($attribute . '_' . $lang, $translation->$attribute);
							if ($lang == Yii::$app->language) {
								$this->setTranslateAttribute($attribute, $translation->$attribute);
							}
						}
					}
				}
			}
		} else {
			if (!$this->owner->isRelationPopulated('translation')) {
				$this->owner->translation;
			}
			$translation = $this->owner->getRelatedRecords()['translation'];
			if ($translation) {
				foreach ($this->attributes as $attribute) {
					$this->owner->setTranslateAttribute($attribute, $translation->$attribute);
				}
			}
		}
		foreach ($this->attributes as $attribute) {
			if ($this->owner->hasAttribute($attribute) && $this->getTranslateAttribute($attribute)) {
				$this->owner->setAttribute($attribute, $this->getTranslateAttribute($attribute));
			}
		}
	}

	/**
	 * Handle 'afterInsert' event of the owner.
	 */
	public function afterInsert() {
		$this->saveTranslations();
	}

	/**
	 * Handle 'afterUpdate' event of the owner.
	 */
	public function afterUpdate() {
		/** @var ActiveRecord $owner */
		$owner = $this->owner;
		if ($owner->isRelationPopulated('translations')) {
			$translations = $this->indexByLanguage($owner->getRelatedRecords()['translations']);
			$this->saveTranslations($translations);
		}
	}

	/**
	 * Handle 'afterDelete' event of the owner.
	 */
	public function afterDelete() {
		if ($this->forceDelete) {
			$this->owner->unlinkAll('translations', true);
		}
	}

	/**
	 * Returns a value indicating whether a property can be read.
	 * A property is readable if:
	 *
	 * - the class has a getter method associated with the specified name
	 *   (in this case, property name is case-insensitive);
	 * - the class has a member variable with the specified name (when `$checkVars` is true);
	 *
	 * @param string  $name      the property name
	 * @param boolean $checkVars whether to treat member variables as properties
	 *
	 * @return boolean whether the property can be read
	 * @see canSetProperty()
	 */
	public function canGetProperty($name, $checkVars = true) {
		return method_exists($this, 'get' . $name) || $checkVars && property_exists($this, $name) || $this->hasTranslateAttribute($name);
	}

	/**
	 * Returns a value indicating whether a property can be set.
	 * A property is writable if:
	 *
	 * - the class has a setter method associated with the specified name
	 *   (in this case, property name is case-insensitive);
	 * - the class has a member variable with the specified name (when `$checkVars` is true);
	 *
	 * @param string  $name      the property name
	 * @param boolean $checkVars whether to treat member variables as properties
	 *
	 * @return boolean whether the property can be written
	 * @see canGetProperty()
	 */
	public function canSetProperty($name, $checkVars = true) {
		return $this->hasTranslateAttribute($name);
	}

	/**
	 * Returns the value of an object property.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `$value = $object->property;`.
	 *
	 * @param string $name the property name
	 *
	 * @return mixed the property value
	 * @throws UnknownPropertyException if the property is not defined
	 * @throws InvalidCallException if the property is write-only
	 * @see __set()
	 */
	public function __get($name) {
		try {
			return parent::__get($name);
		} catch (UnknownPropertyException $e) {
			if ($this->hasTranslateAttribute($name)) {
				return $this->getTranslateAttribute($name);
			} else {
				throw $e;
			}
		}
	}

	/**
	 * Sets value of an object property.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `$object->property = $value;`.
	 *
	 * @param string $name  the property name or the event name
	 * @param mixed  $value the property value
	 *
	 * @throws UnknownPropertyException if the property is not defined
	 * @throws InvalidCallException if the property is read-only
	 * @see __get()
	 */
	public function __set($name, $value) {
		try {
			parent::__set($name, $value);
		} catch (UnknownPropertyException $e) {
			if ($this->hasTranslateAttribute($name)) {
				$this->setTranslateAttribute($name, $value);
			} else {
				throw $e;
			}
		}
	}

	/**
	 * Checks if a property is set, i.e. defined and not null.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `isset($object->property)`.
	 *
	 * Note that if the property is not defined, false will be returned.
	 *
	 * @param string $name the property name or the event name
	 *
	 * @return boolean whether the named property is set (not null).
	 * @see http://php.net/manual/en/function.isset.php
	 */
	public function __isset($name) {
		if (!parent::__isset($name)) {
			return $this->hasTranslateAttribute($name);
		} else {
			return true;
		}
	}

	/**
	 * Save related model
	 *
	 * @param array $translations
	 *
	 * @since 2.0.0
	 * @throws NotFoundHttpException
	 */
	private function saveTranslations($translations = []) {
		/** @var ActiveRecord $owner */
		/** @var ActiveRecord $translation */
		foreach ($this->availableLanguages as $language) {
			if (!isset($translations[$language])) {
				$translation                               = new $this->translateClassName;
				$translation->{$this->languageField}       = $language;
				$translation->{$this->translateForeignKey} = $this->owner->getPrimaryKey();
			} else {
				$translation = $translations[$language];
			}
			foreach ($this->attributes as $attribute) {
				$value = $this->getTranslateAttribute($attribute, $language);
				if ($value !== null) {
					$translation->$attribute = $value;
				}
			}
			$translation->save();
		}
	}

	/**
	 * @param mixed|string $name special attribute name if you want get only multi language for this column
	 *
	 * @return array
	 * @since 2.0.0
	 */
	public function getTranslateAttributes($name = null) {
		$attributes = [];
		if ($name != null) {
			foreach ($this->availableLanguages as $language) {
				$attributes[] = $name . '_' . $language;
			}
		} else {
			foreach ($this->attributes as $attribute) {
				foreach ($this->availableLanguages as $language) {
					$attributes[] = $attribute . '_' . $language;
				}
			}
		}
		return $attributes;
	}

	/**
	 * @param $records
	 *
	 * @return array
	 * @since 2.0.0
	 */
	protected function indexByLanguage($records) {
		$sorted = array();
		foreach ($records as $record) {
			$sorted[$record->{$this->languageField}] = $record;
		}
		unset($records);
		return $sorted;
	}

	/**
	 * Whether an attribute exists
	 *
	 * @param string $name the name of the attribute
	 *
	 * @return boolean
	 * @since 2.0.0
	 */
	public function hasTranslateAttribute($name) {
		return array_key_exists($name, $this->translateAttributes);
	}

	/**
	 * @param string $name  the name of the attribute
	 * @param string $value the value of the attribute
	 *
	 * @since 2.0.0
	 */
	public function setTranslateAttribute($name, $value) {
		$this->translateAttributes[$name] = $value;
	}

	/**
	 * @param string $name the name of the attribute
	 *
	 * @param null   $language
	 *
	 * @return string the attribute value
	 * @since 2.0.0
	 */
	public function getTranslateAttribute($name, $language = null) {
		if ($language !== null) {
			$attribute = $name . '_' . $language;
		} else {
			$attribute = $name;
		}
		return $this->hasTranslateAttribute($attribute) ? $this->translateAttributes[$attribute] : null;
	}
}
