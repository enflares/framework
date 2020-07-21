<?php
namespace enflares\System;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use SessionHandlerInterface;

/**
 * Class Session
 * @package enflares\System
 */
class Session extends Component implements IteratorAggregate, ArrayAccess
{
    use ArrayAccessTrait;

    /**
     * @return Session|Component
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }

    /**
     * Start a session
     *
     * @return Session|void
     * @throws Exception
     */
    public static function start()
    {
        if( !isset($_SESSION) ) {
            switch( session_status() ) {
                case PHP_SESSION_DISABLED:
                    return Exception::trigger('Session is disabled');
                break;

                case PHP_SESSION_ACTIVE:
                break;

                case PHP_SESSION_NONE:
                    // session_cache_limiter(env('SESSION_CACHE_LIMITER'));
                    // session_cache_expire(env('SESSION_CACHE_EXPIRE'));
                    //session_module_name(env('SESSION_MODULE_NAME'));
                    // session_save_path(env('SESSION_SAVE_PATH'));
                    //session_name(env('SESSION_NAME'));
                    // session_id(env('SESSION_ID'));
                    if( $class = env('SESSION_SAVE_HANDLER') ) {
                        if( is_subclass_of($class, SessionHandlerInterface::class) ) {
                            if( is_subclass_of($class, SingletonInterface::class) ) {
                                session_set_save_handler($class::getInstance());
                            }else{
                                session_set_save_handler(new $class);
                            }
                        }
                    }

                    session_start();
                break;
            }
        }

        return static::getInstance();
    }

    /**
     * Get the current session id
     * @return string
     */
    public static function id()
    {
        return session_id();
    }

    /**
     * Get the current session name.
     * @return string
     */
    public static function name(){
        return session_name();
    }

    /**
     * Discard session array changes and finish session
     * @return bool|void
     */
    public static function abort()
    {
        return session_abort();
    }

    /**
     * Write session data and end session
     * @return bool|void
     */
    public static function close()
    {
        return session_write_close();
    }

    /**
     * Get a value from $_SESSION
     * @param $key
     * @param null $default
     * @return mixed|null
     * @throws Exception
     */
    public static function get($key, $default=NULL)
    {
        $result = static::start()->{$key};
        if( is_null($result) ) $result = $default;
        return $result;
    }

    /**
     * Set a value to $_SESSION
     * @param $key
     * @param $value
     * @return Session
     * @throws Exception
     */
    public static function set($key, $value)
    {
        return static::start()->__set($key, $value);
    }

    /**
     * Check if an entry exists in $_SESSION
     * @param $key
     * @return bool
     * @throws Exception
     */
    public static function has($key)
    {
        return isset(static::start()->$key);
    }

    /**
     * Remove an entry off $_SESSION
     * @param $key
     * @return Session
     * @throws Exception
     */
    public static function delete($key)
    {
        return static::set($key, NULL);
    }

    /**
     * Return the $_SESSION
     * @return mixed
     * @throws Exception
     */
    public static function all()
    {
        static::start();
        return $_SESSION;
    }

    /**
     * Clear all session data
     * @return Session|void
     * @throws Exception
     */
    public static function clear()
    {
        $instance = static::start();
        $_SESSION = [];
        return $instance;
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        Session::close();
    }

    /**
     * Session entry getter
     * @param $key
     * @return mixed|null
     */
    public function __get($key)
    {
        return $_SESSION[$key] ?? NULL;
    }

    /**
     * Session entry setter
     * @param $key
     * @param $value
     * @return $this|mixed
     */
    public function __set($key, $value)
    {
        if( is_null($value) ) unset($_SESSION[$key]);
        else $_SESSION[$key] = $value;

        return $this;
    }

    /**
     * Check if an entry exists in Session
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove an entry off the Session
     * @param $key
     */
    public function __unset($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($_SESSION);
    }

}