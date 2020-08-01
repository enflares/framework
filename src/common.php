<?php
/**************************************************
 * Enflares - A PHP Framework For Web
 * @package  Enflares
 * @author   Shaotang Zhang <shaotang.zhang@gmail.com>
 **************************************************/

/////////////////////////////
//// Fundamental Helpers ////
/////////////////////////////

if( !function_exists('env') ){
    /**
     * Get a configuration or set a batch of configurations
     * When the argument $key is an Array, the values will be merged to the $_ENV
     * @param string|array $key
     * @param null $default Default value if the key is not found in $_ENV
     * @return mixed
     */
    function env($key, $default=NULL) {
        return is_array($key) 
                ? $_ENV = array_merge($_ENV, $key)
                : $_ENV[$key] ?? $default;
    }
}

if( !function_exists('path') ){
    /**
     * Combine a path with components, adding the root directory to the prefixed, without verifying the existence
     * @return string
     */
    function path() {
        $args = func_get_args();
        array_unshift($args, DOC_ROOT);
        return implode(DIRECTORY_SEPARATOR, $args);
    }
}

if( !function_exists('url') ){

    /**
     * Build a url
     *
     * @param string $base
     * @param array|null $args
     * @param bool $https
     * @return string
     */
    function url(String $base, Array $args=NULL, Bool $https=NULL){
        $u = parse_url($base);
        if( $https ) $u['scheme'] = 'https';

        if( !empty($args) ) {
            parse_str($u['query']??NULL, $a);
            $a = array_merge($a, $args);
            foreach( $a as $key=>$value )
                if( empty($value) ) unset($a[$key]);
            $u['query'] = http_build_query($a);
        }

        $s = [];
            
        if( isset($u['user']) || isset($u['pass']) )
            $s[] = rtrim(($u['user'] ?? NULL) . ':' . ($u['pass'] ?? NULL), ':');

        if( isset($u['host']) || isset($u['port']) )
            $s[] = rtrim(($u['host'] ?? $_SERVER['SERVER_NAME']) . ':' . ($u['port'] ?? NULL), ':');

        if( count($s) ) $s = [implode('@', $s)];

        if( isset($u['scheme']) ) array_unshift($s, $u['scheme'] .':/');
        if( isset($u['path']) ) $s[] = trim($u['path'], '/');
        
        $url = implode('/', $s);
        if( isset($u['query']) ) $url .= '?' . $u['query'];
        if( isset($u['fragment']) ) $url .= '#' . $u['fragment'];

        return $url;
    }
}

if( !function_exists('home') ){
    /**
     * Build a url based on the home directory
     * @param string $url
     * @param array|NULL $args
     * @param bool|null $https
     * @return string
     */
    function home($url=NULL, Array $args=NULL, $https=NULL)
    {
        if( is_array($url) ) {
            $k = $url;
            $url = $k['route'] ?? NULL;
            $https = $k['https'] ?? NULL;
            $args = array_merge($k, $args);
            unset($args['route'], $args['https'], $k);
        }

        $base = rtrim(env('BASE_URL') ?: '/', '/') . '/';        
        if( $https ) $base = 'https://'.$_SERVER['SERVER_NAME'] . '/' . $base;    
        if( $home = env('HOME_URL') ) $base .= trim($home, '/') . '/';

        $url = rtrim( $url . '.' . ($args['wrap']??NULL), '.');
        $site = $args['site'] ?? env('SITE_KEY');
        unset($args[0], $args['wrap'], $args['site']);

        if( function_exists('url_build') )
            return $base . url_build($url, $args, $https, $site);

        $pos = strpos($url, '?');
        if( $pos!==FALSE ) {
            parse_str(substr($url, $pos+1), $a);
            $args = array_merge((array)$a, (array)$args);
            $url = substr($url, 0, $pos);
        }

        foreach( $args as $key=>$value ) 
            if( empty($empty) ) unset($args[$key]);

        if( count($args) ) $url .= '?' . http_build_query($args);

        return $base.$url;
    }
}

if( !function_exists('import') ){
    /**
     * Include a PHP file
     * @return mixed
     */
    function import() {
        if( $file = realpath(path(...func_get_args()).'.php') )
            return include($file);
        return false;
    }
}

if( !function_exists('redirect') ){
    /**
     * Redirect to the other url
     * @param string $url
     * @param int|null $code
     */
    function redirect($url, $code=NULL) {

        if( !headers_sent() ){
            header('location: '.$url, TRUE, $code ?: 302);
            exit;
        }

        $url = json_encode($url);
        exit("<html lang='en'><head><meta name=\"refresh\" content=\"0;$url\"><title>Redirecting...</title></title><script>window.location.replace($url)</script></head></html>");
    }
}

