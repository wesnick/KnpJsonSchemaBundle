<?php

namespace Knp\JsonSchemaBundle\Model;

class Schema implements \JsonSerializable
{
    const TYPE_OBJECT = 'object';
    const SCHEMA_V4 = 'http://json-schema.org/draft-04/schema#';

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var Schema[]
     */
    private $definitions = array();

    /**
     * The "id" keyword is used to alter the resolution scope.  When an id is encountered, the implementation
     * must resolve against the most immediate parent scope.
     *
     * @var string
     */
    private $id;

    /**
     * @var
     */
    private $type;

    /**
     * This keyword MUST be a URL and a valid JSON reference.  Use a default or link to a customized schema.
     *
     * @var string
     */
    private $schema;

    /**
     * @var Property[]
     */
    private $properties;

    /**
     * To validate against allOf, the given data must be valid against all of the given subschemas.
     *
     * @var PropertyReference[]
     */
    private $allOf;

    /**
     * To validate against anyOf, the given data must be valid against any (one or more) of the given subschemas.
     *
     * @var PropertyReference[]
     */
    private $anyOf;

    /**
     * To validate against oneOf, the given data must be valid against exactly one of the given subschemas.
     *
     * @var PropertyReference[]
     */
    private $oneOf;

    /**
     * @var bool
     */
    private $rootSchema = false;

    /**
     * @param boolean $rootSchema
     */
    public function setRootSchema($rootSchema)
    {
        $this->rootSchema = $rootSchema;
    }

    /**
     * @return array
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     * @param string $alias
     */
    public function hasDefinition($alias)
    {
        return array_key_exists($alias, $this->definitions) && null !== $this->definitions[$alias];
    }

    /**
     * @param string $alias
     * @param Schema $definition
     */
    public function addDefinition($alias, Schema $definition)
    {
        $this->definitions[$alias] = $definition;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Schema
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function addProperty(Property $property)
    {
        $this->properties[$property->getName()] = $property;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    /**
     * @return PropertyReference[]
     */
    public function getAllOf()
    {
        return $this->allOf;
    }

    /**
     * @param PropertyReference $allOf
     *
     * @return Schema
     */
    public function addAllOf($allOf)
    {
        $this->allOf[$allOf->getName()] = $allOf;
        return $this;
    }

    /**
     * @return PropertyReference[]
     */
    public function getAnyOf()
    {
        return $this->anyOf;
    }

    /**
     * @param PropertyReference $anyOf
     *
     * @return Schema
     */
    public function addAnyOf(PropertyReference $anyOf)
    {
        $this->anyOf[$anyOf->getName()] = $anyOf;
        return $this;
    }

    /**
     * @return PropertyReference[]
     */
    public function getOneOf()
    {
        return $this->oneOf;
    }

    /**
     * @param PropertyReference $oneOf
     *
     * @return Schema
     */
    public function addOneOf(PropertyReference $oneOf)
    {
        $this->oneOf[$oneOf->getName()] = $oneOf;
        return $this;
    }

    public function jsonSerialize()
    {

        $serialized = array();

        // Add schema and id root schema only
        if ($this->rootSchema) {
            $serialized['$schema'] = $this->schema;
            $serialized['id'] = $this->id;
        }

        $properties = array();

        foreach ($this->properties as $i => $property) {
            if ($property->hasType(Property::TYPE_OBJECT)) {

                $type = $property->getObject();

                if (!$type) {
                    continue;
                }

                $reference = new PropertyReference($property);
                if (!$this->hasDefinition($type)) {
                    if (null === $property->getSchema()) {
                        continue;
                        //throw new \LogicException(sprintf("Property %s in class %s references and unknown type %s.", $i, $this->title, $type));
                    }
                    $this->addDefinition($type, $property->getSchema());
                }
                $properties[$i] = $reference->jsonSerialize();

            } else {
                $properties[$i] = $property->jsonSerialize();
            }
        }

        $serialized += array(
            'title'      => $this->title,
            'type'       => $this->type,
            'properties' => $properties,
        );

        // Add definitions to root schema only
        if ($this->rootSchema && !empty($this->definitions)) {
            $serialized['definitions'] = $this->definitions;
        }

        // Add schema keywords
        foreach (array('anyOf', 'allOf', 'oneOf') as $membership) {
            if (null !== $this->{$membership}) {
                foreach ($this->{$membership} as $member) {
                    $serialized[$membership][] = $member->jsonSerialize();
                }
            }
        }

        $requiredProperties = array_keys(array_filter($this->properties, function ($property) {
            return $property->isRequired();
        }));

        if (count($requiredProperties) > 0) {
            $serialized['required'] = $requiredProperties;
        }

        return $serialized;
    }
}
