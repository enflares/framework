<?php
namespace enflares\Db;

use enflares\System\Module;
use enflares\System\InvalidException;
use enflares\System\NotFoundException;

class SchemaTable extends Module
{
    const DEFAULT_COLLATION = 'utf8_unicode_ci';
    const DEFAULT_ENGINE = 'MyISAM';

    protected $name;
    protected $prefix;
    protected $collation;
    protected $engine;
    protected $comment;

    private $__fields = [];
    private $__primary = [];
    private $__uniques = [];
    private $__indexes = [];
    private $__fullTexts = [];

    private $__relations = [];

    public function __construct($name, $prefix=NULL, $collation=NULL, $engine=NULL, $comment=NULL)
    {
        parent::__construct();

        $this->name($name);
        $this->prefix($prefix);
        $this->collation = $collation;
        $this->engine = $engine;
        $this->comment = $comment;
    }

    public function relations() 
    {
        return $this->__relations;
    }

    /**
     * @param $factory
     * @return $this|void
     * @throws \enflares\System\Exception
     */
    public function hasMany($factory)
    {
        if( !is_subclass_of($factory, SchemaFactory::class) ) {
            return InvalidException::trigger('The first argument must be one subclass of SchemaFactory');
        }

        if( !in_array($factory, $this->__relations) ) {
            $this->__relations [] = $factory;
        }

        return $this;
    }

    public function columns()
    {
        return $this->__fields;
    }
    
    public function name($value=NULL)
    {
        if( func_num_args() ) {
            $this->name = strtr(strtolower(trim($value)), '-', '_');
            return $this;
        }else{
            return $this->name;
        }
    }

    public function prefix($value=NULL)
    {
        if( func_num_args() ) {
            $this->prefix = strtr(strtolower(trim($value)), '-', '_');
            return $this;
        }else{
            return $this->prefix;
        }
    }

    public function collation($value=NULL)
    {
        if( func_num_args() ) {
            $this->collation = $value;
            return $this;
        }else{
            return $this->collation ?: static::DEFAULT_COLLATION;
        }
    }

    public function engine($value=NULL)
    {
        if( func_num_args() ) {
            $this->engine = $value;
            return $this;
        }else{
            return $this->engine ?: static::DEFAULT_ENGINE;
        }
    }

    public function comment($value=NULL)
    {
        if( func_num_args() ) {
            $this->comment = $value;
            return $this;
        }else{
            return $this->name;
        }
    }

    public function isRelation()
    {
        return 1!==count($this->__primary);
    }

    /**
     * Create a column
     *
     * @param string $column
     * @param string $type
     * @param int|string|null $length
     * @param bool|null $nullable
     * @param mixed $default
     * @param string|null $collation
     * @param string|null $attributes
     * @param bool|null $autoIncrement
     * @param string|null $comment
     * @return SchemaField
     */
    public function createField($column, $type, $length=NULL, $nullable=NULL, $default=NULL, $comment=NULL)
    {
        return ($this->__fields[$column = str_replace('@', $this->prefix.'_', strtolower($column))] 
                    = new SchemaField($column, $type, $length, $nullable, $default, 
                    is_null($comment) ? ucwords(str_replace('@', $this->prefix, strtr($column, '_', ' '))) : $comment ))
                    ->table($this);
    }

    //// Primary Key ////

    /**
     * Adds a column to the primary key
     *
     * @param SchemaField|string $field
     * @return SchemaField|void
     * @throws \enflares\System\Exception
     */
    public function createPrimary($field)
    {
        $key = strtolower((string)$field);
        if( isset($this->__fields[$key]) ) {
            $this->__primary[] = $key;
            $this->__primary = array_unique($this->__primary);
            return $this->__fields[$key];
        }

        return NotFoundException::trigger('Column "%s" is not created yet', $key);
    }

    /**
     * Removes a column off the primary key
     *
     * @param SchemaField|string $field
     * @return $this
     */
    public function removePrimary($field)
    {
        $key = strtolower((string)$field);
        unset( $this->__primary[array_search($key, $this->__primary)] );
        return $this;
    }

