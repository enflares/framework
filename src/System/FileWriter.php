<?php
namespace enflares\System;

/**
 * Class File
 * @package enflares\System
 */
class FileWriter extends File implements StorageWriterInterface
{
    /**
     * The default time offset of the cache
     */
    const DEFAULT_CACHE_TIME = 60;

    /**
     * Write all or part of data
     * Note: serialization will be taken part in if $data is not scalar type
     * @param $data
     * @param null $length
     * @return bool|false|int|mixed
     */
    public function write($data, $length=NULL)
    {
        if( !is_scalar($data) ) $data = serialize($data);
        if( $length>0 ) $data = substr($data, 0, $length);
        return ( $this->touch() )
                ? file_put_contents($this->realpath(), $data, LOCK_EX)
                : false;
    }

    /**
     * @inheritDoc
     */
    public function store($key, $data, $lifeTime = NULL)
    {
        return ($path = $this->realpath())
                && ($file = $path . DS . substr($name = md5($key), 0, 2) . DS . substr($name, 2))
                && (is_dir($path = dirname($file)) || mkdir($path, 0777, TRUE))
                    && ($modifiedTime = realpath($file)
                        ? filemtime($file)
                        : (time()+intval(env('CACHE_DEFAULT_LIFETIME', static::DEFAULT_CACHE_TIME))))
                    && file_put_contents($file, serialize($data), LOCK_EX)
                    && touch( $file, is_null($lifeTime) ? $modifiedTime : timeExpire($lifeTime) );
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        return $this->write('');
    }

}