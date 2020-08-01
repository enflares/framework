<?php
namespace enflares\Db;

use mysqli_result;

defined('NOT_NULL_FLAG')        OR define('NOT_NULL_FLAG',   1);        /* Field can't be NULL */
defined('PRI_KEY_FLAG')         OR define('PRI_KEY_FLAG',    2);        /* Field is part of a primary key */
defined('UNIQUE_KEY_FLAG')      OR define('UNIQUE_KEY_FLAG',  4);       /* Field is part of a unique key */
defined('MULTIPLE_KEY_FLAG')    OR define('MULTIPLE_KEY_FLAG', 8);      /* Field is part of a key */
defined('BLOB_FLAG')            OR define('BLOB_FLAG', 16);             /* Field is a blob */
defined('UNSIGNED_FLAG')        OR define('UNSIGNED_FLAG', 32);         /* Field is unsigned */
defined('ZEROFILL_FLAG')        OR define('ZEROFILL_FLAG', 64);         /* Field is zerofill */
defined('BINARY_FLAG')          OR define('BINARY_FLAG', 128);          /* Field is binary   */
defined('ENUM_FLAG')            OR define('ENUM_FLAG', 256);            /* field is an enum */
defined('AUTO_INCREMENT_FLAG')  OR define('AUTO_INCREMENT_FLAG', 512);  /* field is a autoincrement field */
defined('TIMESTAMP_FLAG')       OR define('TIMESTAMP_FLAG', 1024);      /* Field is a timestamp */
defined('SET_FLAG')             OR define('SET_FLAG', 2048);            /* field is a set */
defined('NO_DEFAULT_VALUE_FLAG') OR define('NO_DEFAULT_VALUE_FLAG', 4096);  /* Field doesn't have default value */
defined('ON_UPDATE_NOW_FLAG')   OR define('ON_UPDATE_NOW_FLAG', 8192);  /* Field is set to NOW on UPDATE */
defined('NUM_FLAG')             OR define('NUM_FLAG', 32768);           /* Field is num (for clients) */
defined('PART_KEY_FLAG')        OR define('PART_KEY_FLAG', 16384);      /* Intern); Part of some key */
defined('GROUP_FLAG')           OR define('GROUP_FLAG', 32768);         /* Intern: Group field */
defined('UNIQUE_FLAG')          OR define('UNIQUE_FLAG', 65536);        /* Intern: Used by sql_yacc */
defined('BINCMP_FLAG')          OR define('BINCMP_FLAG', 131072);       /* Intern: Used by sql_yacc */

/**
 * Class MySQLi
 * @package enflares\Db
 */
class MySQLi extends Db
{
    /**
     * @return \mysqli
     */
    private function db() {
        if( $this->connection instanceof \mysqli )
            return $this->connection;
        return NULL;
    }

    /**
     * @inheritDoc
     */
    public function begin()
    {
        if( $db=$this->db() ) 
            return mysqli_autocommit($db, FALSE);
        return NULL;
    }

    /**
     * @inheritDoc
     */
    public function commit()
    {
        if( $db=$this->db() ) {
            return mysqli_commit($db) && mysqli_autocommit($db, TRUE);
        }
        return NULL;
    }

    /**
     * @inheritDoc
     */
    public function rollback()
    {
        if( $db=$this->db() ) {
            return mysqli_rollback($db) && mysqli_autocommit($db, TRUE);
        }
        return NULL;
    }

    /**
     * @inheritDoc
     */
    public function getAffectedRows()
    {
        if( $db=$this->db() ) return mysqli_affected_rows($db);
        return NULL;
    }

    /**
     * @inheritDoc
     */
    public function getInsertedId()
    {
        if( $db=$this->db() ) return mysqli_insert_id($db);
        return NULL;
    }

    /**
     * @inheritDoc
     */
    public function getError()
    {
        if( $db=$this->db() ) return mysqli_errno($db);
        return NULL;
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        if( $db=$this->db() ) return mysqli_error($db);
        return NULL;
    }

    /**
     * @inheritDoc
     */
    public function fetch($result, $assoc = NULL, $num = NULL)
    {
        if( $result instanceof mysqli_result ) {
            if( $assoc && $num )
                return mysqli_fetch_array($result,MYSQLI_BOTH);

            if( $assoc )
                return mysqli_fetch_assoc($result);

            if( $num )
                return mysqli_fetch_array($result, MYSQLI_NUM);
        }
        return NULL;
    }

    /**
     * @inheritDoc
     */
    public function fetchObject($result, $class=NULL, Array $params=NULL)
    {
        if( $result instanceof mysqli_result )
            return $result->fetch_object($class, $params);
        return NULL;
    }

