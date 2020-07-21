<?php
namespace enflares\System;

use ArrayIterator;

/**
 * The implementation of an CollectionInterface
 * The inherited class can implement from \Countable and \IteratorAggregate
 */
trait CollectionTrait
{
    /**
     * A copy of items
     *
     * @var array
     */
    private $items = [];

    /**
     * Return all items as an array
     *
     * @return array
     */
    public function items()
    {
        return $this->items;
    }

    /**
     * Return the first item in the collection
     *
     * @return void
     */
    public function first()
    {
        return reset($this->items);
    }

    /**
     * Return the first key in the collection
     *
     * @return int|string
     */
    public function firstKey()
    {
        return array_key_first($this->items);
    }

    /**
     * Return the last item in the collection
     *
     * @return void
     */
    public function last()
    {
        return end($this->items);
    }

    /**
     * Return the last key in the collection
     *
     * @return int|string
     */
    public function lastKey()
    {
        return array_key_last($this->items);
    }

    /**
     * To check if an item exists in the collection
     *
     * @param int|EntityInterface|mixed $item
     * @return bool
     */
    public function exists($item)
    {
        if( is_int($item) )
            return !!isset($this->items[$item]);

        return !!in_array($item, $this->items, TRUE);
    }

    /**
     * Append an item to the collection
     *
     * @param EntityInterface|mixed $item
     * @param null $key
     * @return $this
     */
    public function append($item=NULL, $key=NULL)
    {        
        if( is_null($item) || is_array($item) ) {
            $class = static::class . '::ENTITY_ITEM';
            $class = defined($class) ? constant($class) : static::class;

            if( $class ) $item = new $class($item);
        }

        if( $item instanceof EntityInterface )
            $this->items[$key ?: $item->id()] = $item;
        elseif( !is_null($key) )
            $this->items[$key] = $item;
        else
            $this->items[] = $item;

        return $this;
    }

    /**
     * Contact multiple items to the collection
     *
     * @param array|Collection|EntityInterface $list
     * @return $this|void
     * @throws Exception
     */
    public function merge($list)
    {
        if( is_array($list) || ($list instanceof self) )
            foreach( $list as $index=>$item ) 
                $this->append($item, is_int($index) ? NULL : $index);
        elseif( $list instanceof EntityInterface )
            $this->append($list);
        else
            return InvalidException::trigger('Invalid argument type');

        return $this;
    }

    /**
     * Extract a slice of the collection
     *
     * @param int $offset
     * @param int $length
     * @param bool $preserveKeys
     * @return CollectionTrait
     */
    public function slice($offset=NULL, $length=NULL, $preserveKeys=NULL)
    {
        $g = new static;
        $g->items = array_slice($this->items, $offset, $length, $preserveKeys);
        return $g;
    }

    /**
     * Remove an item off the collection
     *
     * @param int|EntityInterface|mixed $item
     * @return $this
     */
    public function expel($item)
    {
        if( is_int($item) ) unset( $this->items[$item] );
        else unset( $this->items[array_search($item, $this->items, TRUE)] );

        return $this;
    }

    /**
     * Remove all items in the collection
     *
     * @return $this
     */
    public function clear()
    {
        $this->items = array();
        return $this;
    }

    /**
     * Return the number of items in the collection
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Return the iterator of this collection, for the use with foreach command
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }
}