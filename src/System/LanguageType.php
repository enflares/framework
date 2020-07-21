<?php
namespace enflares\System;

/**
 * Class LanguageType
 * @package enflares\System
 */
class LanguageType
{
    /**
     * @var string
     */
    private $majority;
    /**
     * @var string
     */
    private $minority;

    /**
     * LanguageType constructor.
     * @param null $major
     * @param null $minor
     */
    public function __construct($major=NULL, $minor=NULL)
    {
        $value = $major.'-'.$minor;
        $this->setMajor($major);
        $this->setMinor($minor ?: substr($value, 3));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return trim("$this->majority-$this->minority", '-');
    }

    /**
     * @return string
     */
    public function getMajor()
    {
        return $this->majority;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setMajor($value)
    {
        $this->majority = substr(strtolower(trim($value)), 0, 2);
        return $this;
    }

    /**
     * @return string
     */
    public function getMinor()
    {
        return $this->minority;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setMinor($value)
    {        
        $this->minority = substr(strtoupper(trim($value)), 0, 2);
        return $this;
    }
}