if( !function_exists('debug') ){
    /**
     * Output the debugging information
     */
    function debug() {
        $args=func_get_args();

        if( env('IS_DEBUG') ) {
            echo '<pre style="border: 1px solid #f2f2f2">';

            foreach( $args as $arg ) {
                if( is_bool($arg) )
                    echo $arg ? 'TRUE' : 'FALSE';
                else
                    print_r($arg);
            }

            foreach( debug_backtrace() as $bt ) {
                if( isset($bt['function']) && ($bt['function']==='debugX') )
                    continue;
                    
                if( isset($bt['file'], $bt['line']) ) {
                    echo '<small>';                     
                    echo $bt['file']??NULL, '@', $bt['line']??NULL;    
                    $lines = file($bt['file']);
                    $code = $lines[$bt['line']-1] ?? NULL;
                    if( $code ) {
                        echo '<div style="background-color: #f4f4f4">';
                        highlight_string( '<?php '.trim($code));
                        echo '</div>';
                    }     
                    echo '</small>';     
                    break;
                }  
            } 

            echo '</pre>';
        }

        return end($args);
    }
}

if( !function_exists('debugX') ){
    /**
     * Output the debugging information and then abort the application
     */
    function debugX() {
        debug( ...func_get_args() );
        exit;
    }
}

if( !function_exists('tag') ){
    /**
     * Build a HTML tag
     *
     * @param string $tagName
     * @param mixed $content
     * @param array $attributes
     * @param string|null $tagClose
     * @return string
     */
    function tag($tagName=NULL, $content=NULL, Array $attributes=NULL, $tagClose=NULL, $must=NULL) {
        if( is_array($tagName) ) {
            $attributes = $tagName;
            $tagClose = $content;
            $tagName = $content = NULL;
        }

        switch( $tagName = strtolower($tagName) ) {
            case 'img': case 'br': case 'hr': case 'input':
                $tagClose = '/>';
            break;

            case 'link': case 'base':
                $tagClose = '>';
            break;

            case '!--': case '#':
                return '<!-- ' . $content . ' -->';
            break;

            case '': case '#text':
                return (string)$content;
            break;
        }

        $s = array('<'.$tagName);
        if( $attributes ) ksort($attributes);
        if( $attributes ) $s[] = tag_attr(NULL, $attributes, $must);
        $s = implode(' ', $s);

        if( $content = tag_content($content) )
            return $s . '>'.$content.'</'.$tagName.'>';
        
        return $s . ($tagClose ?: "></$tagName>");
    }
}

if( !function_exists('tag_content') ) {
    /**
     * Convert the content of a tag into string
     *
     * @param mixed $content
     * @return string
     */
    function tag_content($content) {
        if( $content instanceof Closure )
            return tag_content(call_user_func($content));

        if( is_array($content) ) {
            return implode(array_map('tag_content', $content));
        }

        return (string)$content;
    }
}

if( !function_exists('tag_attr') ) {
    /**
     * Convert the argument into the attribute format of a tag
     *
     * @param string $key
     * @param mixed $value
     * @param bool $must
     * @param string $prefix
     * @return string|void
     */
    function tag_attr($key, $value, $must=NULL, $prefix=NULL) {

        if( $value===TRUE ) 
            return strtr($prefix.$key, '_', '-');

        if( !$must && empty($value) && (var_export($value, TRUE)!=='0') ) 
            return; 

        if( is_array($value) ) {
            foreach( $value as $k=>$v ) 
                $value[$k] = tag_attr($k, $v, $must, $prefix);
            return implode(' ', array_values($value));    
        }
            
        return sprintf('%s="%s"', strtr($prefix.$key, '_', '-'), str_replace('"', '&quot;', $value));
    }
}

if( !function_exists('vars') ) {
    /**
     * Fetch the value from data
     *
     * @param string $key
     * @param object|array $data
     * @return mixed
     */
    function vars($key, $data) {
        $p = &$data;

        foreach( explode('.', $key) as $k ) {

            if( is_object($p) ) {
                $p = $p->$k ?? NULL;
            } elseif( is_array($p) ) {
                $p = $p[$k] ?? NULL;
            } else {
                $p = NULL;
            }

            if( is_null($p) ) break;
        }

        return $p;
    }
}

if( !function_exists('var_get') ) {
    /**
     * Fetch the value from data
     *
     * @param object|array $data
     * @param string $key
     * @return mixed
     */
    function var_get($data, $key) {
        return vars($key, $data);
    }
}

