<?php
namespace enflares\System;

/**
 * Class PatternValidator
 * @package enflares\System
 */
class PatternValidator extends Validator
{
    /**
     * @var string
     */
    protected $pattern;

    /**
     * PatternValidator constructor.
     * @param $pattern
     */
    public function __construct($pattern)
    {
        if( is_string($pattern) )
            $pattern = array('pattern'=>$pattern);

        parent::__construct((array)$pattern);
    }

    /**
     * @param $value
     * @param null $column
     * @return string
     */
    public function test($value, $column=NULL)
    {
        if( $pattern = trim($this->pattern) )
            if( !preg_match($pattern, $value) )
                return 'Invalid data format';
    }
}