    /**
     * Checks if a column is the primary key or a part of the primary key
     *
     * @param SchemaField|string $field
     * @return boolean
     */
    public function isPrimary($field)
    {
        return in_array(strtolower((string)$field), $this->__primary);
    }

    //// Uniques ////

    /**
     * Adds a column to a unique index
     *
     * @param string $name
     * @param SchemaField|string $field
     * @return SchemaField|void
     * @throws \enflares\System\Exception
     */
    public function createUnique($name, $field) 
    {
        $key = strtolower((string)$field);
        if( isset($this->__fields[$key]) ) {
            $this->__uniques[$name][] = $key;  
            $this->__uniques[$name] = array_unique($this->__uniques[$name]);
            return $this->__fields[$key];
        }

        return NotFoundException::trigger('Column "%s" is not created yet', $key); 
    }

    /**
     * Removes a column off a unique index or all unique indexes
     * 
     * @param SchemaField|string $field
     * @param string|null $name
     * @return $this
     */
    public function removeUnique($field, $name=NULL) 
    {
        $key = strtolower((string)$field);

        if( $name ) {
            if( isset($this->__uniques[$name]) ) {
                unset( $this->__uniques[$name][array_search($key, $this->__uniques[$name])] );
            }
        }else{
            foreach( $this->__uniques as $name=>$unique ) {
                unset( $this->__unique[$name][array_search($key, $unique)] );
            }
        }

        return $this;
    }

    /**
     * Checks if a column is within one or any unique index
     *
     * @param SchemaField|string $field
     * @param string|null $name
     * @return boolean
     */
    public function isUnique($field, $name=NULL) 
    {
        $key = strtolower((string)$field);

        if( $name ) {
            return isset($this->__uniques[$name]) 
                    && in_array($key, $this->__uniques[$name]);
        }
        
        foreach( $this->__uniques as $name=>$unique ) {
            if( in_array($key, $unique) ) return TRUE;
        }

        return FALSE;
    }


    //// Indexes ////

    /**
     * Adds a column to an index
     *
     * @param string $name
     * @param SchemaField|string $field
     * @return SchemaField|void
     * @throws \enflares\System\Exception
     */
    public function createIndex($name, $field) 
    {
        $key = strtolower((string)$field);
        if( isset($this->__fields[$key]) ) {
            $this->__indexes[$name][] = $key;  
            $this->__indexes[$name] = array_unique($this->__indexes[$name]);
            return $this->__fields[$key];
        }

        return NotFoundException::trigger('Column "%s" is not created yet', $key); 
    }

    /**
     * Removes a column off an index or all indexes
     * 
     * @param SchemaField|string $field
     * @param string|null $name
     * @return $this
     */
    public function removeIndex($field, $name=NULL) 
    {
        $key = strtolower((string)$field);

        if( $name ) {
            if( isset($this->__indexes[$name]) ) {
                unset( $this->__indexes[$name][array_search($key, $this->__indexes[$name])] );
            }
        }else{
            foreach( $this->__indexes as $name=>$indexes ) {
                unset( $this->__indexes[$name][array_search($key, $indexes)] );
            }
        }

        return $this;
    }

    /**
     * Checks if a column is within one or any index
     *
     * @param SchemaField|string $field
     * @param string|null $name
     * @return boolean
     */
    public function isIndex($field, $name=NULL) 
    {
        $key = strtolower((string)$field);

        if( $name ) {
            return isset($this->__indexes[$name]) 
                    && in_array($key, $this->__indexes[$name]);
        }
        
        foreach( $this->__indexes as $name=>$indexes ) {
            if( in_array($key, $indexes) ) return TRUE;
        }

        return FALSE;
    }


    //// Full Text Indexes ////

    /**
     * Adds a column to a full-text index
     *
     * @param string $name
     * @param SchemaField|string $field
     * @return SchemaField|void
     * @throws \enflares\System\Exception
     */
    public function createFullText($name, $field) 
    {
        $key = strtolower((string)$field);
        if( isset($this->__fields[$key]) ) {
            $this->__fullTexts[$name][] = $key;  
            $this->__fullTexts[$name] = array_unique($this->__fullTexts[$name]);
            return $this->__fields[$key];
        }

        return NotFoundException::trigger('Column "%s" is not created yet', $key); 
    }

