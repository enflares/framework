<?php
/**************************************************
 * Enflares - A PHP Framework For Web
 * @package  Enflares
 * @author   Shaotang Zhang <shaotang.zhang@gmail.com>
 **************************************************/

/////////////////////////////
//// Localization Helpers ////
/////////////////////////////

if( !function_exists('_t') ){
    /**
     * Translate a text into localization
     * @param string $message
     * @param null $arg
     * @return string
     */
    function _t($message, $arg=NULL)
    {
        if( func_num_args()>1 )
        {
            // todo: localization a text
            $message = sprintf(...func_get_args());
        }

        return $message;
    }
}

if( !function_exists('locale') ){
    /**
     * Localization variable
     * @param mixed $var
     * @param null $arg
     * @return string|array
     * @throws Exception
     */
    function locale($var, $arg=NULL) {
        if( is_array($var) ) {
            $args = func_get_args();
            $args[0] = __FUNCTION__;
            return array_map(...$args);
        }
        
        if( is_int($var) )
            return number_format($var);
        if( is_numeric($var) )
            return number_format($var, intval($arg), env('LOCALE_DEC_POINT', '.'), env('LOCALE_DEC_GROUP', ','));

        if( is_string($var) ) $var = new DateTime($var);
        if( $var instanceof DateTime )
            return $var->format(env('LOCALE_'.strtoupper(strtr(strtr($arg, '-', '_'), ' ', '_')), 'c'));

        return _t(...func_get_args());
    }
}

if( !function_exists('locale_date') ){
    /**
     * Localization a date time
     * @param mixed $var
     * @param null $arg
     * @return string|array
     * @throws Exception
     */
    function locale_date($var, $arg=NULL) {
        $args = func_get_args();
        if( is_int($var) ) {
            $args[0] = new DateTime;
            $args[0]->setTimestamp($var);
        }else {
            $args[0] = new DateTime($var);
        }
        return locale(...$args);
    }
}