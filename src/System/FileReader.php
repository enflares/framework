<?php
namespace enflares\System;

/**
 * Class File
 * @package enflares\System
 */
class FileReader extends File implements StorageReaderInterface
{
    /**
     * @inheritDoc
     */
    public function exists($key)
    {
        return ( $file = $this->realpath() )
                && !!realpath($file . DS .
                                substr($name = md5($key), 0, 2) . DS .
                                substr($name, 2));
    }

    /**
     * @inheritDoc
     */
    public function fetch($key)
    {
        if( $path = $this->realpath() ) {
            $file = $path . DS . substr($name = md5($key), 0, 2) . DS .
                                 substr($name, 2);
            if( realpath($file) && (filemtime($file)>=time()) )
                return unserialize(file_get_contents($file));
        }
    }

    /**
     * Read all or part of data as text
     * @param null $length
     * @param null $skip
     * @return false|string
     */
    public function read($length=NULL, $skip=NULL)
    {
        return ( $file=$this->realpath() )
                ? file_get_contents($file, FALSE, NULL, intval($skip), $length)
                : false;
    }

}