    /**
     * Removes a column off a full-text index or all full-text indexes
     * 
     * @param SchemaField|string $field
     * @param string|null $name
     * @return $this
     */
    public function removeFullText($field, $name=NULL) 
    {
        $key = strtolower((string)$field);

        if( $name ) {
            if( isset($this->__fullTexts[$name]) ) {
                unset( $this->__fullTexts[$name][array_search($key, $this->__fullTexts[$name])] );
            }
        }else{
            foreach( $this->__fullTexts as $name=>$fullTexts ) {
                unset( $this->__fullTexts[$name][array_search($key, $fullTexts)] );
            }
        }

        return $this;
    }

    /**
     * Checks if a column is within one or any full-text index
     *
     * @param SchemaField|string $field
     * @param string|null $name
     * @return boolean
     */
    public function isFullText($field, $name=NULL) 
    {
        $key = strtolower((string)$field);

        if( $name ) {
            return isset($this->__fullTexts[$name]) 
                    && in_array($key, $this->__fullTexts[$name]);
        }
        
        foreach( $this->__fullTexts as $name=>$indexes ) {
            if( in_array($key, $indexes) ) return TRUE;
        }

        return FALSE;
    }

    //// Create Fields ////
    
    /**
     * Create the auto increment field, and it must be the primary key
     * Default length: 20
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createAutoIncrement($column=NULL, $comment=NULL)
    {
        return $this->createPrimary(
            $this->createField($column ?: 'id', 'bigint', 20, NULL, NULL, $comment ?: 'Primary key')
                 ->unsigned(TRUE)
                 ->autoIncrement(TRUE)
        );
    }

    /**
     * Create bit type column
     * Length is from 1 (default) to 64
     * @param string $column
     * @param int $length
     * @param string|null $comment
     * @return SchemaField
     */
    public function createBit($column, $length=NULL, $comment=NULL)
    {
        return $this->createIndex(
            'default',
            $this->createField($column, 'bit', min(64, max(1, intval($length))), FALSE, 0, $comment)
                 ->unsigned(TRUE)
        );
    }

    /**
     * Create boolean type column
     * Default length: 1
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createBoolean($column, $comment=NULL)
    {
        return $this->createIndex(
            'default',
            $this->createField($column, 'int', 1, TRUE, NULL, $comment)
                 ->unsigned(TRUE)
        );
    }

    /**
     * Create int type column
     * Default length: 11
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createInt($column, $comment=NULL)
    {
        return $this->createIndex(
            'default',
            $this->createField($column, 'int', 11, FALSE, 0, $comment)
        );
    }

    /**
     * Create TinyInt type column
     * Default length: 2
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createTinyInt($column, $comment=NULL)
    {
        return $this->createIndex(
            'default',
            $this->createField($column, 'tinyint', 2, FALSE, 0, $comment)
        );
    }

    /**
     * Create small int type column
     * Default length: 4
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createSmallInt($column, $comment=NULL)
    {
        return $this->createIndex(
            'default',
            $this->createField($column, 'smallint', 4, FALSE, 0, $comment)
        );
    }

    /**
     * Create medium int type column
     * Default length: 8
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createMediumInt($column, $comment=NULL)
    {
        return $this->createIndex(
            'default',
            $this->createField($column, 'mediumint', 8, FALSE, 0, $comment)
        );
    }

    /**
     * Create bit int type column
     * Default length: 20
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createBigInt($column, $comment=NULL)
    {
        return $this->createIndex(
            'default',
            $this->createField($column, 'bigint', 20, FALSE, 0, $comment)
        );
    }

    /**
     * Create float type column
     * Default length: 16,2
     * @param string $column
     * @param int $decimal
     * @param string|null $comment
     * @return SchemaField
     */
    public function createFloat($column, $decimal=NULL, $comment=NULL)
    {
        return $this->createIndex(
            'default',
            $this->createField($column, 'float', '16,'.(intval($decimal)?:2), FALSE, 0.0, $comment)
        );
    }

