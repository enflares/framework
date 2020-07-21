<?php
namespace enflares\System;

/**
 * Interface EventServiceInterface
 * @package enflares\System
 */
interface EventServiceInterface
{
    /**
     * @param Event $event
     * @return void
     */
    public function notify(Event $event);
}