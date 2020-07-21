<?php
namespace enflares\System;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

class Data implements DataInterface, ArrayAccess, IteratorAggregate {
    use PropertyTrait;
    use ArrayAccessTrait;

    public function getIterator()
    {
        return new ArrayIterator( $this->_ );
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }
}