<?php
namespace enflares\System;

/**
 * Interface StorageReaderInterface
 * @package enflares\System
 */
interface StorageReaderInterface
{
    /**
     * Check if an entry exists
     * @param $key
     * @return mixed
     */
    public function exists($key);

    /**
     * Fetch an entry
     * @param $key
     * @return mixed
     */
    public function fetch($key);

    /**
     * Read a part from a curtain position
     * @param null $length
     * @param null $skip
     * @return mixed
     */
    public function read($length=NULL, $skip=NULL);
}