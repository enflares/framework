<?php
namespace enflares\System;

use Countable;
use IteratorAggregate;

/**
 * Interface CollectionInterface
 * @package enflares\System
 */
interface CollectionInterface extends Countable, IteratorAggregate, ItemsInterface
{
    /**
     * Checks if a key or an item exists
     * @param $item
     * @return bool
     */
	public function exists($item);

    /**
     * Append an item to the collection
     * @param $item
     * @return $this
     */
	public function append($item);

    /**
     * Remove an item
     * @param $item
     * @return $this
     */
	public function expel($item);

    /**
     * Remove all items
     * @return $this
     */
	public function clear();
}