<?php
namespace enflares\System;

class Cache implements SingletonInterface
{
    const DEFAULT_EXPIRE = 180; // seconds

    /**
     * @return Cache
     */
    public static function getInstance()
    {
        static $g;
        if( !$g ) $g = new static;
        return $g;
    }

    public static function get($key)
    {
        return static::getInstance()->fetch($key);
    }

    public static function set($key, $value, $ttl=NULL)
    {
        if( is_null($value) || ($ttl<0) )
            return static::getInstance()->remove($key);

        return static::getInstance()->store($key, $value, $ttl);
    }

    public static function has($key)
    {
        return static::getInstance()->exists($key);
    }

    public static function delete($key)
    {
        return static::getInstance()->remove($key);
    }

    public static function update($key, $value, $ttl)
    {
        return static::has($key) ? static::set($key, $value) : static::set($key, $value, $ttl);
    }

    private function filename($key)
    {
        $key = md5($key);
        return path('resource', env('SITE_NAME', 'default'), 'cache', substr($key, 0, 2), substr($key, 2));
    }

    public function fetch($key)
    {
        $file = $this->filename($key);
        if( realpath($file) ) return unserialize(file_get_contents($file));
    }

    public function store($key, $value, $ttl=NULL)
    {
        $file = $this->filename($key);

        $ttl = intval($ttl);
        if( is_null($value) || ($ttl<0) )
            if( realpath($file) ) return @unlink($file);

        if( !realpath(dirname($file)) )
        {
            // Cache path doesn't exists
            if( !mkdir(dirname($file), 0777, TRUE) ) return FALSE;

            $time = time() + ($ttl ?: intval(env('CACHE_DEFAULT_EXPIRE', static::DEFAULT_EXPIRE)));
        }else{
            $time = $ttl ? (time()+$ttl) : filemtime($file);
        }

        file_put_contents($file, serialize($value), LOCK_EX) && touch($file, $time);

        return filemtime($file);
    }

    public function exists($key)
    {
        $file = $this->filename($key);
        return (realpath($file) && (($m=filemtime($file))>=time())) ? $m : FALSE;
    }

    public function remove($key)
    {
        return $this->store($key, NULL, -1);
    }
}

