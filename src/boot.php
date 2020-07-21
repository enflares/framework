<?php

/**************************************************
 * Enflares - A PHP Framework For Web
 * @package  Enflares
 * @author   Shaotang Zhang <shaotang.zhang@gmail.com>
 **************************************************/

// STOP if already imported
if( defined('ENFLARES_VERSION') ) return;
/**
 * The version of the Enflares PHP Framework
 */
define('ENFLARES_VERSION', '1.0.0');

/**
 * A shortcut for DIRECTORY_SEPARATOR
 */
defined('DS')           or define('DS',         DIRECTORY_SEPARATOR);
/**
 * Define the root directory of everything
 */
defined('DOC_ROOT')     or define('DOC_ROOT',   realpath(__DIR__ . '../../../../'));
/**
 * Define the root directory of the application
 */
defined('SITE_ROOT')    or define('SITE_ROOT',  realpath(DOC_ROOT . DS . 'public' . DS . 'default'));
/**
 * Define the root directory of public web
 */
defined('WEB_ROOT')     or define('WEB_ROOT',   realpath(SITE_ROOT . DS . 'dist'));

/**
 * Import the fundamental helpers
 */
include_once __DIR__ . '/common.php';
/**
 * Import the application helpers
 */
include_once __DIR__ . '/application.php';
/**
 * Import the helpers for localization
 */
include_once __DIR__ . '/localization.php';
/**
 * Import the helpers for theme
 */
include_once __DIR__ . '/the.php';