    /**
     * Create double type column
     * Default length: 16,3
     * @param string $column
     * @param int $decimal
     * @param string|null $comment
     * @return SchemaField
     */
    public function createDecimal($column, $decimal=NULL, $comment=NULL)
    {
        return $this->createIndex(
            'default',
            $this->createField($column, 'decimal', '16,'.(intval($decimal)?:3), FALSE, 0.0, $comment)
        );
    }

    /**
     * Create decimal type column
     * Default length: 16,4
     * @param string $column
     * @param int $decimal
     * @param string|null $comment
     * @return SchemaField
     */
    public function createDouble($column, $decimal=NULL, $comment=NULL)
    {
        return $this->createIndex(
            'default',
            $this->createField($column, 'double', '16,'.(intval($decimal)?:4), FALSE, 0.0, $comment)
        );
    }

    /**
     * Create real type column
     * Default length: 16,8
     * @param string $column
     * @param int $decimal
     * @param string|null $comment
     * @return SchemaField
     */
    public function createReal($column, $decimal=NULL, $comment=NULL)
    {
        return $this->createIndex(
            'default',
            $this->createField($column, 'real', '16,'.(intval($decimal)?:8), FALSE, 0.0, $comment)
        );
    }
    
    /**
     * Create date type column
     *
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createDate($column, $comment=NULL)
    {
        return $this->createIndex(
            'default',
            $this->createField($column, 'date', NULL, TRUE, NULL, $comment)
        );        
    }

    /**
     * Create datetime type column
     *
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createDateTime($column, $comment=NULL)
    {
        return $this->createIndex(
            'default',
            $this->createField($column, 'datetime', NULL, FALSE, NULL, $comment)
        );        
    }
    
    /**
     * Create time type column
     *
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createTime($column, $comment=NULL)
    {
        return $this->createIndex(
            'default',
            $this->createField($column, 'time', NULL, FALSE, NULL, $comment)
        );        
    }

    /**
     * Create year type column
     * Default length: 4
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createYear($column, $comment=NULL)
    {
        return $this->createIndex(
            'default',
            $this->createField($column, 'year', 4, FALSE, NULL, $comment)
        );        
    }

    /**
     * Create timestamp type column
     *
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createTimestamp($column, $comment=NULL)
    {
        return $this->createIndex(
            'default',
            $this->createField($column, 'timestamp', NULL, FALSE, NULL, $comment)
        );        
    }

    /**
     * Create char type column
     *
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createChar($column, $length, $comment=NULL)
    {
        return $this->createField($column, 'char', intval($length), NULL, NULL, $comment)
                    ->collation($this->collation());
    }

    /**
     * Create varchar type column
     *
     * @param string $column
     * @param int $length
     * @param bool $mb4
     * @param string|null $comment
     * @return SchemaField
     */
    public function createString($column, $length, $mb4=NULL, $comment=NULL)
    {
        if( is_null($mb4) ) $collation = $this->collation();
        else $collation = $mb4 ? 'utf8mb4_general_ci' : 'utf8_general_ci';

        return $this->createField($column, 'varchar', intval($length), NULL, NULL, $comment)
                    ->collation($collation);
    }

    /**
     * Create text type column
     *
     * @param string $column
     * @param bool $mb4
     * @param string|null $comment
     * @return SchemaField
     */
    public function createText($column, $mb4=NULL, $comment=NULL)
    {
        if( is_null($mb4) ) $collation = $this->collation();
        else $collation = $mb4 ? 'utf8mb4_general_ci' : 'utf8_general_ci';

        return $this->createField($column, 'text', NULL, TRUE, NULL, $comment)
                    ->collation($collation);
    }

    /**
     * Create tinytext type column
     *
     * @param string $column
     * @param bool $mb4
     * @param string|null $comment
     * @return SchemaField
     */
    public function createTinyText($column, $mb4=NULL, $comment=NULL)
    {
        if( is_null($mb4) ) $collation = $this->collation();
        else $collation = $mb4 ? 'utf8mb4_general_ci' : 'utf8_general_ci';

        return $this->createField($column, 'tinytext', NULL, TRUE, NULL, $comment)
                    ->collation($collation);
    }

