<?php

namespace enflares\Db;

use ReflectionClass;
use enflares\System\Controller;

/**
 * Class SchemaInstaller
 * @package enflares\Db
 */
abstract class SchemaInstaller extends Controller 
{
    protected function onInit()
    {
        spl_autoload_register(function($class) {
            if( $file = realpath(map('database', 'factory', strtr($class, '\\', DIRECTORY_SEPARATOR)) . '.php') ) {
                include_once $file;
                return class_exists($class, FALSE) || interface_exists($class, FALSE) || trait_exists($class, FALSE);
            }
            return NULL;
        });
    }

    public function index()
    {
        if( realpath($file = map('schema.lock')) ) {
            return 'Can not re-install';
        }

        debug($results = Schema::migrate(), db());

        file_put_contents($file, json_encode(array_keys($results)));
        return null;
    }

    public function finish()
    {
        $r = new ReflectionClass(static::class);
        unlink($r->getFileName());

        return $this->redirect(home());
    }
}