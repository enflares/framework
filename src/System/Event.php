<?php
namespace enflares\System;

use Closure;

/**
 * Class Event
 * @package enflares\System
 */
abstract class Event
{
    /**
     * @var array
     */
    protected static $listeners = array();

    /**
     * Convert a string to a class name of an event
     * @param string $event
     * @return string
     */
    public static function lookUp($event)
    {
        $class = 'Event\\'.strtr($event, '.', '\\');
        if( class_exists($class) && is_subclass_of($class, __CLASS__) )
            return $class;

        $class = 'App\\Event\\'.strtr($event, '.', '\\');
        if( class_exists($class) && is_subclass_of($class, __CLASS__) )
            return $class;
    }

    /**
     * Raise/Trigger an event
     * @param array|NULL $eventArgs
     * @param null $eventSender
     * @return mixed
     */
    public static function fire(Array $eventArgs=NULL, $eventSender=NULL)
    {
        $event = new static;
        $event->sender = $eventSender;
        $event->arguments = $eventArgs;

        foreach( $event::$listeners as $listener ) {
            if( $listener instanceof Closure ) {
                $listener($event);
            } else if( $listener instanceof EventServiceInterface ) {
                $listener->notify($event);
            } else if( is_string($listener) ) {
                if( is_subclass_of($listener, EventServiceInterface::class) ) {
                    call_user_func([is_subclass_of($listener, SingletonInterface::class )
                                ? call_user_func([$listener, 'getInstance'])
                                : (new $listener), 'notify'], $event);
                }
            }

            if( $event->cancel ) break;
        }

        return $event->result;
    }

    /**
     * Register notification listener from an event
     * @param $listener
     */
    public static function hook($listener)
    {
        if( !in_array($listener, static::$listeners, TRUE) )
            static::$listeners[] = $listener;
    }

    /**
     * Unregister notification listener off an event
     * @param $listener
     */
    public static function unhook($listener){
        unset( static::$listeners[array_search($listener, static::$listeners, TRUE)] );
    }

    protected $sender;
    protected $arguments;
    protected $result;
    protected $cancel;

    public function getSender()
    {
        return $this->sender;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function cancel()
    {
        $this->cancel = TRUE;
        return $this;
    }

    public function __toString()
    {
        return static::class;
    }
}