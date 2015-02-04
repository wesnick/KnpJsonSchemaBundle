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

    public static function create($name, $type, $object)
    {

        $property = new Property();
        $property->setName($name);
        $property->addType($type);
        $property->setObject($object);

        return new static($property);
    }
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getObject()
    {
        return $this->object;
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
