<?php
/**
 * @file PropertyCollectorInterface.php
 */
namespace Knp\JsonSchemaBundle\Collector;
use Knp\JsonSchemaBundle\Model\Property;


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
}
