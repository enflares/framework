<?php
namespace enflares\System;

/**
 * Class File
 * @package enflares\System
 */
class File extends Storage
{
    /**
     * @var string 
     */
    private $file;

    /**
     * File constructor.
     * @param null $file
     */
    public function __construct($file=NULL)
    {
        $this->file = implode(DIRECTORY_SEPARATOR, func_get_args());        
    }

    /**
     * @return StorageReaderInterface
     */
    public function getReader()
    {
        return new FileReader($this->file);
    }

    /**
     * @return StorageWriterInterface
     */
    public function getWriter()
    {
        return new FileWriter($this->file);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "$this->file";
    }

    /**
     * Returns cannibalised absolute pathname
     * @return false|string
     */
    public function realpath()
    {
        return realpath($this->file);
    }

    /**
     * Returns filename component of path without extension
     * @return string
     */
    public function filename()
    {
        return basename($this->file, $this->ext());
    }

    /**
     * Returns filename component of path
     * @return string
     */
    public function basename()
    {
        return basename($this->file);
    }

    /**
     * Returns directory name component of path
     * @return string
     */
    public function dirname()
    {
        return dirname($this->file);
    }

    /**
     * Alias of $this->>dirname()
     * @return string
     */
    public function path()
    {
        return $this->dirname();
    }

    /**
     * Returns extension component of path
     * @return string
     */
    public function ext()
    {
        return pathinfo($this->file, PATHINFO_EXTENSION);
    }

    /**
     * Sets access and modification time of file with creation if file does not exist
     * @param null $modifiedTime
     * @param null $accessedTime
     * @return bool
     */
    public function touch($modifiedTime=NULL, $accessedTime=NULL)
    {
        return (is_dir($path = $this->path()) || mkdir($path, 0777, TRUE))
                && touch($this->file, $modifiedTime ? timeExpire($modifiedTime) : NULL,
                                     $accessedTime ? timeExpire($accessedTime) : NULL);
    }

    /**
     * Gets file size
     * @return false|int
     */
    public function size()
    {
        return ($file=$this->realpath()) ? filesize($file) : false;
    }

    /**
     * Gets file creation time
     * @return false|int
     */
    public function createdAt()
    {
        return ($file=$this->realpath()) ? filectime($file) : false;
    }

    /**
     * Gets file last access time
     * @return false|int
     */
    public function accessedAt()
    {
        return ($file=$this->realpath()) ? fileatime($file) : false;
    }

    /**
     * Gets file last modification time
     * @return false|int
     */
    public function updatedAt()
    {
        return ($file=$this->realpath()) ? filemtime($file) : false;
    }

    /**
     * Append data
     * @param $data
     * @param null $length
     * @return false|int
     */
    public function append($data, $length=NULL)
    {
        if( !is_scalar($data) ) $data = serialize($data);
        if( $length>0 ) $data = substr($data, 0, $length);
        return ( $this->touch() )
                ? file_put_contents($this->file, $data, FILE_APPEND | LOCK_EX)
                : false;
    }

    /**
     * Delete the file
     * @return bool
     */
    public function delete()
    {
        return ($file = $this->realpath()) ? unlink($file) : false;
    }

}