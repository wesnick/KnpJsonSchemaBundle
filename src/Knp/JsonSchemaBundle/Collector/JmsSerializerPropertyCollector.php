<?php

namespace Knp\JsonSchemaBundle\Collector;

use JMS\Serializer\SerializationContext;
use Knp\JsonSchemaBundle\Model\Property;
use Knp\JsonSchemaBundle\Model\PropertyFactory;
use Metadata\MetadataFactoryInterface;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;


/**
 * Class JmsSerializerPropertyCollector
 * 
 * @author Wesley O. Nichols <wesley.o.nichols@gmail.com>
 */
class JmsSerializerPropertyCollector implements PropertyCollectorInterface
{

    protected $groups = array();

    /**
     * @var MetadataFactoryInterface
     */
    protected $metadataFactory;

    /**
     * @var PropertyFactory
     */
    protected $propertyFactory;

    function __construct($metadataFactory, $propertyFactory, $groups = array())
    {
        $this->metadataFactory = $metadataFactory;
        $this->propertyFactory = $propertyFactory;
        $this->groups = $groups;
    }

    /**
     * @param $className
     * @return Property[]
     */
    public function getPropertiesForClass($className)
    {

        $exclusionStrategies = array();
        if ($this->groups) {
            $exclusionStrategies[] = new GroupsExclusionStrategy($this->groups);
        }

        $metadata = $this->metadataFactory->getMetadataForClass($className);

        $properties = [];

        foreach ($metadata->propertyMetadata as $prop) {

            $property = $this->propertyFactory->createProperty($prop->name);

            // apply exclusion strategies
            /** @var ExclusionStrategyInterface $strategy */
            foreach ($exclusionStrategies as $strategy) {
                if (true === $strategy->shouldSkipProperty($prop, SerializationContext::create())) {
                    $property->setIgnored(true);
                    continue;
                }
            }

            $properties[] = $property;
        }

        return $properties;
    }
}
