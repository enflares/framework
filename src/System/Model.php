<?php
namespace enflares\System;

use BadMethodCallException;
use enflares\Db\Db;
use enflares\Db\Query;
use enflares\Db\ResultSet;

/**
 * Class Model
 * @package enflares\System
 */
class Model extends Data implements EntityInterface
{
    /**
     * The table name
     */
    const ENTITY_NAME = NULL;

    /**
     * The prefix of field
     */
    const ENTITY_PREFIX = NULL;

    /**
     * The primary key name, mostly "id"
     */
    const ENTITY_PRIMARY_KEY = NULL;

    /**
     * @return Db
     */
    public static function db()
    {
        return Db::getInstance();
    }

    /**
     * Gets the entity table name
     * @return mixed|null
     */
    public static function entity_name()
    {
        if( static::ENTITY_NAME ) return static::ENTITY_NAME;

        static $g = array();
        $class = static::class;
        if( !isset($g[$class]) ) {
            $prefix = '\\Model\\';
            $pos = strpos($class, $prefix);
            if( $pos!==FALSE ) $class = substr($class, $pos+7);
            $g[$class] = strtolower(preg_replace('/\W+/', '_', $class));
        }

        return $g[$class];
    }

    /**
     * Formats the field name
     * @param $field
     * @return string
     */
    public static function entity_field($field)
    {
        $fields = get_class_vars(static::class);
        $field = strtolower($field);
        if( array_key_exists($field, $fields) ) return $field;

        if( !($prefix = static::ENTITY_PREFIX) ) {
            $prefix = explode('_', static::entity_name());
            $prefix = end($prefix);
        }

        if( $prefix && array_key_exists($name = $prefix . '_'. $field, $fields) )
            return $name;

        return NULL;
    }

    /**
     * Gets the field name of primary key
     * @return string
     */
    public static function entity_primary_key()
    {
        return static::ENTITY_PRIMARY_KEY ?: static::entity_field('id');
    }

    /**
     * @param $item
     * @param bool $destroy
     * @return mixed
     */
    public static function buffer($item, $destroy=NULL)
    {
        static $g = array();

        if( is_array($item) ) $item = new static($item);
        if( $item instanceof static) $g[$id = $item->id()] = $item;
        elseif( is_int($item) ) $id = $item;
        else return NULL;

        if( $destroy ) unset($g[$id]);
        else return $g[$id];

        return NULL;
    }

    /**
     * @param null $criteria
     * @param null $ordering
     * @param null $index
     * @return mixed
     * @throws Exception
     */
    public static function find($criteria=NULL, $ordering=NULL, $index=NULL)
    {
        if( is_int($criteria) ){
            if( $data = static::buffer($criteria) ) return $data;
            $criteria = [ static::entity_primary_key()=>$criteria];
        }

        if( $data = static::search($criteria, $ordering, 1, $index)->fetch() )
            return static::buffer($data);
        return NULL;
    }

    /**
     * @param null $criteria
     * @param null $ordering
     * @param null $limit
     * @param null $index
     * @param null $fieldList
     * @param null $joins
     * @return ResultSet
     * @throws Exception
     */
    public static function search($criteria=NULL, $ordering=NULL, $limit=NULL, $index=NULL, $fieldList=NULL, $joins=NULL)
    {
        $table = static::entity_name();
        if( $joins ) $table = '#_'. $table . ' ' . $joins;
        
        // ignore the deleted records by default( if not exists $criteria['recycled'] )
        // show only the recycled records if $criteria['recycled'] = TRUE
        // show all records if $criteria['recycled'] = FALSE

        if( $delete = static::entity_field('deleted_at') )
        {
            $time = time();
            if( isset($criteria['recycled']) && $criteria['recycled'] ) 
                $criteria[] = "IF($delete, $delete<$time, 0)";
            elseif( !isset($criteria['recycled']) )
                $criteria[] = "IF($delete, $delete>=$time, 1)";
        }
        unset($criteria['recycled']);
        
        return static::db()->search($table, $fieldList, $criteria, NULL, $ordering, $limit, $index);
    }

    /**
     * @param null $fields
     * @return Query
     */
    public static function select($fields=NULL){
        $fields = implode(',', func_get_args()) ?: '*';
        return static::db()->table(static::entity_name(), $fields, static::entity_primary_key());
    }

