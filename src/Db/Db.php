<?php
namespace enflares\Db;

use enflares\System\Component;
use enflares\Db\Exception;

/**
 * Class Db
 * @package enflares\Db
 */
abstract class Db extends Component
{
    protected $connection;

    private $profile;
    private $histories = array();
    private $limit;
    private $index;

    /**
     * @return Db
     */
    public static function getInstance()
    {
        static $g;
        if( !$g ) {
            $driver = env('DB_DRIVER', MySQLi::class);
            $g = new $driver;
        }
        return $g;
    }

    /**
     * Db constructor.
     * @param $profile
     */
    public function __construct($profile=NULL)
    {
        parent::__construct();
        $this->profile = $profile ? ('DB_' . strtoupper($profile) . '_') : 'DB_';
    }

    /**
     * Db destructor.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function __get($key)
    {
        return $this->config($key);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return count($this->histories) ? end($this->histories) : '';
    }

    /**
     * Query strings
     *
     * @return array
     */
    public function histories()
    {
        return $this->histories;
    }

    /**
     * Output the debugging information
     */
    public function debug()
    {
        if( $error = $this->getError() )
            debug('Error: ' . $error, 'Message: '.$this->getMessage(), $this->histories());
        else
            debug( $this->histories() );
    }

    /**
     * Retrieve the configuration
     * @param $field
     * @param null $default
     * @return mixed|null
     */
    protected function config($field, $default=NULL){
        return env($this->profile . strtoupper($field), $default);
    }

    /**
     * Open the connection to database
     * @return null
     * @throws \enflares\System\Exception
     */
    public function open()
    {
        if( is_null($this->connection) )
            $this->connection = $this->connect() ?: FALSE;

        if( !$this->connection )
            return Exception::trigger('Unable to connect the database');

        return $this->connection ?: NULL;
    }

    /**
     * Close the connection with database
     * @return $this
     */
    public function close()
    {
        if( $this->connection ) {
            $this->disconnect($this->connection);
            $this->connection = NULL;
        }

        return $this;
    }

    /**
     * Execute an query
     * @param string $sql
     * @param mixed $args
     * @param int $limit
     * @param int $index
     * @return ResultSet
     * @throws Exception
     * @throws \enflares\System\Exception
     */
    public function query($sql, $args=NULL, $limit=NULL, $index=NULL)
    {
        $this->limit = $limit;
        $this->index = $index;

        $sql = $this->limit($this->format($sql, (array)$args), $limit, $index);
        $this->histories[] = $sql;

        if( $result = $this->exec($sql) )
            return new ResultSet($result, $this);

        $this->throwError();
    }

    /**
     * Execute a command
     * @param string $sql
     * @param mixed $args
     * @return int
     * @throws Exception
     * @throws \enflares\System\Exception
     */
    public function execute($sql, $args=NULL)
    {
        $sql = $this->format($sql, (array)$args);
        $this->histories[] = $sql;

        if( $this->exec($sql) )
            return $this->getAffectedRows();

        $this->throwError();
    }

    /**
     * @return $this|void
     * @throws Exception
     * @throws \enflares\System\Exception
     */
    public function throwError(){
        if( $error = $this->getError() )
            return Exception::trigger($this->getMessage(), $error);

        return $this;
    }

    /**
     * Return the affected rows by the latest command
     * @return int
     */
    public abstract function getAffectedRows();

    /**
     * Return the latest auto increased id value
     * @return mixed
     */
    public abstract function getInsertedId();

    /**
     * Return the error code of the latest operation
     * @return int
     */
    public abstract function getError();

    /**
     * Return the error message of the latest operation
     * @return mixed
     */
    public abstract function getMessage();

    /**
     * Retrieve the queried result
     * @param $result
     * @param bool $assoc
     * @param bool $num
     * @return array
     */
    public abstract function fetch($result, $assoc=NULL, $num=NULL);

    /**
     * Retrieve the queried result as an object
     * @param $result
     * @param string $class Optional. 'stdClass' by default
     * @param array $params Optional
     * @return array
     */
    public abstract function fetchObject($result, $class=NULL, Array $params=NULL);

    /**
     * Retrieve the columns' meta information
     * @param $result
     * @return array
     */
    public abstract function fetchMeta($result);

    /**
     * Release to free the memory occupation by the result set
     * @param $result
     * @return mixed
     */
    public abstract function freeResult($result);

    /**
     * Create the connection to the database
     * @throws Exception
     * @return mixed
     */
    protected abstract function connect();

    /**
     * Terminate the connection to the database
     * @param $connection
     * @return bool
     */
    protected abstract function disconnect($connection);

    /**
     * Change the charset
     * @param $charset
     * @param null $connection
     * @throws Exception
     */
    public abstract function setCharset($charset, $connection=NULL);


