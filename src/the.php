<?php

use enflares\System\View;
use enflares\Ui\Tag;
use enflares\Ui\Element;

/**
 * Factory of UI design system
 *
 * @param string $name
 * @param mixed $content
 * @param array $args
 * @param array $items
 * @return Element|View|Tag|string|void
 */
function the($name, $content=NULL, Array $args=NULL, Array $items=NULL) {

    $class = str_replace(' ', '', ucwords(strtr($name, '-', ' ')));
    $class = strtr(ucwords(strtr(strtr(strtr($class, '/', '\\'), '.', '\\'), '\\', ' ')), ' ', '\\');

    if( $class = trim($class, '/\\. ') ){
        if( $prefix = env('UI_DESIGN_SYSTEM') ) $class = trim($prefix, '/\\. ') . '\\' . $class;

        if( is_subclass_of($class, Element::class) )
            return new $class($content, $args, $items);
        else {
            $view = view('components.'.$name, $args);
            if( $view->view_file() ) {
                if( $items ) $view->__set('items', $items);
                if( $content ) $view->__set('content', $content);
                return $view;
            }else{
                return new Tag($name, implode((array)$content) . implode((array)$items), $args);
            }
        }
    }
}