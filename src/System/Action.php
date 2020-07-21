<?php
namespace enflares\System;

use Closure;

/**
 * Class Action
 * @package enflares\System
 */
class Action extends Component
{
    /**
     * Parse the route to an Action
     *
     * @param string $route
     * @param array|null $parameters
     * @param null $namespace
     * @return Action
     */
    public static function parse($route, Array $parameters=NULL, $namespace=NULL)
    {
        $parts = explode('/', trim(strtr($route, '@', '/'), '/\\ '));
        foreach( $parts as $index=>$part ) 
            $parts[$index] = str_replace(' ', '', ucwords(strtr($part, '-', ' ')));

        switch( count($parts) )
        {
            case 0:
                $route = env('DEFAULT_ROUTE', 'Index@index');
            break;

            case 1:
                $route .= '@index';
            break;

            default:
                $route = array_pop($parts);
                $route = implode('/', $parts) . '@' . $route;
        }

        return new static($route, $parameters, $namespace);
    }
    
    use PropertyTrait;

    /**
     * @var string
     */
    protected $route;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Controller
     */
    private $controller;

    /**
     * Executes an action directly
     * @param $route
     * @param array|NULL $args
     * @param null $namespace
     * @param Request|NULL $request
     * @param Response|NULL $response
     * @return \Exception|mixed
     */
    public static function run($route, Array $args=NULL, $namespace=NULL, Request $request=NULL, Response $response=NULL)
    {
        return (new static(...func_get_args()))->exec($request, $response);
    }

    /**
     * Class constructor
     *
     * @param string $route
     * @param array $args
     * @param null $namespace
     */
    public function __construct($route, Array $args=NULL, $namespace=NULL)
    {
        parent::__construct($args);
        $this->route = implode('@', (array)$route);
        $this->namespace = $namespace; // ?: 'App';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getRoute() . '';
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Convert the route to Controller class
     * @return string
     */
    public function getClass()
    {
        $parts = explode('@', $this->getRoute());
        foreach( explode('\\', trim(strtr(strtr($this->namespace . '\\Controller\\' .
                        (( $prefix = env('CONTROLLER_CLASS_PREFIX') ) ? "$prefix\\" : '') .
                        reset($parts) ?: env('DEFAULT_ACTION', 'index')
                        , '.', '\\'), '/', '\\'), '/\\')) as $index=>$part ) {
            $parts[$index] = str_replace(' ', '', ucwords(strtr($part, '-', ' ')));
        }

        if( is_subclass_of($class = implode('\\', $parts), Controller::class) )
            return $class;
    }

    /**
     * Returns the controller
     * @return Controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Returns the command part
     * @return mixed
     */
    public function getCommand()
    {
        $parts = explode('@', $this->getRoute());
        return trim(end($parts)) ?: env('DEFAULT_ACTION_COMMAND', 'index');
    }

    /**
     * Convert the action to url
     * @return string
     */
    public function getUrl()
    {
        return home(strtr(strtr(strtr($this->getRoute(), '.', '/'), '@', '/'), '\\', '/'),
                    $this->__toArray(),
                    env('HTTPS'));
    }

    /**
     * @param Request|null $request
     * @param Response|null $response
     * @return mixed
     * @throws Exception
     */
    public function execute(Request $request=NULL, Response $response=NULL)
    {
        if( ($command = $this->getCommand())
            && ( substr($command, 0, 2) !== '__' )
            && ( $class = $this->getClass() ) ) {
            $this->controller = call_user_func([$class, 'getInstance']);
            $this->controller->action($this);
            if( method_exists($this->controller, $command) )
                return call_user_func([$this->controller, $command],
                    $this->request = call_user_func(array(($request ?: $this->request) ?: request(), 'merge'), $this->__toArray()),
                    $this->response = $response ?: response());
        }

        return DeniedException::trigger('Action "%s" is not found', $this->getRoute());
    }

    /**
     * Executes this action silently
     * @param Request|NULL $request
     * @param Response|NULL $response
     * @return \Exception|mixed
     */
    public function exec(Request $request=NULL, Response $response=NULL)
    {
        try{
            $result = $this->execute($request, $response);
            while( $result ) {
                if( $result instanceof Closure ) $result = call_user_func($result, $request, $response);
                elseif( $result instanceof self ) $result = $result->execute( $request, $response );
                else return $result;
            }
        }catch(\Exception $ex){
            return $ex;
        }
    }

    /**
     * Internally change to another action
     * @param $route
     * @param array|NULL $args
     * @param null $namespace
     * @return Action
     */
    public function forward($route, Array $args=NULL, $namespace=NULL)
    {
        $this->controller = NULL;
        $this->route = $route;
        $this->namespace = $namespace;

        return $this->__reset()->__assign((array)$args);
    }

    /**
     * Magic function
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws Exception
     */
    public function __invoke(Request $request, Response $response)
    {
        return $this->execute($request, $response);
    }
}
