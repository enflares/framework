<?php
namespace enflares\System;

/**
 * Class LoginException
 * @package enflares\System
 */
class LoginException extends DeniedException
{
    public $url;
}