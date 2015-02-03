<?php

namespace Knp\JsonSchemaBundle\Schema;

use Knp\JsonSchemaBundle\Collector\PropertyCollectorInterface;
use Knp\JsonSchemaBundle\Reflection\ReflectionFactory;
use Knp\JsonSchemaBundle\Schema\SchemaRegistry;
use Knp\JsonSchemaBundle\Model\SchemaFactory;
use Knp\JsonSchemaBundle\Model\Schema;
use Knp\JsonSchemaBundle\Model\PropertyFactory;
use Knp\JsonSchemaBundle\Model\Property;
use Knp\JsonSchemaBundle\Property\PropertyHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SchemaGenerator
{
    protected $jsonValidator;
    protected $propertyCollector;
    protected $schemaRegistry;
    protected $schemaFactory;
    protected $propertyFactory;
    protected $propertyHandlers;
    protected $aliases = array();
    /**
     * @var Schema
     */
    protected $rootSchema;

    public function __construct(
        \JsonSchema\Validator $jsonValidator,
        UrlGeneratorInterface $urlGenerator,
        PropertyCollectorInterface $propertyCollector,
        SchemaRegistry $schemaRegistry,
        SchemaFactory $schemaFactory,
        PropertyFactory $propertyFactory
    ) {
        $this->jsonValidator     = $jsonValidator;
        $this->urlGenerator      = $urlGenerator;
        $this->propertyCollector = $propertyCollector;
        $this->schemaRegistry    = $schemaRegistry;
        $this->schemaFactory     = $schemaFactory;
        $this->propertyFactory   = $propertyFactory;
        $this->propertyHandlers  = new \SplPriorityQueue;
    }

    public function generate($alias, $isRootSchema = true)
    {
        $this->aliases[] = $alias;

        $className = $this->schemaRegistry->getNamespace($alias);
        $schema    = $this->schemaFactory->createSchema(ucfirst($alias));

        $schema->setId($this->urlGenerator->generate('show_json_schema', array('alias' => $alias), true) . '#');
        $schema->setSchema(Schema::SCHEMA_V4);
        $schema->setType(Schema::TYPE_OBJECT);
        $schema->setRootSchema($isRootSchema);

        if ($isRootSchema) {
            $this->rootSchema =& $schema;
        }

        foreach ($this->propertyCollector->getPropertiesForClass($className) as $property) {
            $this->applyPropertyHandlers($className, $property);

            if (!$property->isIgnored() && $property->hasType(Property::TYPE_OBJECT) && $property->getObject()) {
                // Make sure that we're not creating a reference to the parent schema of the property
                if (!in_array($property->getObject(), $this->aliases)) {

                    // Generate the schema for the property
                    $subSchema = $this->generate($property->getObject(), false);
                    // Add any definitions for this property to the parent
                    foreach ($subSchema->getDefinitions() as $definitionAlias => $definition) {
                        $this->rootSchema->addDefinition($definitionAlias, $definition);
                    }

                    if (!$isRootSchema && !$this->rootSchema->hasDefinition($property->getObject())) {
                        $this->rootSchema->addDefinition($property->getObject(), $subSchema);
                    }
                    $property->setSchema($subSchema);
                } else {
                    $property->setIgnored(true);
                }
            }

            if (!$property->isIgnored()) {
                $schema->addProperty($property);
            }
        }

        if (null === $schema->getProperties()) {
            throw new \Exception(sprintf("The resource %s has no visible properties for this serialization group.", $schema->getTitle()));
        }

        if (false === $this->validateSchema($schema)) {
            $message = "Generated schema is invalid. Please report on" .
                "https://github.com/KnpLabs/KnpJsonSchemaBundle/issues/new.\n" .
                "The following problem(s) were detected:\n";
            foreach ($this->jsonValidator->getErrors() as $error) {
                $message .= sprintf("[%s] %s\n", $error['property'], $error['message']);
            }
            $message .= sprintf("Json schema:\n%s", json_encode($schema, JSON_PRETTY_PRINT));
            throw new \Exception($message);
        }

        return $schema;
    }

    public function registerPropertyHandler(PropertyHandlerInterface $handler, $priority)
    {
        $this->propertyHandlers->insert($handler, $priority);
    }

    public function getPropertyHandlers()
    {
        return array_values(iterator_to_array(clone $this->propertyHandlers));
    }

    /**
     * @param PropertyCollectorInterface $propertyCollector
     *
     * @return SchemaGenerator
     */
    public function setPropertyCollector($propertyCollector)
    {
        $this->propertyCollector = $propertyCollector;
        return $this;
    }

    /**
     * Validate a schema against the meta-schema provided by http://json-schema.org/schema
     *
     * @param Schema $schema a json schema
     *
     * @return boolean
     */
    private function validateSchema(Schema $schema)
    {
        $this->jsonValidator->check(
            json_decode(json_encode($schema->jsonSerialize())),
            json_decode(file_get_contents($schema->getSchema()))
        );

        return $this->jsonValidator->isValid();
    }

    private function applyPropertyHandlers($className, Property $property)
    {
        $propertyHandlers = clone $this->propertyHandlers;

        while ($propertyHandlers->valid()) {
            $handler = $propertyHandlers->current();

            $handler->handle($className, $property);

            $propertyHandlers->next();
        }
    }
}
