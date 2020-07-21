<?php
namespace enflares\System;

/**
 * Class Storage
 * @package enflares\System
 */
abstract class Storage
{
    /**
     * @return StorageReaderInterface
     */
    public abstract function getReader();

    /**
     * @return StorageWriterInterface
     */
    public abstract function getWriter();
}