if( !function_exists('var_set') ) {
    /**
     * Fetch the value from data
     *
     * @param object|array $data
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    function var_set($data, $key, $value) {
        $p = &$data;

        $parts = is_array($key) ? $key : explode('.', $key);
        while( $k=array_shift($parts) ){
            if( is_object($p) ){
                if( count($parts) ) {
                    $p = &$p->$k;
                }else{
                    if( is_null($value) ) unset($p->$k);
                    else $p->$k = $value;
                    break;
                }
            }elseif( is_array($p) ) {
                if( count($parts) ){
                    if( !isset($p[$k]) ) $p[$k] = [];
                    $p = &$p[$k];
                }else{
                    if( is_null($value) ) unset($p[$k]);
                    else $p[$k] = $value;
                    break;
                }
            }else{
                $p = var_set([], $parts, $value);
                break;
            }
        }

        return $data;
    }
}

if( !function_exists('var_isset') ) {
    /**
     * Fetch the value from data
     *
     * @param string $key
     * @param mixed $data
     * @return bool
     */
    function var_isset($data, $key) {
        return !is_null(var_get($data, $key));
    }
}

if( !function_exists('var_unset') ) {
    /**
     * Fetch the value from data
     *
     * @param string $key
     * @param mixed $data
     * @return void
     */
    function var_unset($data, $key) {
        return var_set($data, $key, NULL);
    }
}


if( !function_exists('uuid') ) {
    /**
     * Build a value with an auto-increment hex index
     *
     * @param string $prefix
     * @return string
     */
    function uuid($prefix=NULL) {
        static $g = 0;
        return $prefix.dechex(++$g);
    }
}

if( !function_exists('trait_uses') ) {

    /**
     * Check if a trait is used in a definition
     * @param $any
     * @param string $trait
     * @return bool
     */
    function trait_uses($any, $trait) {
        return is_object($any) && in_array($trait, class_uses($any));
    }
}

if( !function_exists('ip') ) {
    /**
     * Return the real Remove Address
     *
     * @return string
     */
    function ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
            return $_SERVER['HTTP_CLIENT_IP'];

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            return $_SERVER['HTTP_X_FORWARDED_FOR'];

        return $_SERVER['REMOTE_ADDR'];
    }
}

if( !function_exists('url_build') ) {
    /**
     * Build a url with a route
     *
     * @param string $route
     * @param array|null $args
     * @param bool $https
     * @param string $site
     * @return string
     */
    function url_build($route, $args=NULL, $https=NULL, $site=NULL) {

        $s = [];
        if( $site ) $s[] = '~'.trim(strtolower($site), '/\\.');

        $base = trim(str_replace('/-', '/', strtr(strtolower(preg_replace('/([A-Z])/', '-\\1', $route)), '\\', '/')), '/');
        $base = explode('.', $base);

        $s[] = array_shift($base);

        if( isset($args['name']) && $args['name'] ) {
            $s[] = implode('/', array_map('ucfirst', explode('/', strtr($args['name'], '\\', '/'))));
            $args['name'] = NULL;
        }

        if( isset($args['id']) && $args['id'] ) {
            $s[] = $args['id'];
            $args['id'] = NULL;
        }

        $u = explode('?', url(implode('/', $s), $args, $https));
        array_unshift($base, array_shift($u));
        array_unshift($u, implode('.', $base));

        return implode('?', $u);
    }
}

if( !function_exists('url_parse') ) {

    /**
     * Analyze a url into meaningful parts
     *
     * @param string $url
     * @return array
     */
    function url_parse($url) {

        $info = [$url = strtr($url, '\\', '/')];
        $pattern = '#(?:/(?<id>\d+))?(?:\.(?<wrap>\w+))?$#';
        if( preg_match($pattern, $url, $matches) ) {
            if( isset($matches['id']) && ($id=intval($matches['id']))) $info['id'] = $id;
            if( isset($matches['wrap']) ) $info['wrap'] = trim($matches['wrap'], '.');
            if( $len=strlen($matches[0]) ) $url = substr($url, 0, -$len);
        }
        
        $pattern = '#(/~(?<site>[\w\-]+))?(?<route>(?:/[a-z][\w\-]*)+)?(?<name>(?:/[A-Z0-9][^\.\/]*)+)?$#';
        if( preg_match($pattern, $url, $matches) ) {
            if( isset($matches['site'])  && $matches['site'])   $info['site']  = $matches['site'];
            if( isset($matches['route']) && $matches['route'] ) $info['route'] = trim($matches['route'], '/');
            if( isset($matches['name'])  && $matches['name'] )  $info['name']  = trim($matches['name'], '/');
        }

        return $info;
    }
}

if( !function_exists('timeStamp') ) {
    /**
     * Convert to timestamp
     * @param $any
     * @return int
     */
    function timeStamp($any) {
        if( is_string($any) ) return strtotime($any);
        if( $any instanceof DateTime) return $any->getTimestamp();
        return intval($any);
    }
}

if( !function_exists('timeExpire') ) {
    /**
     * Calculate an expired time
     * @param $any
     * @return false|int|string
     */
    function timeExpire($any) {
        if( empty($any) ) return time();
        if( is_numeric($any) ) return time() + $any;
        if( is_string($any) ) return strtotime($any);
        if( $any instanceof DateTime) return $any->getTimestamp();
        throw new InvalidArgumentException('Invalid expire time');
    }
}