<?php
namespace enflares\System;

use ReflectionClass;
use ReflectionException;

/**
 * Class View
 * @package enflares\System
 */
class View extends Template
{
    use ItemsTrait;
    use ConfigJsonTrait;
    use AttributesTrait;

    /**
     * @var mixed
     */
    private $__data;

    /**
     * @var string
     */
    private $__name;

    /**
     * View class constructor
     *
     * @param string $name
     * @param array|null $data
     * @param string $base
     */
    public function __construct($name, Array $data=NULL, $base=NULL)
    {
        parent::__construct($data);
        $this->setTemplate($name, $base);
    }

    /**
     * Gets the template name
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->__name;
    }

    /**
     * Sets the template name
     * @param $name
     * @param null $base
     * @return $this
     */
    public function setTemplate($name, $base=NULL)
    {
        $this->__name = $name;

        $file = Theme::lookUpTemplate($name);
        if( !$file && $base ) {
            try {
                $path = ($base instanceof DataInterface) ? dirname((new ReflectionClass($base))->getFileName()) : $base;
                $parts = explode('/', strtr($path, '\\', '/'));

                while( !empty($parts) ) {
                    if( $path = realpath(implode(DS, $parts) . DS . 'view') ) break;
                    array_pop($parts);
                }

                if( $path ) $file = Theme::lookUpTemplate($name, $path);
            } catch (ReflectionException $e) {}
        }

        $this->view_file( $file ?: Theme::lookUpTemplate($name, realpath(path('resources', 'view', 'default'))));
        return $this;
    }

    /**
     * The data source
     * @param mixed $data
     * @return $this
     */
    public function data($data=NULL)
    {
        if( func_num_args() ) $this->data = $data;
        return $this;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return vars($name, $this->__data) ?: parent::__get($name);
    }

    public function __isset($name)
    {
        return isset($this->__data[$name], $this->data->$name) || parent::__isset($name);
    }

    public function slot($name, Array $args=NULL)
    {
        // 屏蔽掉
    }
}