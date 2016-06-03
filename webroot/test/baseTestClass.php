<?php
/*
 * Abstract Class for Test Cases
 */
abstract class Workana_PHPUnit_Base_Framework extends PHPUnit_Framework_TestCase {
    
    protected function setUp() {
        
    }
    
   /*
    * Invoke a Static or private method using ReflectionClass
    * @param object $object
    * @param string $methodName method name to invoke
    * @param array $args Aguments to pass to method invoked 
    */ 
    protected function callMethod($object, $methodName, $args = array()) {
        
        $reflectedClass = new ReflectionClass(get_class($object));
        $method = $reflectedClass->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }
    
   /*
    * get a Static or private property using ReflectionClass
    * @param object $object
    * @param string $propertyName property name
    */ 
    protected function getProperty($object, $propertyName) {
        $reflectedClass = new ReflectionClass(get_class($object));
        $property = $reflectedClass->getProperty($propertyName);
        $property->setAccessible(TRUE);
        return $property->getValue($object);
    }
   
   /*
    * set a Static or private property value using ReflectionClass
    * @param object $object
    * @param string $propertyName property name
    * @param mixed $value property value
    */  
    protected function setProperty($object, $propertyName, $value) {
        $reflectedClass = new ReflectionClass(get_class($object));
        $property = $reflectedClass->getProperty($propertyName);
        $property->setAccessible(TRUE);
        $property->setValue($object, $value);
    }

}
