<?php
namespace enflares\System;

use DateTime;

/**
 * Class CookieStorage
 * @package enflares\System
 */
class CookieStorage extends Storage implements StoragereaderInterface, StorageWriterInterface
{
    public function getReader()
    {
        return $this;
    }

    public function getWriter()
    {
        return $this;
    }

    public function exists($key)
    {
        return isset($_COOKIE[$key]);
    }

    ///// READER /////

    public function fetch($key)
    {
        return isset($_COOKIE[$key]) ? unserialize($_COOKIE[$key]) : NULL;
    }

    public function read($length=NULL, $skip=NULL)
    {}


    ///// WRITER /////

    public function store($key, $data, $lifeTime=NULL)
    {
        if( is_string($lifeTime) ) $lifeTime = strtotime($lifeTime);
        if( is_int($lifeTime) ) $lifeTime = time() + intval($lifeTime);
        if( $lifeTime instanceof DateTime ) $lifeTime = $lifeTime->getTimestamp();

        return response()->cookie($key, serialize($data), time()+intval($lifeTime));
    }

    public function clear()
    {
        foreach( $_COOKIE as $key=>$value )
            setcookie($key, NULL, -1);

        return $this;
    }

    public function write($data, $length=NULL)
    {
        
    }
}