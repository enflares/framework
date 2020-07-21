<?php


namespace enflares\System;


class Theme
{
    /**
     * @return string
     */
    public static function name()
    {
        return env('THEME_NAME', 'default');
    }

    /**
     * Look up the template file
     * @param $name
     * @param null $base
     * @return false|string
     */
    public static function lookUpTemplate($name, $base=NULL)
    {
        $name = strtr($name, '.', DS) . '.' . trim(env('THEME_TEMPLATE_EXT', 'php'), '.');
        return realpath($base ? (rtrim($base, '/\\') . DS . ltrim($name, '/\\'))
                      : map('view', static::name(), $name) );
    }
}