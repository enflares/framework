<?php

namespace App\Controller;

use enflares\Db\Schema;
use enflares\System\Controller;

class Install extends Controller 
{
    public function index()
    {
        if( realpath($file = map('install.lock')) ) {
            return 'Can not re-install';
        }
        debug(Schema::migrate());
        touch($file);
        return null;
    }

    public function finish()
    {
        unlink(__FILE__);

        return $this->redirect(home());
    }
}