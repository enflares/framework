<?php
namespace enflares\System;

/**
 * Trait RelationTrait
 * @package enflares\System
 */
trait RelationTrait
{
    // const ENTITY_PRIMARY = NULL;
    // const ENTITY_SECONDARY = NULL;
    // const ENTITY_PREFIX = 'relation';

    /**
     * @var EntityInterface
     */
	private $primary;

    /**
     * @var EntityInterface
     */
    private $secondary;

    /**
     * @var int
     */
	//protected $relation_sort_order;

    /**
     * @var int
     */
	//protected $relation_created_at;

    /**
     * @var int
     */
	//protected $relation_deleted_at;

    /**
     * Gets/Sets the primary object
     * @param null $object
     * @return EntityInterface|Model|void|null
     * @throws Exception
     */
    public function primary($object=NULL)
    {
        if( func_num_args() ) {
            $class = constant(static::class . '::ENTITY_PRIMARY');
            if( is_array($object) ) $object = new $class($object);
            if( $object && !($object instanceof EntityInterface) )
                return InvalidException::trigger('Invalid argument type for the primary object');

            if( is_subclass_of($class, Model::class)
                && ($field = call_user_func([$class, 'entity_primary_key'])) ) {
                if ($object instanceof Model) $this->$field = $object->id();
                elseif (!$object) $this->$field = 0;
            }

            $this->primary = $object;
        }
        
        return $this->primary;
    }

    /**
     * Gets/Sets the secondary object
     * @param null $object
     * @return EntityInterface|Model|void|null
     * @throws Exception
     */
	public function secondary($object=NULL)
    {
        if( func_num_args() ) {
            $class = constant(static::class . '::ENTITY_SECONDARY');
            if( is_array($object) ) $object = new $class($object);
            if( $object && !($object instanceof EntityInterface) )
                return InvalidException::trigger('Invalid argument type for the secondary object');

            if( is_subclass_of($class, Model::class)
                && ($field = call_user_func([$class, 'entity_primary_key'])) ) {
                if ($object instanceof Model) $this->$field = $object->id();
                elseif (!$object) $this->$field = 0;
            }
            
            $this->secondary = $object;
        }
        
        return $this->secondary;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->__get('relation_sort_order');
    }

    /**
     * @param $value
     * @return int
     */
    public function setSortOrder($value)
    {
        return $this->__set('relation_sort_order', intval($value));
    }
}

