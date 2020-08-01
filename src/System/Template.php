<?php
namespace enflares\System;

use Closure;

/**
 * Class Template
 * @package System
 */
class Template extends Component
{
    private $__view_real_file__;

    protected function onRender(){}
    protected function onRendered(){}

    public function __construct($file, array $data = NULL)
    {
        parent::__construct($data);
        $this->__view_real_file__ = realpath($file);
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        ob_start();
        try{
            $this->render();
        }catch (\Exception $ex) {
            if( env('IS_DEBUG') ) debug($ex);
            else echo $ex->getMessage();
        }
        return ob_get_clean();
    }

    /**
     * @param null $file
     * @return false|string
     */
    public function view_file($file=NULL)
    {
        if( func_num_args() ) {
            $this->__view_real_file__ = realpath($file);
        }
        
        return $this->__view_real_file__;
    }

    /**
     * Render the template with data
     * @return $this
     * @throws \Exception
     */
    public function render()
    {
        $this->onRender();

        if( is_file($this->__view_real_file__) ) {
            extract($this->__toArray());
            include $this->__view_real_file__;
        }else $this->renderContent();

        $this->onRendered();
        return $this;
    }

    /**
     * @return $this|Template|void
     * @throws \Exception
     */
    public function renderContent()
    {
        return $this->renderAny($this->__get('content'));
    }

    /**
     * Render anything
     * @param $any
     * @return $this|Template|void
     * @throws \Exception
     */
    protected function renderAny($any)
    {
        if( empty($any) || is_array($any) ) return;
        if( $any instanceof Closure ) return call_user_func($any, $this);
        if( $any instanceof self ) return $any->render();
        if( isset($any->content) ) return $this->renderAny($any->content);
        if( $any instanceof \Exception ) throw $any;
        echo $any;
        return $this;
    }
}