<?php
/**************************************************
 * Enflares - A PHP Framework For Web
 * @package  Enflares
 * @author   Shaotang Zhang <shaotang.zhang@gmail.com>
 **************************************************/

/////////////////////////////
//// Application Helpers ////
/////////////////////////////

// application, request, response, controller, view, model, module, route, action, db

use App\Application as App;
use enflares\Db\Db;
use enflares\System\Action;
use enflares\System\Application;
use enflares\System\Request;
use enflares\System\Response;


if( !function_exists('map') ) {
    /**
     * Combine a path with components, adding the site root directory to the prefixed, without verifying the existence
     * @return string
     */
    function map() {
        $args = func_get_args();
        array_unshift($args, SITE_ROOT);
        return implode(DIRECTORY_SEPARATOR, $args);
    }
}

if( !function_exists('db') ) {
    /**
     * Return the default connection to database
     * @return Db
     */
    function db() {
        return Db::getInstance();
    }
}

if( !function_exists('app') ){
    /**
     * Get/Set the current instance of application
     * @param string|Application $class
     * @return Application
     */
    function app($class=NULL)
    {
        static $g;
        if( $class && $g ) 
            throw new RuntimeException('The application is already started');

        $g = ( is_subclass_of($class = $class ?: App::class, Application::class) )
                ? call_user_func([$class, 'getInstance'])
                : FALSE;

        if( $g ) return $g;
        throw new RuntimeException('Argument should be the class or an instance of Application');
    }
}

if( !function_exists('action') ) {
    /**
     * Generates a new Action
     * @param $route
     * @param array|NULL $args
     * @param null $namespace
     * @return Action
     */
    function action($route, Array $args=NULL, $namespace=NULL)
    {
        return new Action($route, $args, $namespace);
    }
}

if( !function_exists('request') ) {
    /**
     * The application request
     * @param string $key
     * @param null $default
     * @return Request|mixed
     */
    function request($key=NULL, $default=NULL)
    {
        return app()->request(...func_get_args());
    }
}

if( !function_exists('response') ) {
    /**
     * The application response
     * @return Response
     */
    function response()
    {
        return app()->response();
    }
}

if( !function_exists('view') ) {
    function view($name, Array $args=NULL, $base=NULL)
    {

    }
}