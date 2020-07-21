<?php
namespace enflares\Db;

use Closure;
use enflares\System\DataInterface;

/**
 * Class Query
 * @package enflares\Db
 */
class Query
{
    /**
     * @var Db
     */
    protected $db;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var array
     */
    protected $joins = [];

    /**
     * @var string|null
     */
    protected $fieldList;

    /**
     * @var
     */
    protected $primaryKey;

    protected $limit;
    protected $index;

    protected $where = [];
    protected $args = [];
    protected $ordering = [];
    protected $grouping = [];
    protected $having = [];
    protected $distinct;

    private $result;

    private $callback;

    /**
     * @var bool
     */
    private $ignoreOrdering;

    public function __construct($table, $fieldList=NULL, Db $db=NULL, $primaryKey=NULL)
    {
        $this->db = $db ?: Db::getInstance();
        $this->fieldList = $fieldList;

        $table = trim(implode(', ', (array)$table), ', ');
        if( (strpos($table, ' ')===FALSE) && (strpos($table, '#_')===FALSE) ) 
            $table = '#_'.$table;
        $this->table = $table;

        if( !($this->primaryKey = $primaryKey) ) {
            $prefix = explode('_', $table);
            $this->primaryKey = end($prefix).'_id';
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $table = $this->table;
        if( count($this->joins) ) $table .= ' ' . implode(' ', $this->joins);        
        $sql = $this->db->buildQuery($table,
                                    implode(',', (array)$this->fieldList),
                                    $this->where,
                                    $this->args,
                                    $this->ignoreOrdering ? NULL : $this->ordering,
                                    $this->grouping,
                                    $this->having,
                                    $this->distinct
        );

        return ( $this->ignoreOrdering )
                ? $sql
                : $this->db->limit($sql, $this->limit, $this->index);
    }

    public function callback($callback=NULL)
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @param null $formatter
     * @return array
     * @throws Exception
     */
    public function all($formatter=NULL)
    {
        $result = [];
        while( $rs = $this->fetch($formatter) ) $result[] = $rs;
        return $result;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->where = [];
        $this->args = [];
        $this->ordering = [];
        $this->grouping = [];
        $this->having = [];

        $this->distinct = NULL;
        $this->limit = NULL;
        $this->index = NULL;

        $this->result = NULL;
        $this->ignoreOrdering = NULL;
        return $this;
    }

    public function alias($alias=NULL)
    {
        $this->alias = $alias;

        if( strpos($this->table, ' ')!==FALSE ) $this->table = "($this->table) AS $alias";
        else $this->table .= " AS $alias";

        return $this;
    }

    public function limit($limit=NULL, $index=NULL)
    {
        $this->limit = $limit;
        $this->index = $index;
        return $this;
    }

    /**
     * @return ResultSet|false
     * @throws Exception
     */
    public function search()
    {
        $this->ignoreOrdering = FALSE;
        return $this->result = $this->db->query((string)$this) ?: FALSE;
    }

    /**
     * @return array
     * @throws \enflares\System\Exception
     */
    public function find()
    {
        return $this->search()->fetch();
    }

    /**
     * Retrieve a record row
     *
     * The record can be convert to different types, indicated by the $callback parameter as follow:
     * 1. Return the raw array of record if $callback is NULL or omitted
     * 2. Return the merged array of record if $callback is an array
     * 3. Return an instance of Data if $callback is a class name extended from enflares\System\Data
     * 4. Return the result of the callback function if $callback is an instance of \Closure (a function)
     * 5. Return an object if $callback is TRUE
     * 6. Return an array if $callback is FALSE
     *
     * @param $callback
     * @param $class
     * @param $_
     * @return mixed
     * @throws Exception
     */
    public function fetch($callback=NULL, $class=NULL, $_=NULL)
    {
        if(is_null($this->result)) $this->search();
        if( $this->result ) {
            if( $callback = $callback ?: $this->callback ) {
                if( $callback === TRUE ) 
                    return $this->result->fetchObject($class, array_slice(func_get_args(), 2));   // => Exit

                if( is_array($rs = $this->result->fetch()) ) {
                    if( is_array($callback) ) 
                        return array_merge($callback, $rs);   // => Exit
                    
                    if( $callback instanceof Closure ) {
                        $args = func_get_args();
                        $args[0] = $rs;
                        return call_user_func_array($callback, $args);   // => Exit
                    }

                    if( is_subclass_of($callback, DataInterface::class) )
                        return new $callback($rs);   // => Exit
                }

                return $rs;

            }elseif( $callback===FALSE ) { 
                return $this->result->fetchArray();
            }else{
                return $this->result->fetch();
            }
        }
    }

    /**
     * @param string $command
     * @param string|null $columns
     * @return int
     * @throws \enflares\System\Exception
     */
    public function calculate($command, $columns=NULL)
    {
        $this->ignoreOrdering = TRUE;
        $table = $this->alias ?: $this->table;
        $columns = $columns ?: "$table.$this->primaryKey";
        if( $this->distinct ) $columns = 'DISTINCT '.$columns;

        $sql = preg_replace('/^SELECT\s+(.+)\s+FROM\s+/i', "SELECT $command($columns) FROM ", $this->__toString());
        return $this->db->query($sql, $this->args)->fetchValue();
    }

    /**
     * @param null $columns
     * @return int
     * @throws \enflares\System\Exception
     */
    public function sum($columns=NULL)
    {
        return $this->calculate('SUM', $columns);
    }

    /**
     * @param null $columns
     * @return int
     * @throws \enflares\System\Exception
     */
    public function count($columns=NULL)
    {
        return $this->calculate('COUNT', $columns);
    }

    /**
     * @param null $columns
     * @return int
     * @throws \enflares\System\Exception
     */
    public function max($columns=NULL)
    {
        return $this->calculate('MAX', $columns);
    }

    /**
     * @param null $columns
     * @return int
     * @throws \enflares\System\Exception
     */
    public function min($columns=NULL)
    {
        return $this->calculate('MIN', $columns);
    }

    /**
     * @param null $toggle
     * @return $this
     */
    public function distinct($toggle=NULL){
        $this->distinct = $toggle!==FALSE;
        return $this;
    }

    /**
     * @param $field
     * @param null $value
     * @return $this
     */
    public function where($field, $value=NULL)
    {
        if( func_num_args()===1 ) {
            $this->where[] = $value;
        }else{
            if( preg_match('/^\s*(\w+)\s+(.+)\s*$/i', $field, $m) ) {
                $value = $this->db->escape($value);
                $this->where[] = "$m[1] $m[2] $value";
            }else{
                $this->where[$field] = $value;
            }
        }

        return $this;
    }

    public function order($field, $desc=NULL)
    {
        if( func_num_args()===1 ) 
            $this->ordering[] = $field;
        else
            $this->ordering[] = $field . ($desc ? ' DESC' : NULL);

        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function groupBy($field)
    {
        if( is_null($field) ){
            $this->grouping = [];
        }else{
            $this->grouping = array_merge($this->grouping, func_get_args());
        }

        return $this;
    }

    /**
     * @param $field
     * @param null $value
     * @return $this
     */
    public function having($field, $value=NULL)
    {
        if( is_null($field) ) $this->having = [];
        if( func_num_args()===1 ) {
            $this->having[] = $value;
        }else{
            $this->having[$field] = $value;
        }

        return $this;
    }

    /**
     * @param $type
     * @param $table
     * @param null $primaryKey
     * @param null $foreignKey
     * @param null $alias
     * @param null $extra
     * @return $this
     */
    public function join($type, $table, $alias=NULL, $primaryKey=NULL, $foreignKey=NULL, $extra=NULL)
    {
        $table = (string)$table;
        if( strpos($table, ' ')===FALSE ) {
            $table = '#_'.$table;
            if( $alias ) $table .= ' AS '.$alias;
            else $alias = $table;
        }else{
            if( !$alias ) $alias = 'X_'.count($this->joins);
            $table = "($table) AS $alias";
        }

        if( !$primaryKey ) $primaryKey = $this->primaryKey;
        if( !$foreignKey ) {
            $parts=explode('.', $primaryKey);
            $foreignKey = end($parts);
        }

        if( strpos($primaryKey, '.')===FALSE )
            $primaryKey = ($this->alias ? $this->alias : '#_'.$this->table).'.'.$primaryKey;

        if( strpos($foreignKey, '.')===FALSE )
            $foreignKey = $alias.'.'.$foreignKey;

        $this->joins[] = trim("$type JOIN $table ON $primaryKey=$foreignKey $extra");

        return $this;
    }

    /**
     * @param $table
     * @param null $primaryKey
     * @param null $foreignKey
     * @param null $alias
     * @param null $extra
     * @return $this
     */
    public function innerJoin($table, $alias=NULL, $primaryKey=NULL, $foreignKey=NULL, $extra=NULL)
    {
        return $this->join('INNER', $table, $alias, $primaryKey, $foreignKey, $extra);
    }

    /**
     * @param $table
     * @param null $primaryKey
     * @param null $foreignKey
     * @param null $alias
     * @param null $extra
     * @return $this
     */
    public function leftJoin($table, $alias=NULL, $primaryKey=NULL, $foreignKey=NULL, $extra=NULL)
    {
        return $this->join('LEFT', $table, $alias, $primaryKey, $foreignKey, $extra);
    }

    /**
     * @param $table
     * @param null $primaryKey
     * @param null $foreignKey
     * @param null $alias
     * @param null $extra
     * @return $this
     */
    public function rightJoin($table, $alias=NULL, $primaryKey=NULL, $foreignKey=NULL, $extra=NULL)
    {
        return $this->join('RIGHT', $table, $alias, $primaryKey, $foreignKey, $extra);
    }

    /**
     * @param $table
     * @param null $required
     * @return $this
     */
    public function expend($table, $required=NULL)
    {
        return $this->join($required ? 'INNER' : 'LEFT', $table);
    }

    /**
     * @param null $formatter
     * @param null $range
     * @return array
     * @throws Exception
     */
    public function package($formatter=NULL, $range=NULL)
    {
        $result = [];

        $items = $this->all($formatter);
        if( count($items) ) $result['items'] = $items;
        if( $this->result ) $result['meta'] = $this->result->pagination($range);

        return $result;
    }
}