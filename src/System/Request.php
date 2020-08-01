<?php
namespace enflares\System;

use ArrayIterator;
use Closure;

/**
 * Class Request
 * @package enflares\System
 */
class Request extends Data
{
    use ArrayAccessTrait;

    /**
     * Checks if the request is by HEAD method
     *
     * @return boolean
     */
    public static function isHead()
    {
        return !strcasecmp(static::server('REQUEST_METHOD'), 'HEAD');
    }

    /**
     * Checks if the request is by GET method
     *
     * @return boolean
     */
    public static function isGet()
    {
        return !strcasecmp(static::server('REQUEST_METHOD'), 'GET');
    }

    /**
     * Checks if the request is by POST method
     *
     * @return boolean
     */
    public static function isPost()
    {
        return !strcasecmp(static::server('REQUEST_METHOD'), 'POST');
    }

    /**
     * Checks if the request is by DELETE method
     *
     * @return boolean
     */
    public static function isDelete()
    {
        return !strcasecmp(static::server('REQUEST_METHOD'), 'DELETE');
    }

    /**
     * Checks if the request is by PUT method
     *
     * @return boolean
     */
    public static function isPut()
    {
        return !strcasecmp(static::server('REQUEST_METHOD'), 'PUT');
    }

    /**
     * Checks if the request is by PATCH method
     *
     * @return boolean
     */
    public static function isPatch()
    {
        return !strcasecmp(static::server('REQUEST_METHOD'), 'PATCH');
    }

    /**
     * Checks if the request is by OPTIONS method
     *
     * @return boolean
     */
    public static function isOptions()
    {
        return !strcasecmp(static::server('REQUEST_METHOD'), 'OPTIONS');
    }

    /**
     * Fetch an entry from $_GET
     * @param $key
     * @param $default
     * @return Request|mixed
     */
    public static function get($key=NULL, $default=NULL)
    {
        if( !func_num_args() ) {
            $instance = new static;
            $instance->_ = &$_GET;
            return $instance;
        }
        
        return $_GET[$key] ?? $default;
    }

    /**
     * Fetch an entry from $_POST
     * @param $key
     * @param $default
     * @return Request|mixed
     */
    public static function post($key=NULL, $default=NULL)
    {
        if( !func_num_args() ) {
            $instance = new static;
            $instance->_ = &$_POST;
            return $instance;
        }

        return $_POST[$key] ?? $default;
    }

    public static function upload($field, $index=NULL)
    {
        return new Upload($field, $index);
    }

    /**
     * Return one header or all headers
     * @param null $key Must be in a format like : XRequestedWith
     * @param null $default
     * @return mixed
     */
    public static function header($key=NULL, $default=NULL)
    {
        $args = func_get_args();
        if (strpos($key, 'HTTP_') === FALSE) $args[0] = 'HTTP_' . $key;
        return static::server(...$args);
    }

    /**
     * Get an adaptor on $_COOKIE or an entry from it
     * @param null $key
     * @param null $default
     * @return mixed|static|null
     */
    public static function cookie($key=NULL, $default=NULL)
    {
        if( !func_num_args() ) {
            $instance = new static;
            $instance->_ = $_COOKIE;
            return $instance;
        }

        return $_COOKIE[$key] ?? $default;
    }

    /**
     * Get an adaptor on $_SERVER or an entry from it
     * @param null $key
     * @param null $default
     * @return static|null
     */
    public static function server($key=NULL, $default=NULL)
    {   
        static $g;
        if( !$g ) {
            $g = new static;
            $g->_ = array_change_key_case($_SERVER, CASE_UPPER);
            if (function_exists('apache_request_headers'))
                $g->_ = array_merge($g->_, array_change_key_case(apache_request_headers(), CASE_UPPER));
        }

        return func_num_args() ? ($g->__get(strtr(strtoupper($key), '-', '_')) ?: $default)
                               : $g;
    }

    /**
     * Get an adaptor on $_SESSION or an entry from it
     * @param null $key
     * @param null $default
     * @return Component|Session|mixed|null
     * @throws Exception
     */
    public static function session($key=NULL, $default=NULL)
    {
        return func_num_args() ? Session::get($key, $default) : Session::getInstance();
    }

    /**
     * @var array
     */
    private $_ = [];

    /**
     * Get an entry
     * @param $key
     * @return mixed|null
     */
    public function __get($key)
    {
        return $this->_[$key] ?? NULL;
    }

    /**
     * Set the value to an entry
     * @param $key
     * @param $value
     * @return $this
     */
    public function __set($key, $value)
    {
        $k = &$this->_;
        $k[$key] = $value;
        return $this;
    }

    /**
     * Check if an entry exists
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->_[$key]);
    }

    /**
     * Remove an entry
     * @param $key
     */
    public function __unset($key)
    {
        $k = &$this->_;
        unset($k[$key]);
    }

    /**
     * Merge data
     * @param array $data
     * @return $this
     */
    public function merge(Array $data)
    {
        $k = &$this->_;
        foreach( $data as $key=>$value ) $k[$key] = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_);
    }

    /**
     * Return all data as an array
     * @return array
     */
    public function all()
    {
        return $this->_;
    }

    /**
     * Fetch some values
     * @return array
     */
    public function some()
    {
        return array_values($this->pack(...func_get_args()));
    }

    /**
     * Fetch some entries
     * @return array
     */
    public function pack()
    {
        $results = array();
        foreach( func_get_args() as $key )
            $results[$key] = $this->__get($key);

        return $results;
    }

    /**
     * Return an entry as ID
     * @param null $column
     * @return mixed
     */
    public function id($column=NULL)
    {
        return max(0, intval($this->__get($column ?: 'id')));
    }

    /**
     * Return an entry as Integer
     * @param $column
     * @return int
     */
    public function int($column)
    {
        return intval($this->__get($column));
    }

    /**
     * Return an entry as Float
     * @param $column
     * @return float
     */
    public function float($column)
    {
        return floatval($this->__get($column));
    }

    /**
     * Return an entry as Boolean
     * @param $column
     * @return bool|void
     */
    public function bool($column)
    {
        $result = $this->__get($column);
        if( !is_null($result) )
            return !in_array(trim(strtolower($result)), array('', '0', 'off', 'no', 'false', 'disabled'));
    }

    /**
     * Validate some values
     * @param $columns
     * @return array
     * @throws Exception
     */
    public function validate($columns){
        $args = func_get_args();

        $results = array();
        foreach( (array)$columns as $column ){
            $args[0] = $column;
            $results[$column] = $this->assert(...$args);
        }

        return $results;
    }

    /**
     * Validate a value and asserts if not matched
     * @param $column
     * @return mixed|null
     * @throws Exception
     */
    public function assert($column)
    {
        $value = $this->__get($column);

        $count = func_num_args();
        for($i=1; $i<$count; $i++) {
            $validator = func_get_arg($i);

            if( $validator instanceof Validator )
                $message = $validator->test($validator, $column);
            elseif ( $validator instanceof Closure )
                $message = $validator($value);
            else
                $message = Validator::validate($validator, $value, $column);

            if( $message )
                return InvalidException::trigger($message, $column, $value);
        }

        return $value;
    }
}