<?php
namespace enflares\System;

/**
 * Class Validator
 * @package enflares\System
 */
abstract class Validator
{
    use PropertyTrait;

    /**
     * Generates a validator
     * @param $validator
     * @return Validator
     */
    public static function factory($validator)
    {
        if( $validator instanceof self )
            return $validator;

        $class = NULL;
        if( is_array($validator) ) {
            $class = isset($validator['class']) ? $validator['class'] : (
                isset($validator[0]) ? $validator[0] : NULL
            );
        }elseif( is_string($validator) ){
            $class = ucfirst($validator) . 'Validator';
        }

        if( is_string($class) && $class ) {
            if (is_subclass_of($class, __CLASS__)) {
                return new $class($validator);
            }
            $pattern = $class;
        }elseif( isset($validator['pattern']) ) {
            $pattern = $validator['pattern'];
        }else{
            $pattern = NULL;
        }

        if( $pattern ) return new PatternValidator($pattern);
    }

    /**
     * Performs validation
     * @param $validator
     * @param $value
     * @param $column
     * @return bool
     */
    public static function validate($validator, $value, $column=NULL)
    {
        $instance = static::factory($validator);
        return $instance ? $instance->test($value, $column) : FALSE;
    }

    /**
     * Performs instance validation
     * @param $value
     * @param null $column
     * @return mixed
     */
    public abstract function test($value, $column=NULL);
}