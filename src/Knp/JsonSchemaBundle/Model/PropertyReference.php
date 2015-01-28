<?php

namespace Knp\JsonSchemaBundle\Model;


/**
 * Class PropertyReference
 * 
 * @author Wesley O. Nichols <wesley.o.nichols@gmail.com>
 */
class PropertyReference implements \JsonSerializable
{

    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $object;

    public function __construct(Property $property)
    {
        $this->name = $property->getName();
        $this->object = $property->getObject();
        $this->type = $property->hasType(Property::TYPE_ARRAY) ? Property::TYPE_ARRAY : Property::TYPE_OBJECT;
    }


    function jsonSerialize()
    {
        $reference = ['$ref' => '#/definitions/' . $this->object];

        if ($this->type === Property::TYPE_OBJECT) {
            return $reference;
        } else {
            return array(
                'type' => 'array',
                'items' => $reference
            );

        }

    }

}
