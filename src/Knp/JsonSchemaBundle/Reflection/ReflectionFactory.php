<?php

namespace Knp\JsonSchemaBundle\Reflection;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Knp\JsonSchemaBundle\Model\PropertyFactory;

class ReflectionFactory
{
    public function __construct(Finder $finder, Filesystem $filesystem, PropertyFactory $propertyFactory)
    {
        $this->finder     = $finder;
        $this->filesystem = $filesystem;
        $this->propertyFactory = $propertyFactory;
    }


    public function getPropertiesForClass($className)
    {
        $refl = new \ReflectionClass($className);

        $properties = [];

        foreach ($refl->getProperties() as $prop) {
            $properties[$prop->name] = $this->propertyFactory->createProperty($prop->name);
        }

        return $properties;
    }

    public function createFromDirectory($directory, $namespace)
    {
        if (false === $this->filesystem->exists($directory)) {
            return array();
        }

        $this->finder->files();
        $this->finder->name('*.php');
        $this->finder->in($directory);

        $refClasses = array();

        foreach ($this->finder->getIterator() as $name) {
            $baseName      = substr($name, strlen($directory)+1, -4);
            $baseClassName = str_replace('/', '\\', $baseName);
            $refClasses[]  = $this->create($namespace.'\\'.$baseClassName);
        }

        return $refClasses;
    }
}
