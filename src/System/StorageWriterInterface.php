<?php
namespace enflares\System;

/**
 * Interface StorageWriterInterface
 * @package enflares\System
 */
interface StorageWriterInterface
{
    /**
     * Save an entry
     * @param $key
     * @param $data
     * @param null $lifeTime
     * @return bool
     */
    public function store($key, $data, $lifeTime=NULL);

    /**
     * Clear all data
     * @return mixed
     */
    public function clear();

    /**
     * Write data
     * @param $data
     * @param null $length
     * @return mixed
     */
    public function write($data, $length=NULL);
}