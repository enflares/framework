<?php
namespace enflares\System;

/**
 * Interface HierarchyInterface
 * @package enflares\System
 */
interface HierarchyInterface
{
    /**
     * @param null $replacement
     * @return mixed
     */
    public function parent($replacement=NULL);

    /**
     * @return mixed
     */
    public function items();
}

