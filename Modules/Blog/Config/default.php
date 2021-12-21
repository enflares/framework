<?php
/**
 * Enflares PHP Framework
 *
 * This file is the entrance to define routes, events, and middlewares for the module.
 *
 * Date:        2021/12/21
 */

use enflares\System\Route;

Route::import([
    'index.php',
    '/admin'=>'admin.php',
    '/api'=>'api.php',
    '/user'=>'user.php'
], ['modifier'=>'i'], dirname(__DIR__, '/Routes'));