    /**
     * Create mediumtext type column
     *
     * @param string $column
     * @param bool $mb4
     * @param string|null $comment
     * @return SchemaField
     */
    public function createMediumText($column, $mb4=NULL, $comment=NULL)
    {
        if( is_null($mb4) ) $collation = $this->collation();
        else $collation = $mb4 ? 'utf8mb4_general_ci' : 'utf8_general_ci';

        return $this->createField($column, 'mediumtext', NULL, TRUE, NULL, $comment)
                    ->collation($collation);
    }

    /**
     * Create longtext type column
     *
     * @param string $column
     * @param bool $mb4
     * @param string|null $comment
     * @return SchemaField
     */
    public function createLongText($column, $mb4=NULL, $comment=NULL)
    {
        if( is_null($mb4) ) $collation = $this->collation();
        else $collation = $mb4 ? 'utf8mb4_general_ci' : 'utf8_general_ci';

        return $this->createField($column, 'longtext', NULL, TRUE, NULL, $comment)
                    ->collation($collation);
    }

    /**
     * Create binary type column
     *
     * @param string $column
     * @param int $length
     * @param bool $mb4
     * @param string|null $comment
     * @return SchemaField
     */
    public function createBinary($column, $length, $mb4=NULL, $comment=NULL)
    {
        if( is_null($mb4) ) $collation = $this->collation();
        else $collation = $mb4 ? 'utf8mb4_general_ci' : 'utf8_general_ci';

        return $this->createField($column, 'binary', intval($length), NULL, NULL, $comment)
                    ->collation(str_replace('_general_ci', '_bin', $collation))
                    ->binary(TRUE);
    }

    /**
     * Create varbinary type column
     *
     * @param string $column
     * @param int $length
     * @param bool $mb4
     * @param string|null $comment
     * @return SchemaField
     */
    public function createVarBinary($column, $length, $mb4=NULL, $comment=NULL)
    {
        if( is_null($mb4) ) $collation = $this->collation();
        else $collation = $mb4 ? 'utf8mb4_general_ci' : 'utf8_general_ci';

        return $this->createField($column, 'varbinary', intval($length), NULL, NULL, $comment)
                    ->collation($collation)
                    ->binary(TRUE);
    }

    /**
     * Create blob type column
     *
     * @param string $column
     * @param bool $mb4
     * @param string|null $comment
     * @return SchemaField
     */
    public function createBlob($column, $mb4=NULL, $comment=NULL)
    {
        if( is_null($mb4) ) $collation = $this->collation();
        else $collation = $mb4 ? 'utf8mb4_general_ci' : 'utf8_general_ci';

        return $this->createField($column, 'blob', NULL, TRUE, NULL, $comment)
                    ->collation($collation)
                    ->binary(TRUE);
    }

    /**
     * Create tinyblob type column
     *
     * @param string $column
     * @param bool $mb4
     * @param string|null $comment
     * @return SchemaField
     */
    public function createTinyBlob($column, $mb4=NULL, $comment=NULL)
    {
        if( is_null($mb4) ) $collation = $this->collation();
        else $collation = $mb4 ? 'utf8mb4_general_ci' : 'utf8_general_ci';

        return $this->createField($column, 'tinyblob', NULL, TRUE, NULL, $comment)
                    ->collation($collation)
                    ->binary(TRUE);
    }

    /**
     * Create mediumblob type column
     *
     * @param string $column
     * @param bool $mb4
     * @param string|null $comment
     * @return SchemaField
     */
    public function createMediumBlob($column, $mb4=NULL, $comment=NULL)
    {
        if( is_null($mb4) ) $collation = $this->collation();
        else $collation = $mb4 ? 'utf8mb4_general_ci' : 'utf8_general_ci';

        return $this->createField($column, 'mediumblob', NULL, TRUE, NULL, $comment)
                    ->collation($collation)
                    ->binary(TRUE);
    }

