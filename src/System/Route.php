<?php


namespace enflares\System;


use Closure;

class Route extends Component
{
    protected static $prefix;
    protected static $fallback;
    protected static $matches = array();
    protected static $profiles = array();

    /**
     * Return the specific profiled route
     * @param $name
     * @return Route|null
     */
    public static function profile($name)
    {
        if( isset(static::$profiles[$name]) )
        {
            return static::$profiles[$name];
        }
    }

    public static function group($prefix, Closure $callback)
    {
        $tmp = static::$prefix;
        static::$prefix = $prefix;
        $callback();
        static::$prefix = $tmp;
    }

    public static function add($method, $pattern, $command)
    {
        $route = new static;
        $route->pattern = static::$prefix.$pattern;
        $route->command = $command;

        foreach( (array)$method as $m )
        {
            static::$matches[strtoupper($m)][] = $route;
        }

        return $route;
    }

    public static function any($pattern, $command)
    {
        return static::add(['HEAD', 'GET', 'POST', 'PUT', 'PATCH', 'OPTIONS', 'DELETE'], $pattern, $command);
    }

    public static function head($pattern, $command)
    {
        return static::add(__FUNCTION__, $pattern, $command);
    }

    public static function get($pattern, $command)
    {
        return static::add(__FUNCTION__, $pattern, $command);
    }

    public static function post($pattern, $command)
    {
        return static::add(__FUNCTION__, $pattern, $command);
    }

    public static function put($pattern, $command)
    {
        return static::add(__FUNCTION__, $pattern, $command);
    }

    public static function patch($pattern, $command)
    {
        return static::add(__FUNCTION__, $pattern, $command);
    }

    public static function delete($pattern, $command)
    {
        return static::add(__FUNCTION__, $pattern, $command);
    }

    public static function options($pattern, $command)
    {
        return static::add(__FUNCTION__, $pattern, $command);
    }

    /**
     * Define the fallback process
     * @param Closure $callback
     */
    public static function fallback(Closure $callback)
    {
        static::$fallback = $callback;
    }

    /**
     * Lookup a matched route with a url
     * @param string|string[] $methods
     * @param string $url
     * @return mixed
     */
    public static function match($methods, $url)
    {
        foreach( (array)$methods as $method )
        {
            if( isset(static::$matches[$method]) )
            {
                foreach( static::$matches[$method] as $route )
                {
                    $action = $route->validate($url);
                    if( $action ) return $action;
                }
            }
        }

        if( ($fallback = static::$fallback) instanceof Closure )
        {
            return $fallback($url, $methods);
        }
    }

    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var mixed
     */
    protected $command;

    protected $id;
    protected $args;    
    protected $root;
    protected $route;
    protected $query;
    protected $wrapper;

    public function action()
    {
        $route = $this->route();

        $parts = explode('/', strtr(strtr(trim($route, '/\\. '), '\\', '/'), '.', '/'));

        foreach( $parts as $index=>$part )
            $parts[$index] = str_replace(' ', '', ucwords(strtr($part, '-', ' ')));

        switch( count($parts) )
        {
            case 0:
                $command = 'index';
                $parts = ['index'];
            break;

            case 1:
                $command = 'index';                
            break;

            default:
                $command = lcfirst(array_pop($parts));
        }

        $args = $this->params();
        if( !isset($args['route']) && !empty($this->route) ) $args['route'] = $this->route;
        if( !isset($args['name']) && !empty($this->query) ) $args['name'] = $this->query;
        if( !isset($args['id']) && !empty($this->id) ) $args['name'] = $this->id;

        return new Action([implode('/', $parts), $command], $args);
    }

