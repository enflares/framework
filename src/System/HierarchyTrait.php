<?php
namespace enflares\System;

trait HierarchyTrait
{
    use ItemsTrait;

    /**
     * The parent object
     *
     * @var object
     */
    private $parent;

    /**
     * Returns all levels parents
     * @param bool $selfIncluded
     * @return array
     */
    public function parents($selfIncluded=NULL)
    {
        $parents = array();
        
        $parent = $this;
        if( $selfIncluded ) $parents[$this->id()] = $this;
        
        while( $parent = $parent->getParent() )
            $parents = array_merge(array($parent->id()=>$parent), $parents);

        return $parents;
    }

    /**
     * Get the parent object
     *
     * @param null $parent
     * @return mixed
     */
    public function parent($parent=NULL)
    {
        if( func_num_args() ) $this->parent = $parent;

        return $this->parent;
    }

    /**
     * Get the parent object
     *
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set the parent object
     *
     * @param static|array|null $parent
     * @return $this|void
     * @throws Exception
     */
    public function setParent($parent=NULL)
    {
        if( $parent ) {
            if( is_array($parent) ) {
                $class = static::class.'::ENTITY_PARENT';
                $class = defined($class) ? constant($class) : static::class;
                $parent = new $class($parent);
            }

            if( !($parent instanceof self) )
                return InvalidException::trigger('The parent must be an instanceof %s', self::class);
        }

        $this->parent = $parent;
        return $this;
    }
}

