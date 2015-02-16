<?php

namespace Knp\JsonSchemaBundle\Annotations;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Required
{
    /** @var string */
    public $required;
}