    public function rest($method=NULL)
    {
        $route = $this->route();

        $parts = explode('/', strtr(strtr(trim($route, '/\\. '), '\\', '/'), '.', '/'));

        foreach( $parts as $index=>$part )
            $parts[$index] = str_replace(' ', '', ucwords(strtr($part, '-', ' ')));

        $args = $this->params();
        if( !isset($args['route']) && !empty($this->route) ) $args['route'] = $this->route;
        if( !isset($args['name']) && !empty($this->query) ) $args['name'] = $this->query;
        if( !isset($args['id']) && !empty($this->id) ) $args['name'] = $this->id;

        return new Action([implode('/', $parts), $method ?: 'index'], $args);
    }

    public function root($value=NULL)
    {
        if( func_num_args() ) $this->root = $value;
        return $this->root;
    }

    public function route($value=NULL)
    {
        if( func_num_args() ) $this->route = $value;
        return $this->route;
    }
    
    public function query($value=NULL)
    {
        if( func_num_args() ) $this->query = $value;
        return $this->query;
    }
    
    public function id($value=NULL)
    {
        if( func_num_args() ) $this->id = $value;
        return $this->id;
    }
    
    public function wrapper($value=NULL)
    {
        if( func_num_args() ) $this->wrapper = $value;
        return $this->wrapper;
    }

    public function params($key=NULL, $value=NULL)
    {
        switch( func_num_args() )
        {
            case 0:
                return $this->args;
            break;

            case 1:
                if( is_array($key) ) $this->args = array_merge((array)$this->args, $key);
                else return isset($this->args[$key]) ? $this->args[$key] : NULL;                
            break;

            case 2:
                if(is_null($value)) unset($this->args[$key]);
                else $this->args[$key] = $value;
            break;
        }

        return $this;
    }

    /**
     * Name this route as a profile
     * @param $name
     * @return Route
     */
    public function name($name)
    {
        return static::$profiles[$name] = $this;
    }

    /**
     * To check if a url is matched with this route
     * @param string $url
     * @return mixed
     */
    public function validate($url)
    {
        if( preg_match($this->pattern, $url, $args) )
        {
            if( $this->command instanceof Closure )
            {
                $func = $this->command;
                $matches = $args;
                return function(Request $request, Response $response) use($args, $func, $url, $matches)
                {
                    return $func($request->merge($args), $response, $url, $matches);
                };
            }

            return new Action($this->command, $args);
        }
    }

    /**
     * Build the url for this route
     * @param array|NULL $args
     * @return string
     * @throws Exception
     */
    public function url(Array $args=NULL, $mime=NULL)
    {
        if( isset($args['id']) ) 
        {
            $this->id($args['id']);
            unset($args['id']);
        }
        
        if( isset($args['route']) ) 
        {
            $this->route($args['route']);
            unset($args['route']);
        }
        
        if( isset($args['name']) ) 
        {
            $this->query($args['name']);
            unset($args['name']);
        }

        if( $mime ) $this->wrapper($mime);

        $s = [];
        if( $value=$this->root() ) $s[] = $value;
        if( $value=strtr(strtr($this->route(), '.', '/'), '\\', '/') ) $s[] = $value;
        if( $value=strtr(strtr($this->query(), '.', ' '), '\\', ' ') ) $s[] = strtr(ucwords($value), ' ', '/');
        if( $value=intval($this->id()) ) $s[] = $value;
        
        if( count($s) )
        {
            $pattern = implode('/', $s);
            if( $value=$this->wrapper() ) $pattern .= '.'.trim($value, '.');

            $args = array_merge($this->params(), (array)$args);
        }else{
            $pattern = $this->pattern;
            foreach( (array)$args as $key=>$value )
            {
                if( strpos($pattern, '{'.$key.'}')!==FALSE )
                {
                    $pattern = str_replace('{'.$key.'}', $value, $pattern);
                    unset($args[$key]);
                }
            }

            if( strpos($pattern, '{')!==FALSE )
            {
                InvalidException::trigger('Insufficient arguments provided for url "%s"', $this->pattern);
            }
        }

        if( $args && count($args) )
        {
            return $pattern . '?' . http_build_query($args);
        }else{
            return $pattern;
        }
    }
}