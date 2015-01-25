<?php

namespace Knp\JsonSchemaBundle\Collector;
use Knp\JsonSchemaBundle\Model\Property;
use Knp\JsonSchemaBundle\Reflection\ReflectionFactory;
use Knp\JsonSchemaBundle\Model\PropertyFactory;


/**
 * Class ReflectionCollector
 * 
 * @author Wesley O. Nichols <wesley.o.nichols@gmail.com>
 */
class ReflectionCollector implements PropertyCollectorInterface
{
    /**
     * @var ReflectionFactory
     */
    private $reflectionFactory;

    /**
     * @var PropertyFactory
     */
    protected $propertyFactory;

    function __construct($reflectionFactory, $propertyFactory)
    {
        $this->reflectionFactory = $reflectionFactory;
        $this->propertyFactory = $propertyFactory;
    }

    /**
     * @param $className
     * @return Property[]
     */
    public function getPropertiesForClass($className)
    {
        $refl      = $this->reflectionFactory->create($className);

        $properties = [];
        foreach ($refl->getProperties() as $property) {
            $properties[] = $this->propertyFactory->createProperty($property->name);
        }

        return $properties;
    }

}
