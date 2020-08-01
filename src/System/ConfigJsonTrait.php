<?php
namespace enflares\System;

/**
 * Trait ConfigJsonTrait
 * @package enflares\System
 */
trait ConfigJsonTrait
{
    /**
     * Gets an entry from the configuration property
     * @param null $key
     * @param null $value
     * @return $this|mixed
     */
    public function config($key=NULL, $value=NULL)
    {
        $config = json_decode($this->__get('config'), TRUE);

        switch( func_num_args() ){
            case 0:
                return $config;
            break;

            case 1:
                if( is_array($key) ) {
                    $config = array_merge((array)$config, $key);
                    $this->__set('config', json_encode($config));
                    return $this;
                }else{
                    return vars($key, $config);
                }
            break;

            case 2:
            default:
                $config = var_set($config?:[], $key, $value);
                $this->__set('config', json_encode($config));
                return $this;
            break;
        }
    }
}