    /**
     * Retrieve the current charset of database
     * @return string
     */
    public abstract function getCharset();

    /**
     * Retrieve the current database name
     * @throws Exception
     * @return string
     */
    public abstract function getDatabase();

    /**
     * Change the current database name
     * @param string $name
     * @return mixed
     */
    public abstract function setDatabase($name);

    /**
     * Execute a query
     * @param $sql
     * @return mixed
     */
    protected abstract function exec($sql);

    /**
     * Combine the pagination setting to a sql string
     * @param $sql
     * @param null $limit
     * @param null $index
     * @return mixed
     */
    public abstract function limit($sql, $limit=NULL, $index=NULL);

    /**
     * Format a sql string by escaping the irregular characters and replacing value to placeholders
     * @param $sql
     * @param array $args
     * @return string
     */
    public abstract function format($sql, Array $args);

    /**
     * Escape a value to a string to be combined to a sql query
     * @param $value
     * @return string
     */
    public abstract function escape($value);

    /**
     * Return the columns information in a table
     * @param $table
     * @param null $likes
     * @param null $where
     * @param array|null $args
     * @return array
     */
    public abstract function columns($table, $likes=NULL, $where=NULL, Array $args=NULL);

    /**
     * @param $table
     * @param $fields
     * @param array|null $args
     * @return array
     */
    protected function __build_fields_set($table, $fields, Array &$args=NULL)
    {
        $tmp = array();

        $columns = $this->columns($table);
        if( !$columns || !count($columns) ) return $tmp;

        foreach( (array)$fields as $field=>$value ) {
            if( is_int($field) ) {
                $tmp[] = $value;
            }else{
                if(strpos($field, ' ')!==FALSE){
                    $key = explode(' ', $field);
                    $key = reset($key);
                    if( isset($columns[$key]) ) {
                        $args[$key] = $value;
                        $tmp[] = "$field{:$key:}";
                    }
                }else{
                    if( isset($columns[$field]) ) {
                        $args[$field] = $value;
                        $tmp[] = "$field={:$field:}";
                    }
                }
            }
        }

        return $tmp;
    }

    /**
     * Insert a row
     * @param $table
     * @param array|Query $fields
     * @param null $replace
     * @return int
     * @throws Exception
     * @throws \enflares\System\Exception
     */
    public function insert($table, $fields, $replace=NULL)
    {
        $sql = $replace ? 'REPLACE ' : 'INSERT ';
        if( $replace===FALSE ) $sql .= 'IGNORE ';

        if( $fields instanceof Query ) {
            if($this->execute($sql."#_$table $fields"))
                return $this->getInsertedId();
        } else {
            foreach ($fields as $key => $value)
                if (is_null($value)) unset($fields[$key]);

            $args = array();
            $tmp = $this->__build_fields_set($table, $fields, $args);

            if (count($tmp)) {
                $sql .= '#_' . $table . ' SET ' . implode(', ', $tmp);
                if ($this->execute($sql, $args))
                    return $this->getInsertedId();
            }
        }
    }

    /**
     * Insert or replace a row
     * @param $table
     * @param array|Query $fields
     * @return int
     * @throws Exception
     * @throws \enflares\System\Exception
     */
    public function replace($table, $fields)
    {
        return $this->insert($table, $fields, TRUE);
    }

    /**
     * Update a row
     * @param $table
     * @param array $fields
     * @param array|string|null $criteria
     * @param array|NULL $args
     * @param array|string|null $ordering
     * @param int|null $limit
     * @param int|null $index
     * @return int
     * @throws Exception
     * @throws \enflares\System\Exception
     */
    public function update($table, $fields, $criteria=NULL, Array $args=NULL, $ordering=NULL, $limit=NULL, $index=NULL)
    {
        $args = (array)$args;
        $tmp = $this->__build_fields_set($table, $fields, $args);

        if( count($tmp) ) {
            $sql = 'UPDATE #_'.$table.' SET '.implode(', ', $tmp);
            if( $where = $this->where($criteria, $args) ) $sql .= ' WHERE '.$where;
            if( $ordering ) $sql .= ' ORDER BY '.$this->order($ordering);
            $sql = $this->limit($sql, $limit, $index);

            return $this->execute($sql, $args);
        }
    }

    /**
     * Delete a row
     * @param $table
     * @param array|string|null $criteria
     * @param array|NULL $args
     * @param array|string|null $ordering
     * @param int|null $limit
     * @param int|null $index
     * @return int
     * @throws Exception
     * @throws \enflares\System\Exception
     */
    public function delete($table, $criteria=NULL, Array $args=NULL, $ordering=NULL, $limit=NULL, $index=NULL)
    {
        $sql = 'DELETE FROM #_'.$table;
        if( $where = $this->where($criteria, $args) ) $sql .= ' WHERE '.$where;
        if( $ordering ) $sql .= ' ORDER BY '.$this->order($ordering);
        $sql = $this->limit($sql, $limit, $index);

        return $this->execute($sql);
    }

