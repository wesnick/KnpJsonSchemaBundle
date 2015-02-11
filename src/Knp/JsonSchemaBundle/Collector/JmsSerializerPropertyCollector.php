<?php

namespace Knp\JsonSchemaBundle\Collector;

use JMS\Serializer\SerializationContext;
use Knp\JsonSchemaBundle\Model\Property;
use Knp\JsonSchemaBundle\Model\PropertyFactory;
use Knp\JsonSchemaBundle\Model\PropertyReference;
use Knp\JsonSchemaBundle\Model\Schema;
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

            if (null === $prop->type) {
                $property->setIgnored(true);
            }

            $properties[] = $property;
        }

        if (null !== $metadata->discriminatorFieldName) {

            $discriminator = $this->propertyFactory->createProperty($metadata->discriminatorFieldName);
            $discriminator->setType(Property::TYPE_STRING);
            $discriminator->setRequired(true);

            if (null === $metadata->discriminatorValue) {

                $discriminator->setDescription(
                    sprintf("A discriminator property.  One of: '%s'", implode("', '", array_keys($metadata->discriminatorMap)))
                );

            } else {
                $discriminator->setDescription(
                    sprintf('Discriminated instance.  Set type as "%s"', $metadata->discriminatorValue)
                );
            }

            $properties[] = $discriminator;

        }

        return $properties;
    }

    /**
     * @param string $className
     * @param Schema $schema
     */
    public function appendClassProperties($className, Schema $schema)
    {
        $metadata = $this->metadataFactory->getMetadataForClass($className);

        if (null !== $metadata->discriminatorFieldName) {
            if (null === $metadata->discriminatorValue) {

                // Base Class, add any of
                foreach ($metadata->discriminatorMap as $alias => $map) {
                    $schema->addAnyOf(PropertyReference::create($map, Property::TYPE_OBJECT, $alias));
                }
            } else {
                // Sub Class Show one of
                $baseSchema = $this->extractDiscriminatorBaseClass($metadata->discriminatorBaseClass);

                $schema->addOneOf(
                    PropertyReference::create(
                        $baseSchema, Property::TYPE_OBJECT, $baseSchema
                    )
                );
            }


        }
    }


    /**
     * @TODO: need a strategy for handling this
     *
     * @param $class
     * @return string
     */
    private function extractDiscriminatorBaseClass($class)
    {
        $name = substr($class, strrpos($class, "\\") + 1);

        return lcfirst($name);
    }

}