    /**
     * Create longblob type column
     *
     * @param string $column
     * @param bool $mb4
     * @param string|null $comment
     * @return SchemaField
     */
    public function createLongBlob($column, $mb4=NULL, $comment=NULL)
    {
        if( is_null($mb4) ) $collation = $this->collation();
        else $collation = $mb4 ? 'utf8mb4_general_ci' : 'utf8_general_ci';

        return $this->createField($column, 'longblob', NULL, TRUE, NULL, $comment)
                    ->collation($collation)
                    ->binary(TRUE);
    }

    /**
     * Create enum type column
     *
     * @param string $column
     * @param array $values
     * @param string|null $comment
     * @return SchemaField
     */
    public function createEnum($column, Array $values, $comment=NULL)
    {
        return $this->createIndex(
            'default',
            $this->createField($column, 'enum', trim(json_encode($values), '{}'), NULL, NULL, $comment)
        );
    }
    /**
     * Create set type column
     *
     * @param string $column
     * @param array $values
     * @param string|null $comment
     * @return SchemaField
     */
    public function createSet($column, Array $values, $comment=NULL)
    {
        return $this->createField($column, 'set', trim(json_encode($values), '{}'), NULL, NULL, $comment);
    }

    /**
     * Create password type column
     * Default length: 32
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createPassword($column, $comment=NULL)
    {
        return $this->createField($column, 'char', 32, TRUE, NULL, $comment)
                    ->collation('ascii_general_ci');
    }

    /**
     * Create email type column
     * Default length: 320
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createEmail($column, $comment=NULL)
    {
        return $this->createField($column, 'varchar', 320, TRUE, NULL, $comment)
                    ->collation('ascii_general_ci');
    }

    /**
     * Create url type column
     *
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createUrl($column, $comment=NULL)
    {
        return $this->createField($column, 'text', NULL, TRUE, NULL, $comment)
                    ->collation('ascii_general_ci');
    }

    /**
     * Create auto time column
     *
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createAutoTime($column, $comment=NULL)
    {
        return $this->createInt($column, $comment);
    }
    
    /**
     * Create IPv4 column
     * Default length: 15
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createIP($column, $comment=NULL)
    {
        return $this->createIndex(
                'default',
                $this->createChar($column, 15, $comment)
                     ->collation('ascii_general_ci')
        );
    }

    /**
     * Create IPv6 column
     * Default length: 39
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createIPv6($column, $comment=NULL)
    {
        return $this->createIndex(
                'default',
                $this->createChar($column, 39, $comment)
                     ->collation('ascii_general_ci')
        );
    }
    
    /**
     * Create phone number column
     * Default length: 20
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createPhoneNumber($column, $comment=NULL)
    {
        return $this->createIndex(
                'default',
                $this->createChar($column, 20, $comment)
                     ->collation('ascii_general_ci')
        );
    }
    
    /**
     * Create JSON column
     *
     * @param string $column
     * @param string|null $comment
     * @return SchemaField
     */
    public function createJSON($column, $comment=NULL)
    {
        return $this->createLongText($column, TRUE, $comment)->binary(TRUE);
    }

