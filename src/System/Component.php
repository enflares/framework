<?php
namespace enflares\System;

class Component extends Data implements SingletonInterface
{
    /**
     * Global registries
     * @var array
     */
    private static $globals = array();

    /**
     * Return a global registry
     * @param $key
     * @return mixed|null
     */
    public static function __global($key)
    {
        return self::$globals[$key] ?? NULL;
    }

    /**
     * Dispose all singleton objects
     */
    protected static function __global_dispose(){
        foreach( self::$globals as $key=>$value )
            unset( self::$globals[$key] );
    }

    /**
     * Return the singleton object
     * @return Component
     */
    public static function getInstance(){
        $class = static::class;

        if( !isset(self::$globals[$class]) )
            self::$globals[$class] = new static(...func_get_args());

        return self::$globals[$class];
    }

    /**
     * To trigger an event
     * @param Event|string $event The event name or event object to be triggered
     * @param null $eventArgs Optional
     * @return mixed
     */
    protected function fire($event, $eventArgs=NULL){
        if( $class = Event::lookUp($event) ) {
            $args = func_get_args();
            array_shift($args);
            call_user_func([$class, 'fire'], $args, $this);
        }
        
        return $this;
    }
}