    /**
     * Insert a record
     * @param $item
     * @return mixed
     * @throws Exception
     * @throws \enflares\Db\Exception
     */
    public static function create($item)
    {
        if( is_array($item) ) $item = new static($item);
        $table=static::entity_name();
        if( $item instanceof static ) {
            if( !$item->createdAt() ) $item->createdAt(time());
            $data = $item->__toArray();
            unset( $data[$primaryKey=static::entity_primary_key()] );
            if( $id = static::db()->insert($table, $data) ) $item->id($id);
            return static::buffer($item);
        }

        return InvalidException::trigger('Invalidate argument to create a record of model: %s', $table);
    }

    /**
     * Update a record
     * @param $item
     * @param array|NULL $fields
     * @param null $criteria
     * @param array|NULL $args
     * @param array|string|NULL $ordering
     * @param int|NULL $limit
     * @param int|NULL $index
     * @return int|void
     * @throws Exception
     * @throws \enflares\Db\Exception
     */
    public static function update($item, Array $fields=NULL, $criteria=NULL, Array $args=NULL, $ordering=NULL, $limit=NULL, $index=NULL)
    {
        $table = static::entity_name();
        $primaryKey = static::entity_primary_key();
        $criteria = (array)$criteria;

        if( is_array($item) ) $item = new static($item);

        if( is_int($item) ) {
            $criteria[$primaryKey] = $item;
            $item = NULL;
        }elseif( $item instanceof static ){
            $criteria[$primaryKey] = $item->id();
            if( !$fields ) $fields = $item->__toArray();
        }else{
            // $fields = array();
            return InvalidException::trigger('Invalidate argument to create a record of model: %s', $table);
        }

        if( count($fields) ){
            $createdAt = static::entity_field('created_at');
            $updatedAt = static::entity_field('updated_at');

            unset($fields[$createdAt]);
            unset($fields[$primaryKey]);
            if( $updatedAt && !isset($fields[$updatedAt]) && !$fields[$updatedAt]) $fields[$updatedAt] = time();

            return static::db()->update($table, $fields, $criteria, $args, $ordering, $limit, $index);
        }
    }

    /**
     * Delete a record permanently
     * @param $item
     * @param null $criteria
     * @param array|NULL $args
     * @param array|string|NULL $ordering
     * @param int|NULL $limit
     * @param int|NULL $index
     * @return int|void
     * @throws Exception
     * @throws \enflares\Db\Exception
     */
    public static function delete($item, $criteria=NULL, Array $args=NULL, $ordering=NULL, $limit=NULL, $index=NULL)
    {
        $table = static::entity_name();
        $primaryKey = static::entity_primary_key();
        $criteria = (array)$criteria;

        if( is_int($item) ) $criteria[$primaryKey] = $item;
        elseif( is_array($item) ) $criteria = $item;
        elseif( ($item instanceof static) && ($id=$item->id()) )  $criteria[$primaryKey] = $id;
        else return InvalidException::trigger('Invalidate argument to delete a record of model: %s', $table);

        return static::db()->delete($table, $criteria, $args, $ordering, $limit, $index);
    }

    /**
     * @param $name
     * @param $arguments
     * @return ResultSet|void
     * @throws Exception
     */
    public static function __callStatic($name, $arguments)
    {
        if( !strncasecmp($name, 'findBy', 6) ) {
            if( $field = static::entity_field(substr($name, 6)) ) {
                $arguments[0] = array($field=>$arguments[0]);
                return static::find(...$arguments);
            }

            return InvalidException::trigger('Invalid property: %s', $field);
        }

        if( !strncasecmp($name, 'searchBy', 8) ) {
            if( $field = static::entity_field(substr($name, 8)) ) {
                $arguments[0] = array($field=>$arguments[0]);
                return static::search(...$arguments);
            }
            return InvalidException::trigger('Invalid property: %s', $field);
        }

        throw new BadMethodCallException('Call to undefined method: %s', static::class.'::'.$name);
    }


    //////////////////////////////////////////////////////////////////////////////////////////////////

    use ConfigJsonTrait;

    public function __get($key)
    {
        return parent::__get(static::entity_field($key) ?: $key);
    }

