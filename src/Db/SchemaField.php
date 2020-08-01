<?php
namespace enflares\Db;

use enflares\System\Data;
use enflares\System\InvalidException;

/**
 * Class SchemaField
 * @package enflares\Db
 */
class SchemaField extends Data
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var string|number
     */
    protected $length;
    /**
     * @var string|number
     */
    protected $default;
    /**
     * @var string
     */
    protected $collation;
    /**
     * @var bool
     */
    protected $nullable;
    /**
     * @var bool
     */
    protected $autoIncrement;
    /**
     * @var bool
     */
    protected $unsigned;
    /**
     * @var bool
     */
    protected $binary;
    /**
     * @var string
     */
    protected $comment;

    /**
     * @var SchemaTable
     */
    protected $table;

    /**
     * SchemaField constructor.
     * @param String $name
     * @param $type
     * @param null $length
     * @param null $nullable
     * @param null $default
     * @param null $comment
     */
    public function __construct(String $name, $type, $length=NULL, $nullable=NULL, $default=NULL, $comment=NULL)
    {
        parent::__construct();

        $this->name($name);
        $this->type($type);
        $this->length = $length;
        $this->nullable = $nullable;
        $this->default = $default;
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "$this->name";
    }

    /**
     * @param SchemaTable|NULL $value
     * @return $this|SchemaTable
     */
    public function table(SchemaTable $value=NULL)
    {
        if( func_num_args() ) {
            $this->table = $value;
            return $this;
        }else{
            return $this->table;
        }
    }

    /**
     * @param null $value
     * @return $this|string
     */
    public function name($value=NULL)
    {
        if( func_num_args() ) {
            $this->name = strtolower(preg_replace('/\W+/', '_', $value));
            return $this;
        }else{
            return $this->name;
        }
    }

    /**
     * @param null $value
     * @return $this|string
     */
    public function type($value=NULL)
    {
        if( func_num_args() ) {
            $this->type = strtolower($value);
            return $this;
        }else{
            return $this->type;
        }
    }

    /**
     * @param null $value
     * @return $this|string
     */
    public function collation($value=NULL)
    {
        if( func_num_args() ) {
            $this->collation = $value;
            return $this;
        }else{
            return $this->collation;
        }
    }

    /**
     * @param null $value
     * @return $this|number|string|null
     */
    public function length($value=NULL)
    {
        if( func_num_args() ) {
            $this->length = $value;
            return $this;
        }else{
            return $this->length;
        }
    }

    /**
     * @param null $value
     * @return $this|bool|null
     */
    public function nullable($value=NULL)
    {
        if( func_num_args() ) {
            $this->nullable = $value;
            return $this;
        }else{
            return $this->nullable;
        }
    }

    /**
     * @param null $value
     * @return $this|number|string|null
     */
    public function default($value=NULL)
    {
        if( func_num_args() ) {
            $this->default = $value;
            if( !is_null($value) ) $this->nullable = NULL;
            return $this;
        }else{
            return $this->default;
        }
    }

    /**
     * @param null $value
     * @return $this|string
     */
    public function comment($value=NULL)
    {
        if( func_num_args() ) {
            $this->comment = $value;
            return $this;
        }else{
            return $this->name;
        }
    }

    /**
     * @param null $value
     * @return $this|bool
     */
    public function autoIncrement($value=NULL)
    {
        if( func_num_args() ) {
            $this->autoIncrement = $value;
            return $this;
        }else{
            return $this->autoIncrement;
        }
    }

    /**
     * @param null $value
     * @return $this|bool
     */
    public function unsigned($value=NULL)
    {
        if( func_num_args() ) {
            $this->unsigned = $value;
            return $this;
        }else{
            return $this->unsigned;
        }
    }

    /**
     * @param null $value
     * @return $this|bool
     */
    public function binary($value=NULL)
    {
        if( func_num_args() ) {
            $this->binary = $value;
            return $this;
        }else{
            return $this->binary;
        }
    }

    /**
     * @param null $toggle
     * @return bool|SchemaField|SchemaTable|void
     * @throws \enflares\System\Exception
     */
    public function primary($toggle=NULL)
    {
        if( $this->table ) {
            if( func_num_args() ) {
                return $toggle ? $this->table->createPrimary($this) : $this->table->removePrimary($this);
            }else{
                return $this->table->isPrimary($this);
            }
        }

        return InvalidException::trigger('Schema table is not set yet');
    }

    /**
     * @param null $name
     * @param null $toggle
     * @return bool|SchemaField|SchemaTable|void
     * @throws \enflares\System\Exception
     */
    public function index($name=NULL, $toggle=NULL)
    {
        if( $this->table ) {
            if( $name && $toggle ) return $this->table->createIndex($name, $this);
            if( $toggle===FALSE ) return $this->table->removeIndex($this, $name);
            return $this->table->isIndex($this);
        }

        return InvalidException::trigger('Schema table is not set yet');
    }

    /**
     * @param null $name
     * @param null $toggle
     * @return bool|SchemaField|SchemaTable|void
     * @throws \enflares\System\Exception
     */
    public function unique($name=NULL, $toggle=NULL)
    {
        if( $this->table ) {
            if( $name && $toggle ) return $this->table->createUnique($name, $this);
            if( $toggle===FALSE ) return $this->table->removeUnique($this, $name);
            return $this->table->isUnique($this);
        }

        return InvalidException::trigger('Schema table is not set yet');
    }

    /**
     * @param null $name
     * @param null $toggle
     * @return bool|SchemaField|SchemaTable|void
     * @throws \enflares\System\Exception
     */
    public function fullText($name=NULL, $toggle=NULL)
    {
        if( $this->table ) {
            if( $name && $toggle ) return $this->table->createFullText($name, $this);
            if( $toggle===FALSE ) return $this->table->removeFullText($this, $name);
            return $this->table->isFullText($this);
        }

        return InvalidException::trigger('Schema table is not set yet');
    }

    /**
     * Explains a data column in SQL
     * @return $this|string
     */
    public function explain()
    {
        $sql = $this->type();
        if( $this->length ) $sql .= '('.$this->length.')';
        
        if( $this->unsigned ) $sql .= ' unsigned';
        
        if( $this->collation ) {
            $charset = explode('_', $this->collation);
            $charset = reset($charset);            
            //$sql .= ' CHARACTER SET '.$charset;
            $sql .= ' COLLATE '.($this->binary ? "{$charset}_bin" : $this->collation);
        }
        
        // null default
        //  NO ANY          NOT NULL DEFAULT 0
        //  YES             DEFAULT NULL

        if( $this->nullable ) {
            $sql .= ' DEFAULT NULL';
        } else {
            $sql .= ' NOT NULL';
            if( !is_null($this->default) ) $sql .= ' DEFAULT '.json_encode($this->default);
        }
        if( $this->autoIncrement ) $sql .=' PRIMARY KEY AUTO_INCREMENT';
        if( $comment = addcslashes($this->comment, '\'"') ) $sql .= " COMMENT '$comment'";

        return $sql;
    }
}