    /**
     * Search records
     * @param $table
     * @param null $fieldList
     * @param null $criteria
     * @param null $args
     * @param null $ordering
     * @param null $limit
     * @param null $index
     * @param null $grouping
     * @param null $having
     * @param null $distinct
     * @return ResultSet
     * @throws \enflares\System\Exception
     */
    public function search($table, $fieldList=NULL,
                           $criteria=NULL, $args=NULL, $ordering=NULL,
                           $limit=NULL, $index=NULL,
                           $grouping=NULL, $having=NULL, $distinct=NULL)
    {
        $sql = $this->buildQuery($table, $fieldList, $criteria, $args, $ordering, $grouping, $having, $distinct);
        return $this->query($sql, $args, $limit, $index);
    }

    /**
     * Build a SQL query
     * @param $table
     * @param null $fieldList
     * @param null $criteria
     * @param null $args
     * @param null $ordering
     * @param null $grouping
     * @param null $having
     * @param null $distinct
     * @return string
     */
    public function buildQuery($table, $fieldList=NULL,
                               $criteria=NULL, $args=NULL, $ordering=NULL,
                               $grouping=NULL, $having=NULL, $distinct=NULL)
    {
        foreach( $table=(array)$table as $i=>$tbl )
            if( !preg_match('/\s+/', $tbl) && (strpos($tbl, '#_')===FALSE) )
                $table[$i] = '#_'.$tbl;

        $table = implode(',', $table);

        $sql = array('SELECT');
        if( $distinct ) $sql[] = 'DISTINCT';
        $sql[] = $fieldList ?: '*';
        $sql[] = 'FROM '.$table;

        if( $criteria = $this->where($criteria, $args) )
            $sql[] = 'WHERE '.$criteria;

        if( $grouping = implode(',', (array)$grouping) )
            $sql[] = 'GROUP BY '.$grouping;

        if( $having = $this->where($having, $args) )
            $sql[] = 'HAVING '.$having;

        if( $ordering = $this->order($ordering))
            $sql[] = 'ORDER BY '.$ordering;

        return implode(' ', $sql);
    }

    /**
     * Build the ORDER part of a query
     * @param array|string|null $ordering
     * @return string|void
     */
    public function order($ordering=NULL)
    {
        if( !$ordering ) return;

        $s = array();
        foreach( (array)$ordering as $index=>$item ) {
            if( is_int($index) ) {
                // array('id desc');
                $s[] = $item;
            }else{
                // array('id'=>TRUE); // TRUE = ASC, FALSE = DESC
                // array('id'=>'DESC');
                $s[] = $index . is_bool($item) ? ($item?'':' DESC') : " $item";
            }
        }

        return implode(', ', $s);
    }


    /**
     * Build the WHERE part of a query
     * @param array|string $criteria
     * @param array|NULL $args
     * @param null $op
     * @return string
     */
    public function where($criteria, Array $args=NULL, $op=NULL)
    {
        $q = array();
        foreach( (array)$criteria as $name=>$value ) {
            if( is_numeric($name) ) {
                if( is_array($value) )
                    $value = $this->where($value, $args,strcasecmp($op, 'OR') ? 'OR' : 'AND');

                if( $value=trim($value) )
                    $q[] = "( $value )";
            }else{
                if( strpos($name, ' ')!==FALSE ) {
                    if( is_array($value) ) {
                        $value = $this->escape($value);
                        $q[] = "( $name IN $value )";
                    }else{
                        $args[] = $value;
                        $q[] = "( $name = {:".array_key_last($args).':} )';
                    }
                } else {
                    if( is_array($value) ) {
                        $value = $this->escape($value);
                        $q[] = "( $name IN $value )";
                    }else{
                        $q[] = "( $name = {:$name:} )";                    
                        $args[$name] = $value;
                    }
                }
            }
        }

        if( count($q) ){
            if( !$op ) $op = 'AND';
            return $this->format(implode(" $op ", $q), (array)$args);
        }
    }

    /**
     * @param $table
     * @param null $fieldList
     * @param null $primaryKey
     * @return Query
     */
    public function table($table, $fieldList=NULL, $primaryKey=NULL)
    {
        return new Query($table, $fieldList, $this, $primaryKey);
    }

    /**
     * Return this page limit and page index
     *
     * @return array
     */
    public function pagination()
    {
        return array(
            'limit'=>max(0, intval($this->limit)),
            'index'=>max(1, intval($this->index)),
        );
    }
}