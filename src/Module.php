<?php
/**
 * Created by phuongdev89.
 * @project Yii2 Multi Language
 * @author  Phuong
 * @email   phuongdev89@gmail.com
 * @date    04/02/2016
 * @time    1:46 SA
 * @since   1.0.2
 */

namespace phuongdev89\language;
class Module extends \phuongdev89\base\Module
{

    const VERSION = '2.0.1';

    /**
     * @var string
     * @since 2.0.1
     */
    public $suffix = 'translate';

    public $modelNamespace = null;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();
    }
}
