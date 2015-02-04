<?php
/**
 * @file PropertyCollectorInterface.php
 */
namespace Knp\JsonSchemaBundle\Collector;
use Knp\JsonSchemaBundle\Model\Property;
use Knp\JsonSchemaBundle\Model\Schema;


/**
 * Class JmsSerializerPropertyCollector
 *
 * @author Wesley O. Nichols <wesley.o.nichols@gmail.com>
 */
interface PropertyCollectorInterface
{
    /**
     * @param $className
     * @return Property[]
     */
    public function getPropertiesForClass($className);


    /**
     * @param string $className
     * @param Schema $schema
     */
    public function appendClassProperties($className, Schema $schema);
}