    /**
     * Commit the change of schema
     *
     * @param array $seeds
     * @return mixed
     * @throws \enflares\System\Exception
     */
    public function commit(Array $seeds=NULL)
    {
        $table = $this->name();
        if( !$table ) return InvalidException::trigger('No table name provided');


        $db = static::db();
        $columns = $this->fetchColumns() ?: [];

        $creates = [];
        $updates = [];
        $deletes = [];

        if( $isNewTable = !count($columns) ) {
            $creates = array_keys($this->__fields);
        } else {
            foreach( $this->__fields as $field=>$config ) {
                if( isset($columns[$field]) ) {
                    $updates[] = $field;
                }else{
                    $creates[] = $field;
                }
            }

            foreach( $columns as $column=>$config ) {
                if( !isset($this->__fields[$column]) ) {
                    $deletes[] = $column;
                }
            }

            if( !count($creates) && !count($updates) ) {
                return InvalidException::trigger('No valid columns committed to the Schema table "%s"', $table);
            }

            foreach( $deletes as $field ) {
                $this->removePrimary($field);
                $this->removeUnique($field);
                $this->removeIndex($field);
                $this->removeFullText($field);
            }
        }

        $results = [];
        
        try{
            $s = [];

            if( count($creates) ) {
                if( !count($columns) ) {
                    foreach( $creates as $field ) {
                        $s[] = "`$field` " . $this->__fields[$field]->explain($this);
                    }

                    $sql = 'CREATE'.' TABLE IF NOT EXISTS `#_'.$table.'` (' . implode(',', $s)
                            .') ENGINE '.$this->engine()
                            .' DEFAULT COLLATE '.$this->collation()
                            .' COMMENT '.json_encode($this->comment ?: '');

                    if( !$db->execute($sql) ) $db->throwError();
                    
                    $results[] = "Table $this->name is installed";

                    $s = [];

                    if( count($this->__primary) && (strpos($sql, ' PRIMARY KEY AUTO_INCREMENT')===FALSE) ) {
                        $s[] = 'ADD PRIMARY KEY (`'.implode('`,`', $this->__primary).'`)';
                    }
                }else{
                    foreach( $creates as $field ) {
                        $s[] = "ADD `$field` " . $this->__fields[$field]->explain($this);
                    }
                    
                    //if( count($this->__primary) ) {
                        //$s[] = 'CHANGE PRIMARY KEY (`'.implode('`,`', $this->__primary).'`)';
                    //}
                }
            }

            foreach( $deletes as $column ) {
                $s[] = "DROP `$column`";
            }

            foreach( $updates as $column ) {
                $s[] = "CHANGE `$column` `$column` ".str_replace(' PRIMARY KEY', '', $this->__fields[$column]->explain($this));
                //$s[] = "CHANGE `$column` `$column` ".$this->__fields[$column]->explain($this);
            }

            foreach( $this->__uniques as $name=>$indexes ) {
                if( count($indexes) ) {
                    $s[] = "DROP'.' INDEX IF EXISTS `ix-$name`";
                    $s[] = "ADD INDEX `ix-$name` (`".implode('`,`', $indexes).'`)';
                }
            }

            foreach( $this->__indexes as $name=>$indexes ) {
                if( count($indexes) ) {
                    $s[] = "DROP KEY IF EXISTS `uq-$name`";
                    $s[] = "ADD UNIQUE `uq-$name` (`".implode('`,`', $indexes).'`)';
                }
            }

            foreach( array_keys($this->__fullTexts) as $n ) {
                // Can only contain fields with the same collation within on FULL TEXT index
                $sets = [];
                foreach( $this->__fullTexts[$n] as $field ) {
                    $column = $this->__fields[$field];
                    $sets[ trim($n.'_'.$column->collation(), '_') ][] = $field;
                }

                foreach( $sets as $name=>$indexes ) {
                    if( count($indexes) ) {
                        $s[] = "DROP'.' INDEX IF EXISTS `fx-$name`";
                        $s[] = "ADD FULLTEXT `fx-$name` (`".implode('`,`', $indexes).'`)';
                    }
                }
            }
            
            if( count($s) ) {
                $sql = "ALTER'.' TABLE #_{$this->name} ".implode(',', $s);
                if( !$db->execute($sql) ) $db->throwError();
            }

            $db->commit();
            $results[] = "Table $this->name is upgraded";
            
            if( $isNewTable && $seeds ) {
                $db->columns($this->name, NULL, NULL, NULL, TRUE);
                foreach( $seeds as $rs ) {
                    $data = [];
                    foreach( $rs as $key=>$value ) {
                        $data[str_replace('@', $this->prefix.'_', $key)] = $value;
                    }
                    if( count($data) ) {
                        try{
                            $db->insert($this->name, $data);
                            debugX(db());
                        } catch(\Exception $ex) {
                            debugX($ex);
                        }
                    }else{
                        debug($this->name);
                    }
                }
            }
        }catch(\Exception $ex){
            $db->rollback();

            $results[] = "Table $this->name is installed/upgraded unsuccessful";
            $results[] = "Error: " . $ex->getMessage();
        }

        return $results;
    }

    protected function fetchColumns()
    {
        try{
            return static::db()->columns($this->name());
        }catch(\Exception $ex){
            return [];
        }
    }
}