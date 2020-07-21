<?php
namespace enflares\System;

use Closure;
use ErrorException;

/**
 * Class Application
 * @package enflares\System
 */
class Application extends Component
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * Application constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->request = new Request;
        $this->response = Response::getInstance();

        $this->loadConfig(path('.env'));
        $this->loadConfig(map('.env'));

        error_reporting(E_ALL | E_STRICT);
        spl_autoload_register(array($this, '__autoload'));
        set_error_handler(array($this, 'errorHandler'));
        set_exception_handler(array($this, 'exceptionHandler'));
        register_shutdown_function(function() {
            $last_error = error_get_last();
            if ($last_error['type'] === E_ERROR) {
                return $this->errorHandler(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
            }
        });

        $this->detect();
        $this->fire('AppStarted');
    }

    /**
     * Addition to the composer class autoloader
     * @param $class
     * @return bool
     */
    public function __autoload($class)
    {
        if( $file = realpath(map(strtr($class, '\\', DIRECTORY_SEPARATOR) . '.php')))
            include_once $file;

        return class_exists($class, FALSE) || interface_exists($class) || trait_exists($class);
    }

    /**
     * Performs the detection before any action
     */
    protected function detect()
    {
        $this->fire('AppDetect');
    }

    /**
     * Load Configuration from file
     *
     * @param array|string $config
     * @param string $prefix
     * @return $this
     */
    public function loadConfig($config, $prefix=NULL)
    {
        if( $prefix ) $prefix .= '_';

        $data = NULL;
        if( is_array($config) ){
            $data = $config;
        } elseif(realpath($config)) {
            switch( strtolower(trim(pathinfo($config, PATHINFO_EXTENSION), '.')) )
            {
                case 'php':
                    $data = include($config);
                break;

                case 'env': case 'ini':
                    $data = parse_ini_file($config);
                break;

                case 'json': 
                    $data = json_decode(file_get_contents($config), TRUE);
                break;

                case 'xml':
                    $data = json_decode(json_encode(simplexml_load_file($config)), TRUE);
                break;
            }
        }

        if( is_array($data) ) {
            $data = $this->loadConfigFromArray($data, $prefix);
            $_ENV = array_merge($_ENV, array_change_key_case($data, CASE_UPPER));
            
            $timeZone = env('TIME_ZONE', 'UTC');
            date_default_timezone_set($timeZone);
        }

        return $this;
    }

    /**
     * Flatten an array
     *
     * @param array $data
     * @param string $prefix
     * @return array
     */
    private function loadConfigFromArray(Array $data, $prefix=NULL)
    {
        $results = array();

        foreach( $data as $key=>$value ){
            if( is_array($value) ){
                $value = $this->loadConfigFromArray($value, $prefix.$key.'_');
                $results = array_merge($results, $value);
            }else{
                $results[$prefix.$key] = $value;
            }
        }

        return $results;
    }

    /**
     * Handle an error
     * @param $code
     * @param $message
     * @param $file
     * @param $line
     */
    public function errorHandler($code, $message, $file, $line)
    {
        return $this->exceptionHandler(new ErrorException($message, $code, E_ERROR, $file, $line));
    }

    /**
     * Handle an exception
     * @param \Exception $ex
     */
    public function exceptionHandler($ex)
    {
        return env('IS_DEBUG') ? debugX($ex) : exit($ex->getMessage());
    }

    /**
     * @param string $key
     * @param null $default
     * @return Request
     */
    public function request($key=NULL, $default=NULL){
        return ( $this->request && func_num_args() )
                ? ($this->request->__get($key) ?: $default)
                : $this->request;
    }

    /**
     * @return Response
     */
    public function response(){
        return $this->response;
    }

    public function route($url, $method)
    {
        return Route::match($method, $url);
    }

    /**
     * Process a request
     * @return Application
     * @throws \Exception
     */
    public function run()
    {
        $this->fire('RequestStart');

        $url = isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : (
            isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : NULL
        );

        $pos = strpos($url, '?');
        if( $pos!==FALSE ) {
            parse_str(substr($url, $pos+1), $matches);
            $this->request()->merge($matches);
            $url = substr($url, 0, $pos);
        }

        $result = $this->forward($this->route($url, $_SERVER['REQUEST_METHOD']));
        $this->response()->setContent($result);
            
        return $this->fire('RequestEnded');
    }

    /**
     * Forward an action
     * @param $action
     * @return mixed
     * @throws Exception
     * @throws \Exception
     */
    public function forward($action)
    {
        while( $action ) {
            if( $action instanceof Closure )
                $action = call_user_func($action, $this->request(), $this->response());
            elseif( $action instanceof Action )
                $action = $action->execute($this->request(), $this->response());
            elseif( $action instanceof \Exception )
                throw $action;
            else
                return $action;
        }
    }

    /**
     * Output
     * @param null $content
     * @param array|null $headers
     * @return $this
     */
    public function send($content=NULL, Array $headers=NULL)
    {
        if( $response = $this->response() ) {
            foreach( (array)$headers as $key=>$value )
                $response->header($key, $value);

            if( !is_null($content) ) $response->setContent($content);
            $response->send();
        }

        return $this;
    }

    /**
     * Terminates this application
     */
    public function terminate()
    {
        $this->__global_dispose();
        exit;
    }
}