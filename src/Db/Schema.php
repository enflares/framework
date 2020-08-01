<?php
namespace enflares\Db;

use enflares\System\Module;

/**
 * Class Schema
 * @package enflares\Db
 */
class Schema extends Module
{
    /**
     * Creates a database
     * @param $name
     * @return mixed
     */
    public static function createDatabase($name)
    {
        $sql = 'CREATE DATABASE `'.$name.'`';
        return static::execute($sql);
    }

    /**
     * Creates a data table
     * @param $name
     * @param null $prefix
     * @return SchemaTable
     */
    public static function createTable($name, $prefix=NULL)
    {
        return new SchemaTable($name, $prefix);
    }

    /**
     * Migrates the data structure with/without data
     * @return array
     */
    public static function migrate()
    {
        $root = map('database', 'factory');

        $results = [];

        foreach( glob($root . '/*Factory.php') as $file ) {

            $class = basename($file, '.php');          
            include_once( $file );
            
            if( is_subclass_of($class, SchemaFactory::class) ) {
                $results[$class] = $class::install();
            }else{
                $results[$class] = $class . ' is not a valid factory';
            }
        }

        return $results;
    }
}