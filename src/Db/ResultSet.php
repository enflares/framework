<?php
namespace enflares\Db;

use Closure;
use mysqli_result;

/**
 * Class ResultSet
 * @package enflares\Db
 */
class ResultSet
{
    private $db;
    private $result;

    /**
     * ResultSet constructor.
     * @param $result
     * @param Db $db
     * @throws \enflares\System\Exception
     */
    public function __construct($result, Db $db)
    {
        if( $result && !($result instanceof mysqli_result) )
            Exception::trigger('Invalid argument to create an instance of database ResultSet');

        $this->db = $db;
        $this->result = $result;
    }

    public function __destruct()
    {
        if( $this->result && $this->db )
            $this->db->freeResult($this->result);
    }

    /**
     * @param null $range
     * @return array
     * @throws \enflares\System\Exception
     */
    public function pagination($range=NULL)
    {
        if( $this->result && $this->db )
        {
            list($limit, $index) = array_values((array)$this->db->pagination());

            if( $limit>0 )
            {
                $sql = (string)$this->db;
                $pos = strripos($sql, 'LIMIT ');
                if( $pos!==FALSE ) $sql = substr($sql, 0, $pos);

                $sql = 'SELECT COUNT(*) FROM ('.$sql.') AS CNT_'.time().'_'.mt_rand(100, 999);
                $total = intval($this->db->query($sql)->fetchValue());
                $count = ceil( $total / $limit );
                
                $start = $end = NULL;                
                if( $range=max(0, intval($range)) )
                {
                    $half = floor($range/2);

                    if( $index<=$half ){
                        $start = 1;
                        $end = $start+$range-1;
                    }elseif( $index>=$count-$half){
                        $end = $count;
                        $start = $count-$range+1;
                    }else{
                        $start = max(1, $index-$half);
                        $end = min($count, $index+$half);
                    }                        
                }

                return array(
                    'limit'=>$limit,
                    'index'=>$index,
                    'total'=>$total,
                    'count'=>$count,
                    'prev'=>max(1, $index-1),
                    'next'=>min($count, $index+1),
                    'range'=>$range,
                    'start'=>$start,
                    'end'=>$end,
                );
            }
        }
    }

    public function fetch()
    {
        if( $this->result && $this->db )
            return $this->db->fetch($this->result, TRUE, FALSE);
    }

    public function fetchArray()
    {
        if( $this->result && $this->db )
            return $this->db->fetch($this->result, FALSE, TRUE);
    }

    public function fetchValue()
    {
        if( $this->result && $this->db )
        {
            $rs = $this->fetchArray();
            return reset($rs);
        }
    }

    public function fetchObject($class, Array $params=NULL)
    {
        if( $this->result )
        {
            return $this->db->fetchObject($this->result, $class, (array)$params);
        }
    }

    public function fetchRow()
    {
        if( $rs = $this->fetchArray() )
        {
            $results = array();
            foreach( $this->db->fetchMeta($this->result) as $index=>$meta )
            {
                $results[$meta['Table']][$meta['Field']] = $rs[$index];
            }

            return $results;
        }
    }

    public function fetchAll(Closure $callback=NULL)
    {
        $results = array();
        while( $rs = $callback($this) ) 
            if( $callback ) $results[] = $callback($rs);
            else $results[] = $rs;

        return $results;
    }
}