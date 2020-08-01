<?php

namespace App\namespace_name;

use App\AbstractRelation;

class class_name extends AbstractRelation 
{
    const ENTITY_PRIMARY = App\Model\primary_name::class;
    const ENTITY_SECONDARY = App\Model\secondary_name::class;

    protected $properties;

}