    public function __set($key, $value)
    {
        return parent::__set(static::entity_field($key) ?: $key, $value);
    }

    public function __isset($key)
    {
        return !!static::entity_field($key);
    }

    public function __unset($key)
    {
        parent::__unset(static::entity_field($key) ?: $key);
    }

    /**
     * Return the database record in an array
     * @return array
     */
    public function __toArray()
    {
        return get_object_vars($this);
    }

    /**
     * Return the major values of this model
     * @return array
     */
    public function toCoreArray()
    {
        $prefix = (static::ENTITY_PREFIX ?: static::entity_name()).'_';
        $len = strlen($prefix);

        $result = array();
        foreach( get_object_vars($this) as $key=>$value )
        {
            if( !is_null($value) )
                if (!strncasecmp($key, $prefix, $len))
                    $result[substr($key, $len)] = $value;
        }

        return $result;
    }

    /**
     * Get/Set the primary key value
     * @return int|Model
     */
    public function id()
    {
        if( func_num_args() ){
            $id = intval(func_get_arg(0));
            $this->__set('id', max(0, intval($id)));
            return $this;
        }

        if( $field = static::entity_field('id') )
            return max(0, intval($this->__get($field)));

        return NULL;
    }

    /**
     * Get/Set the creation time of the record
     * @param null $time
     * @return $this|mixed
     */
    public function createdAt($time=NULL){
        if( func_num_args() ){
            $this->__set('created_at', $time);
            return $this;
        }

        return $this->__get('created_at');
    }

    /**
     * Get/Set the modification time of the record
     * @param null $time
     * @return $this|mixed
     */
    public function updateAt($time=NULL){
        if( func_num_args() ){
            $this->__set('updated_at', $time);
            return $this;
        }

        return $this->__get('updated_at');
    }

    /**
     * Get/Set the recycled time
     * @param null $time
     * @return $this|mixed
     */
    public function deletedAt($time=NULL){
        if( func_num_args() ){
            $this->__set('deleted_at', $time);
            return $this;
        }

        return $this->__get('deleted_at');
    }
    

    /**
     * Format the creation time
     *
     * @param string $format
     * @return string
     */
    public function getCreatedTime($format=NULL)
    {
        if( $time = intval($this->__get('created_at')) )
            return date($format ?: env('LOCALE_DATETIME', 'Y-m-d H:i:s'), $time);

        return NULL;
    }

    /**
     * Format the modification time
     *
     * @param string $format
     * @return string
     */
    public function getUpdatedTime($format=NULL)
    {
        if( $time = intval($this->__get('created_at')) )
            return date($format ?: env('LOCALE_DATETIME', 'Y-m-d H:i:s'), $time);

        return NULL;
    }

    /**
     * Format the deletion time
     *
     * @param string $format
     * @return string
     */
    public function getDeletedTime($format=NULL)
    {
        if( $time = intval($this->__get('created_at')) )
            return date($format ?: env('LOCALE_DATETIME', 'Y-m-d H:i:s'), $time);

        return NULL;
    }

    /**
     * Save a record to the database either update it if exists, or create a new
     * @param array|NULL $data
     * @return int|mixed
     * @throws Exception
     * @throws \enflares\Db\Exception
     */
    public function save(Array $data=NULL)
    {
        if( $data ) {
            foreach ($data as $key => $value) {
                if ($field = static::entity_field($key))
                    $this->$field = $value;
            }
        }

        if( $id=$this->id() ) {
            $this->validate_update();
            if (static::update($this)) static::buffer($this);
        }else {
            $this->validate_create();
            if( static::create($this) ) static::buffer($this);
        }

        return $this;
    }

    /**
     * Remove a record either recycle it if possible, or otherwise delete it permanently
     * @return int
     * @throws Exception
     * @throws \enflares\Db\Exception
     */
    public function remove()
    {
        if( $field = static::entity_field('delete_at') ) {
            $time = time();
            return static::update($this, array($field=>$time), "IF($field,$field>$time,1)");
        }else{
            $this->validate_delete();
            return static::delete($this);
        }
    }

    public function validate(Array $validators)
    {
    }

    protected function validate_create()
    {
        return $this->validate([]);
    }

    protected function validate_update()
    {
        return $this->validate([]);
    }

    protected function validate_delete()
    {
    }
}