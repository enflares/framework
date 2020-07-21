<?php
namespace enflares\System;

use Closure;

/**
 * Class Controller
 * @package enflares\System
 */
class Controller extends Component
{
    /**
     * @var Action
     */
    protected $action;

    /**
     * Controller constructor.
     * @param Action|NULL $action
     */
    public function __construct(Action $action=NULL)
    {
        parent::__construct();
        $this->action($action);
        $this->onInit();
    }

    /**
     * Gets/Sets the action
     * @param Action|NULL $action
     * @return Action
     */
    public function action(Action $action=NULL)
    {
        if( func_num_args() ) $this->action = $action;
        return $this->action;
    }

    /**
     * Initializes the controller
     */
    protected function onInit() {}

    /**
     * Fetch the view
     * @param null $view
     * @param array|NULL $args
     * @return array|mixed|null
     */
    protected function fetch($view=NULL, Array $args=NULL)
    {
        if( $view instanceof Closure ) {
            try{
                $view = $view($args);
                if( is_array($view) ) return $view;
            }catch(\Exception $ex){
                return $this->error($ex);
            }
        }

        ////////////////////////////////////////////////////////

        if( is_array($view) ) {
            $args = $view;
            $view = NULL;
        }

        return $this->view($view ?: strtolower(strtr((string)$this->action, '@', '/')),
                            array_merge($this->__toArray(), (array)$args));
    }

    /**
     * Generates a view
     * @param $name
     * @param array|NULL $args
     * @return mixed
     */
    protected function view($name, Array $args=NULL)
    {
        return view($name, $args);
    }

    /**
     * Forwards to another action without redirecting
     * @param $route
     * @param array|NULL $args
     * @return mixed
     * @throws Exception
     */
    protected function forward($route, Array $args=NULL)
    {
        return $this->action->forward( $route, $args )->execute();
    }

    /**
     * Redirects to another page
     * @param $url
     */
    protected function redirect($url)
    {
        return redirect($url);
    }

    /**
     * Return data on success
     * @param $data
     * @param null $message
     * @param array|NULL $extra
     * @return array
     */
    protected function success($data, $message=NULL, Array $extra=NULL)
    {
        $extra['data'] = $data;
        if( $message ) $extra['errMsg'] = $message;
        $extra['errCode'] = 0;

        return $extra;
    }

    /**
     * Return data on failure
     * @param $code
     * @param null $message
     * @param array|NULL $extra
     * @return array
     */
    protected function error($code, $message=NULL, Array $extra=NULL)
    {
        if( $code instanceof \Exception ) {
            if( !$message ) $message = $code->getMessage();
            $code = $code->getCode();
        }

        $extra['errCode'] = intval($code) ?: -1;
        $extra['errMsg'] = $message;

        return $extra;
    }

    /**
     * Asserts if the condition is not TRUE
     * @param null $condition
     * @param null $message
     * @param null $args
     * @return void|null
     * @throws Exception
     */
    protected function asserts($condition=NULL, $message=NULL, $args=NULL)
    {
        if( is_string($condition) ) {
            $args = $message;
            $message = $condition;
            $condition = NULL;
        }
        if( empty($condition) ) return InvalidException::trigger($message ?: 'Invalid', $args);
        return $condition;
    }

    /**
     * Asserts if the $item is not found
     * @param null $item
     * @param null $message
     * @param null $args
     * @return void|null
     * @throws Exception
     */
    protected function assertsNotFound($item=NULL, $message=NULL, $args=NULL)
    {
        if( empty($item) ) return NotFoundException::trigger($message ?: 'Record is not found', $args);
        return $item;
    }

    /**
     * Asserts if the $item is duplicated
     * @param $item
     * @param $message
     * @param $args
     * @return void|null
     * @throws Exception
     */
    protected function assertsDuplicated($item=NULL, $message=NULL, $args=NULL)
    {
        return $this->asserts($item, $message ?: 'Record is already existed.', $args);
    }
}