    /**
     * @inheritDoc
     */
    public function fetchMeta($result)
    {
        $results = array();

        if( $result instanceof mysqli_result ) {
            foreach( mysqli_fetch_fields($result) as $index=>$meta ) {
                $flag = $meta->flags;

                // Field       | Type         | Null | Key | Default | Extra
                // userid      | varchar(16)  | NO   | PRI | NULL

                $results[$index] = array(
                    'Table'=>$meta->Table,
                    'Field'=>$meta->name,
                    'Type'=>$meta->type,
                    'Null'=>!($flag & NOT_NULL_FLAG),
                    'Key'=>($flag & PRI_KEY_FLAG) ? 'PRI' : NULL,
                    'Default'=>NULL,
                    'Extra'=>NULL,
                    'Length'=>$meta->length,
                    'MaxLength'=>$meta->max_length,
                    'CharsetId'=>$meta->charsetnr,
                    'Flags'=>$meta->flags,
                    'PrimaryKey'=>$flag & PRI_KEY_FLAG,
                    'UniqueKey'=>$flag & UNIQUE_KEY_FLAG,
                    'Blob'=>$flag & BLOB_FLAG,
                    'Unsigned'=>$flag & UNSIGNED_FLAG,
                    'ZeroFill'=>$flag & ZEROFILL_FLAG,
                    'Binary'=>$flag & BINARY_FLAG,
                    'Enum'=>$flag & ENUM_FLAG,
                    'AutoIncrement'=>$flag & AUTO_INCREMENT_FLAG,
                    'Timestamp'=>$flag & TIMESTAMP_FLAG,
                    'Set'=>$flag & SET_FLAG,
                    'Number'=>$flag & NUM_FLAG,
                    'PrimaryPartKey'=>$flag & PART_KEY_FLAG,
                    'Group'=>$flag & GROUP_FLAG,
                    'Unique'=>$flag & UNIQUE_FLAG,
                );
            }
        }

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function freeResult($result)
    {
        if( $result instanceof mysqli_result )
            mysqli_free_result($result);

        return $this;
    }

    /**
     * @inheritDoc
     * @throws \enflares\System\Exception
     */
    protected function connect()
    {
        $db = mysqli_connect(
            $this->config('host', 'localhost'),
            $this->config('user', 'root'),
            $this->config('pass', ''),
            $this->config('name')
        );

        if( $db && ( $charset=$this->config('charset', 'utf-8') ) )
            return $this->setCharset($charset, $db);

        if( $error = mysqli_connect_errno() )
            return Exception::trigger(mysqli_connect_error(), $error);
        return NULL;
    }

    /**
     * @inheritDoc
     * @throws \enflares\System\Exception
     */
    public function setCharset($charset, $db=NULL)
    {
        return ( ($charset = $charset ?: $this->config('charset', 'utf-8'))
                && ($charset  = str_replace('-', '', $charset))
                && (($connection = $db ?: $this->db()) instanceof \mysqli)
                && (!mysqli_set_charset($connection, $charset) )
                && ($error = mysqli_errno($db)) )
                ? Exception::trigger(mysqli_error($db), $error)
                : ($db ?: $this);
    }

    /**
     * @inheritDoc
     */
    public function getCharset()
    {
        if( $db=$this->db() ) return mysqli_character_set_name($db);
        return NULL;
    }

    /**
     * @inheritDoc
     */
    protected function disconnect($connection)
    {
        if( $db=$this->db() ) return !!mysqli_close($db);
        return NULL;
    }

    /**
     * @inheritDoc
     * @throws \enflares\System\Exception
     */
    protected function exec($sql)
    {
        if( $connection = $this->open() )
            return mysqli_query($connection, $sql);
        return NULL;
    }

    /**
     * @inheritDoc
     */
    public function limit($sql, $limit = NULL, $index = NULL)
    {
        if( $limit = max(0, intval($limit)) ) {
            $index = max(1, intval($index));
            $start = ($index-1) * $limit;
            return rtrim(trim($sql), '; -') . " LIMIT $start, $limit";
        }

        return $sql;
    }

    /**
     * @inheritDoc
     */
    public function format($sql, array $args)
    {
        $sql = str_replace('#_', $this->config('prefix'), $sql);
        foreach( $args as $key=>$value )
            $sql = str_replace('{:'.$key.':}', $this->escape($value), $sql);

        return $sql;
    }

    /**
     * @inheritDoc
     */
    public function escape($value)
    {
        if( is_null($value) )
            return 'NULL';

        if( is_bool($value) )
            return $value ? 1 : 0;

        if( is_numeric($value) )
            return $value;

        if( is_array($value) )
            return '(' . implode(',', array_map(array($this, __FUNCTION__), $value)) . ')';

        if( is_object($value) )
            $value = serialize($value);

        $value = ( $db=$this->db() )
                ? mysqli_real_escape_string($db, $value)
                : addslashes($value);
                
        return "'$value'";
    }

    /**
     * @inheritDoc
     * @throws \enflares\System\Exception
     */
    public function columns($table, $likes=NULL, $where=NULL, Array $args=NULL, $forceUpdate=NULL)
    {
        static $g = array();
        $table = strtolower(trim($table, '#_ '));

        if( !isset($g[$table]) || $forceUpdate ) {
            $g[$table] = FALSE;

            $sql = 'SHOW COLUMNS IN #_'.$table;
            if( $likes ) $sql .= ' LIKE '.$this->escape($likes);
            if( $where ) $sql .= ' WHERE '.$this->where($where, $args);

            if( $query = $this->query($sql) ) {
                while( $info = $query->fetch() ) {
                    // Field       | Type         | Null | Key | Default | Extra
                    // user_id     | varchar(16)  | NO   | PRI | NULL
                    $g[$table][$info['Field']] = $info;
                }
            }
        }

        return $g[$table] ?: NULL;
    }

    /**
     * @inheritDoc
     * @throws \enflares\System\Exception
     */
    public function getDatabase()
    {
        return $this->query('SELECT database()')->fetchValue();
    }

    /**
     * @inheritDoc
     */
    public function setDatabase($name)
    {
        if( $db=$this->db() ) return !!mysqli_select_db($db, $name);
        return NULL;
    }
}