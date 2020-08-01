<?php

namespace App;

use enflares\System\Model;
use enflares\System\RelationTrait;

abstract class AbstractRelation extends Model 
{
    use RelationTrait;
}