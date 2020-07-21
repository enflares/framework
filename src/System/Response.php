<?php
namespace enflares\System;

/**
 * Class Response
 * @package enflares\System
 */
class Response extends Component
{
    use PropertyTrait;

    protected $content;
    protected $pipe;

    private $__before = array();
    private $__after = array();
    private $__headers = array();
    private $__cookies = array();

    /**
     * Send a header
     * @param $name
     * @param null $value
     * @param null $replace
     * @param null $statusCode
     * @return $this
     */
    public function header($name, $value=NULL, $replace=NULL, $statusCode=NULL)
    {
        $this->__headers[] = func_get_args();
        return $this;
    }

    /**
     * Send a cookie
     * @param $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @return $this
     */
    public function cookie($name, $value = "", $expire = 0, $path = "", $domain = "", $secure = false, $httpOnly = false)
    {
        $args = func_get_args();
        if( is_string($expire) ) $args[2] = strtotime($expire); 
        else $args[2] = time() + intval($expire);
        $this->__cookies[] = $args;

        return $this;
    }

    /**
     * Get the content
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the content
     * @param $value
     * @return $this
     */
    public function setContent($value)
    {
        $this->content = $value;
        return $this;
    }

    public function clear(){
        return $this->setContent(NULL);
    }

    /**
     * Output the content
     * @param $content
     * @return $this
     * @throws \Exception
     */
    public function write($content)
    {
        if( $this->pipe instanceof Storage)
            $this->pipe->getWriter()->write($content);

        elseif( $content instanceof View )
            $content->render();

        elseif( is_array($content) )
            echo json_encode($content);

        elseif( $content instanceof \Exception )
            echo env('IS_DEBUG') ? (string)$content : $content->getMessage();

        else echo $content;

        return $this;
    }

    /**
     * Output the content
     * @return $this
     * @throws \Exception
     */
    public function send()
    {
        $writer = ($this->pipe instanceof Storage) ? $this->pipe->getWriter() : $this;
        
        if( method_exists($writer, 'writeHeader') )
            foreach( $this->__headers as $header )
                $writer->writeHeader(...$header);
        if( method_exists($writer, 'writeCookie') )
            foreach( $this->__cookies as $cookie )
                $writer->writeCookie(...$cookie);

        $writer->write($this->content);

        return $this;
    }
}