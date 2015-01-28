<?php

namespace Knp\JsonSchemaBundle\Property;

use Doctrine\Common\Inflector\Inflector;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use Knp\JsonSchemaBundle\Model\Property;
use Knp\JsonSchemaBundle\Schema\SchemaRegistry;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\FormTypeGuesserInterface;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use JMS\Serializer\SerializationContext;

class JmsSerializerHandler implements PropertyHandlerInterface
{
    /**
     * @var MetadataFactoryInterface
     */
    protected $factory;

    /**
     * @var PropertyNamingStrategyInterface
     */
    protected $namingStrategy;

    /**
     * @var SchemaRegistry
     */
    protected $registry;

    protected $groups = array();

    public function __construct(
        SchemaRegistry $registry,
        MetadataFactoryInterface $factory,
        PropertyNamingStrategyInterface $namingStrategy,
        $groups = array()
    ) {
        $this->registry       = $registry;
        $this->factory        = $factory;
        $this->namingStrategy = $namingStrategy;
        $this->groups         = $groups;
    }

    public function handle($className, Property $property)
    {
        $meta = $this->factory->getMetadataForClass($className);

        if (!isset($meta->propertyMetadata[$property->getName()]) || !isset($meta->propertyMetadata[$property->getName()]->type)) {
            return;
        }

        $propertyMeta = $meta->propertyMetadata[$property->getName()];
        $type = $this->getPropertyType($propertyMeta->type);
        $property->setType($type);

        if (!$dataType = $this->getNestedTypeInArray($propertyMeta)) {
            $dataType = $propertyMeta->type['name'];
        }

        if (in_array($type, [Property::TYPE_OBJECT, Property::TYPE_ARRAY]) && $this->registry->hasNamespace($dataType)) {

            if ($type === Property::TYPE_ARRAY) {
                $property->addType(Property::TYPE_OBJECT);
            }

            $alias = $this->registry->getAlias($dataType);

            if ($alias) {
                $property->setObject($alias);
            }
        }

        if (Property::TYPE_ARRAY === $type) {
            $property->setMultiple(true);
        }

    }

    private function getPropertyType(array $type)
    {
        switch ($type['name']) {
            case 'ArrayCollection':
            case 'array':
                return Property::TYPE_ARRAY;
            case 'boolean':
                return Property::TYPE_BOOLEAN;
            case 'float':
            case 'double':
            case 'number':
                return Property::TYPE_NUMBER;
            case 'integer':
                return Property::TYPE_INTEGER;
            case 'date':
            case 'datetime':
            case 'text':
            case 'textarea':
            case 'country':
            case 'email':
            case 'file':
            case 'language':
            case 'locale':
            case 'time':
            case 'string':
            case 'DateTimeImmutable':
                return Property::TYPE_STRING;
            default:
                return Property::TYPE_OBJECT;
        }
    }

    private function getPropertyFormat(array $type)
    {
        switch ($type['name']) {
            case 'ArrayCollection':
            case 'array':
                return Property::TYPE_ARRAY;
            case 'boolean':
                return Property::TYPE_BOOLEAN;
            case 'float':
            case 'double':
            case 'number':
                return Property::TYPE_NUMBER;
            case 'integer':
                return Property::TYPE_INTEGER;
            case 'date':
            case 'datetime':
            case 'text':
            case 'textarea':
            case 'country':
            case 'email':
            case 'file':
            case 'language':
            case 'locale':
            case 'time':
            case 'string':
                return Property::TYPE_STRING;
        }
    }


    /**
     * Check the various ways JMS describes values in arrays, and
     * get the value type in the array
     *
     * @param  PropertyMetadata $item
     * @return string|null
     */
    protected function getNestedTypeInArray(PropertyMetadata $item)
    {
        if (isset($item->type['name']) && in_array($item->type['name'], array('array', 'ArrayCollection'))) {
            if (isset($item->type['params'][1]['name'])) {
                // E.g. array<string, MyNamespaceMyObject>
                return $item->type['params'][1]['name'];
            }
            if (isset($item->type['params'][0]['name'])) {
                // E.g. array<MyNamespaceMyObject>
                return $item->type['params'][0]['name'];
            }
        }

        return null;
    }

}
